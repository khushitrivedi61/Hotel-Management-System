<?php
/**
 * GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
 * Invoice Generation & Simulated Payment Handler
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helper.php';
require_once __DIR__ . '/booking_functions.php';

/**
 * Process Simulated Payment & Generate Invoice
 */
function processPaymentAndInvoice($bookingId, $paymentMethod, $transactionRef = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT b.*, c.user_id as customer_user_id FROM bookings b JOIN customers c ON b.customer_id = c.id WHERE b.id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        return ['success' => false, 'message' => 'Booking not found.'];
    }
    
    if (empty($transactionRef)) {
        $transactionRef = strtoupper($paymentMethod) . '/' . date('YmdHis') . '/SIMULATED';
    }
    
    $paymentCode = generateCode('PAY');
    $invoiceNumber = generateCode('INV');
    
    try {
        $pdo->beginTransaction();
        
        // 1. Insert Payment
        $payStmt = $pdo->prepare("
            INSERT INTO payments (payment_code, booking_id, customer_id, amount, payment_method, payment_status, transaction_ref) 
            VALUES (?, ?, ?, ?, ?, 'Paid', ?)
        ");
        $payStmt->execute([
            $paymentCode, $bookingId, $booking['customer_id'], $booking['grand_total'], $paymentMethod, $transactionRef
        ]);
        $paymentId = $pdo->lastInsertId();
        
        // 2. Insert Invoice
        $dueDate = date('Y-m-d', strtotime($booking['check_out_date']));
        $invStmt = $pdo->prepare("
            INSERT INTO invoices (invoice_number, booking_id, payment_id, issue_date, due_date, room_charges, service_charges, gst_amount, discount_amount, grand_total) 
            VALUES (?, ?, ?, CURDATE(), ?, ?, 0.00, ?, ?, ?)
        ");
        $invStmt->execute([
            $invoiceNumber, $bookingId, $paymentId, $dueDate, $booking['subtotal'], $booking['tax_amount'], $booking['discount_amount'], $booking['grand_total']
        ]);
        $invoiceId = $pdo->lastInsertId();
        
        // 3. Update Booking Status if it was pending
        if ($booking['status'] === 'Pending') {
            $pdo->prepare("UPDATE bookings SET status = 'Approved' WHERE id = ?")->execute([$bookingId]);
            $pdo->prepare("UPDATE rooms SET status = 'Reserved' WHERE id = ?")->execute([$booking['room_id']]);
        }
        
        $pdo->commit();
        
        createNotification($booking['customer_user_id'], "Payment Successful", "Payment of " . formatCurrency($booking['grand_total']) . " received for Invoice {$invoiceNumber}.", "customer/invoice.php?id=" . $invoiceId);
        
        logActivity('Payment Received', "Payment of " . formatCurrency($booking['grand_total']) . " processed via {$paymentMethod} for Booking {$booking['booking_code']}.", $booking['customer_user_id']);
        
        return ['success' => true, 'invoice_id' => $invoiceId, 'invoice_number' => $invoiceNumber];
    } catch (PDOException $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Payment processing failed: ' . $e->getMessage()];
    }
}

/**
 * Get Full Invoice Details by ID
 */
function getInvoiceDetails($invoiceId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT i.*, 
               b.booking_code, b.check_in_date, b.check_out_date, b.num_guests, b.special_requests,
               r.room_number, rt.type_name as room_type,
               u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
               c.address, c.city, c.country, c.id_type, c.id_number,
               p.payment_method, p.payment_status, p.transaction_ref, p.paid_at
        FROM invoices i
        JOIN bookings b ON i.booking_id = b.id
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        LEFT JOIN payments p ON i.payment_id = p.id
        WHERE i.id = ? LIMIT 1
    ");
    $stmt->execute([$invoiceId]);
    return $stmt->fetch();
}
