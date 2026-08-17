<?php
/**
 * GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
 * Activity Audit Log Engine
 */

require_once __DIR__ . '/../config/db.php';

function logActivity($action, $description, $userId = null) {
    global $pdo;
    try {
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $description, $ip]);
    } catch (PDOException $e) {
        error_log("Failed to insert activity log: " . $e->getMessage());
    }
}
