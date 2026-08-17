<?php
/**
 * GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
 * Room & Amenity Management Logic
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Get Image File Name for Room Type
 */
function getRoomImageFileName($typeName) {
    $name = strtolower((string)$typeName);
    if (strpos($name, 'presidential') !== false || strpos($name, 'villa') !== false) {
        return 'presidential_villa.jpeg';
    } elseif (strpos($name, 'deluxe') !== false) {
        return 'deluxe_double.jpeg';
    } elseif (strpos($name, 'suite') !== false || strpos($name, 'executive') !== false) {
        return 'luxury_suit.jpeg';
    } else {
        return 'normal_room.jpeg';
    }
}

/**
 * Get Available Rooms for specific date range and filters
 */
function getAvailableRooms($checkIn, $checkOut, $roomTypeId = null, $capacity = null, $acStatus = null, $minPrice = null, $maxPrice = null) {
    global $pdo;
    
    $sql = "SELECT r.*, rt.type_name, rt.capacity, rt.ac_status, rt.cover_image, rt.description as type_desc 
            FROM rooms r 
            JOIN room_types rt ON r.room_type_id = rt.id 
            WHERE r.status != 'Maintenance' 
            AND r.id NOT IN (
                SELECT room_id FROM bookings 
                WHERE status IN ('Approved', 'Checked-In', 'Pending') 
                AND (
                    (check_in_date < ? AND check_out_date > ?)
                )
            )";
    
    $params = [$checkOut, $checkIn];
    
    if (!empty($roomTypeId)) {
        $sql .= " AND r.room_type_id = ?";
        $params[] = $roomTypeId;
    }
    
    if (!empty($capacity)) {
        $sql .= " AND rt.capacity >= ?";
        $params[] = $capacity;
    }
    
    if (!empty($acStatus)) {
        $sql .= " AND rt.ac_status = ?";
        $params[] = $acStatus;
    }
    
    if (!empty($minPrice)) {
        $sql .= " AND r.price_per_night >= ?";
        $params[] = $minPrice;
    }
    
    if (!empty($maxPrice)) {
        $sql .= " AND r.price_per_night <= ?";
        $params[] = $maxPrice;
    }
    
    $sql .= " ORDER BY r.price_per_night ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get Room details by ID
 */
function getRoomById($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, rt.type_name, rt.capacity, rt.ac_status, rt.cover_image, rt.description as type_desc 
        FROM rooms r 
        JOIN room_types rt ON r.room_type_id = rt.id 
        WHERE r.id = ? LIMIT 1
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get Amenities for a specific room type
 */
function getAmenitiesByRoomType($roomTypeId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.* FROM amenities a 
        JOIN room_type_amenities rta ON a.id = rta.amenity_id 
        WHERE rta.room_type_id = ?
    ");
    $stmt->execute([$roomTypeId]);
    return $stmt->fetchAll();
}

/**
 * Get all room types
 */
function getAllRoomTypes() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM room_types ORDER BY base_price ASC");
    return $stmt->fetchAll();
}

/**
 * Get room counts by status
 */
function getRoomStatusCounts() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM rooms 
        GROUP BY status
    ");
    $results = $stmt->fetchAll();
    
    $counts = [
        'Total' => 0,
        'Available' => 0,
        'Reserved' => 0,
        'Occupied' => 0,
        'Cleaning' => 0,
        'Maintenance' => 0
    ];
    
    foreach ($results as $row) {
        $counts[$row['status']] = (int)$row['count'];
        $counts['Total'] += (int)$row['count'];
    }
    
    return $counts;
}
