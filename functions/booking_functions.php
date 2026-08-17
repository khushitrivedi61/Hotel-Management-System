<?php
/**
 * GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
 * Core Booking Engine & State Transition Machine
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helper.php';
require_once __DIR__ . '/log_function.php';

/**
 * Create a new booking request
 */
function createBooking($customerId, $roomId, $checkIn, $checkOut, $numGuests, $specialRequests = '', $couponCode = '', $selectedServices = []) {
    global $pdo;
    
    // 1. Verify Availability
    $checkStmt = $pdo->prepare("
        SELECT id FROM bookings 
        WHERE room_id = ? 
        AND status IN ('Approved', 'Checked-In', 'Pending') 
        AND (check_in_date < ? AND check_out_date > ?)
        LIMIT 1
    ");
    $checkStmt->execute([$roomId, $checkOut, $checkIn]);
    if ($checkStmt->fetch()) {
        return ['success' => false, 'message' => 'Selected room is no longer available for these dates. Please choose another date or room.'];
    }
    
    // 2. Fetch Room & Rates
    $roomStmt = $pdo->prepare("SELECT price_per_night FROM rooms WHERE id = ?");
    $roomStmt->execute([$roomId]);
    $room = $roomStmt->fetch();
    if (!$room) {
        return ['success' => false, 'message' => 'Invalid room selected.'];
    }
    
    $nights = calculateNights($checkIn, $checkOut);
    $pricePerNight = (float)$room['price_per_night'];
    $subtotal = $nights * $pricePerNight;
    
    // 3. Process Extra Services Total
    $serviceTotal = 0;
    $validServices = [];
    if (!empty($selectedServices)) {
        foreach ($selectedServices as $sId) {
            $srvStmt = $pdo->prepare("SELECT id, price FROM extra_services WHERE id = ? AND status = 'Active'");
            $srvStmt->execute([$sId]);
            $srv = $srvStmt->fetch();
            if ($srv) {
                $serviceTotal += (float)$srv['price'];
                $validServices[] = $srv;
            }
        }
    }
    
    // 4. Process Coupon Discount
    $discountAmount = 0.00;
    if (!empty($couponCode)) {
        $cpnStmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'Active' AND valid_from <= CURDATE() AND valid_to >= CURDATE() LIMIT 1");
        $cpnStmt->execute([$couponCode]);
        $cpn = $cpnStmt->fetch();
        if ($cpn) {
            $discountPct = (float)$cpn['discount_percent'];
            $calculatedDisc = ($subtotal * $discountPct) / 100;
            if ($cpn['max_discount'] > 0 && $calculatedDisc > $cpn['max_discount']) {
                $calculatedDisc = (float)$cpn['max_discount'];
            }
            $discountAmount = $calculatedDisc;
        }
    }
    
    // 5. Calculate GST & Grand Total
    $taxRate = (float)getSetting('tax_percentage', 18);
    $taxableAmount = max(0, ($subtotal + $serviceTotal) - $discountAmount);
    $taxAmount = ($taxableAmount * $taxRate) / 100;
    $grandTotal = $taxableAmount + $taxAmount;
    
    $bookingCode = generateCode('GRB');
    
    try {
        $pdo->beginTransaction();
        
        // Insert Booking Record
        $bStmt = $pdo->prepare("
            INSERT INTO bookings (booking_code, customer_id, room_id, check_in_date, check_out_date, num_guests, status, special_requests, subtotal, discount_amount, tax_amount, grand_total) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?, ?, ?)
        ");
        $bStmt->execute([
            $bookingCode, $customerId, $roomId, $checkIn, $checkOut, $numGuests, $specialRequests, $subtotal, $discountAmount, $taxAmount, $grandTotal
        ]);
        $bookingId = $pdo->lastInsertId();
        
        // Insert Selected Extra Services
        foreach ($validServices as $srv) {
            $bsStmt = $pdo->prepare("INSERT INTO booking_services (booking_id, service_id, quantity, total_price) VALUES (?, ?, 1, ?)");
            $bsStmt->execute([$bookingId, $srv['id'], $srv['price']]);
        }
        
        // Update Room Status to 'Reserved'
        $rUpdate = $pdo->prepare("UPDATE rooms SET status = 'Reserved' WHERE id = ?");
        $rUpdate->execute([$roomId]);
        
        $pdo->commit();
        
        // Notify Customer & Admin
        $custStmt = $pdo->prepare("SELECT user_id FROM customers WHERE id = ?");
        $custStmt->execute([$customerId]);
        $custUserId = $custStmt->fetchColumn();
        
        if ($custUserId) {
            createNotification($custUserId, "Booking Request Submitted", "Your booking request {$bookingCode} has been placed. Current status: Pending Approval.", "customer/my-bookings.php");
        }
        
        logActivity('Booking Created', "New booking {$bookingCode} created for Room ID {$roomId}.", $custUserId);
        
        return ['success' => true, 'booking_id' => $bookingId, 'booking_code' => $bookingCode, 'grand_total' => $grandTotal];
    } catch (PDOException $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()];
    }
}

/**
 * Update Booking Status (State Machine transition)
 */
function updateBookingStatus($bookingId, $newStatus, $userId = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT b.*, r.id as room_id, c.user_id as customer_user_id FROM bookings b JOIN rooms r ON b.room_id = r.id JOIN customers c ON b.customer_id = c.id WHERE b.id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        return ['success' => false, 'message' => 'Booking not found.'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update Booking Status
        $uStmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $uStmt->execute([$newStatus, $bookingId]);
        
        $roomId = $booking['room_id'];
        $customerUserId = $booking['customer_user_id'];
        
        // Trigger automated room status & task transitions
        if ($newStatus === 'Approved') {
            $pdo->prepare("UPDATE rooms SET status = 'Reserved' WHERE id = ?")->execute([$roomId]);
            createNotification($customerUserId, "Booking Approved!", "Your booking {$booking['booking_code']} has been approved by management.", "customer/my-bookings.php");
        } elseif ($newStatus === 'Checked-In') {
            $pdo->prepare("UPDATE rooms SET status = 'Occupied' WHERE id = ?")->execute([$roomId]);
            createNotification($customerUserId, "Checked-In Successfully", "Welcome to Grand Royale Hotel! You are checked into Room.", "customer/my-bookings.php");
        } elseif ($newStatus === 'Checked-Out') {
            // Set room to Cleaning and automatically assign Housekeeping Task
            $pdo->prepare("UPDATE rooms SET status = 'Cleaning' WHERE id = ?")->execute([$roomId]);
            
            // Auto assign housekeeping task
            $hStmt = $pdo->prepare("INSERT INTO housekeeping_tasks (room_id, task_type, status, priority, notes) VALUES (?, 'Cleaning', 'Pending', 'High', 'Guest checked out. Deep cleaning and linen replacement required.')");
            $hStmt->execute([$roomId]);
            
            createNotification($customerUserId, "Check-Out Complete", "Thank you for staying with us! Your invoice is ready for download.", "customer/my-bookings.php");
        } elseif ($newStatus === 'Cancelled' || $newStatus === 'Rejected') {
            $pdo->prepare("UPDATE rooms SET status = 'Available' WHERE id = ?")->execute([$roomId]);
            createNotification($customerUserId, "Booking Status Update", "Your booking {$booking['booking_code']} is now {$newStatus}.", "customer/my-bookings.php");
        } elseif ($newStatus === 'Completed') {
            $pdo->prepare("UPDATE rooms SET status = 'Available' WHERE id = ?")->execute([$roomId]);
        }
        
        $pdo->commit();
        logActivity('Booking Status Change', "Booking {$booking['booking_code']} status changed to {$newStatus}.", $userId);
        
        return ['success' => true, 'message' => "Booking status updated to {$newStatus}."];
    } catch (PDOException $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Failed to update status: ' . $e->getMessage()];
    }
}

/**
 * Send Notification
 */
function createNotification($userId, $title, $message, $link = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $message, $link]);
    } catch (PDOException $e) {
        error_log("Failed to insert notification: " . $e->getMessage());
    }
}
