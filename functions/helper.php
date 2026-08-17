<?php
/**
 * GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
 * Helper Utilities & Sanitization Functions
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Sanitize HTML output to prevent XSS
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to specific URL safely
 */
function redirect($path) {
    if (strpos($path, 'http') !== 0) {
        $path = BASE_URL . '/' . ltrim($path, '/');
    }
    
    if (!headers_sent()) {
        header("Location: " . $path);
        exit();
    } else {
        echo "<script>window.location.href='" . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . "';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . "'></noscript>";
        exit();
    }
}

/**
 * Set Session Flash Message
 */
function setFlash($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/**
 * Display Session Flash Message
 */
function displayFlash() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_type'], $_SESSION['flash_message']);
        
        $icon = ($type === 'success') ? 'check-circle' : (($type === 'danger') ? 'exclamation-triangle' : 'info-circle');
        echo "
        <div class='alert alert-{$type} alert-dismissible fade show d-flex align-items-center shadow-sm my-3' role='alert'>
            <i class='fas fa-{$icon} me-2 fs-5'></i>
            <div>{$message}</div>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
}

/**
 * Format Currency (e.g. ₹ 5,500.00)
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format((float)$amount, 2);
}

/**
 * Format Date (e.g. 24 Jul 2026)
 */
function formatDate($dateStr, $format = 'd M Y') {
    if (!$dateStr) return 'N/A';
    return date($format, strtotime($dateStr));
}

/**
 * Generate Unique Code (e.g. GRB-202607-8912)
 */
function generateCode($prefix = 'GRB') {
    return $prefix . '-' . date('Ym') . '-' . rand(1000, 9999);
}

/**
 * Calculate total nights between check-in and check-out
 */
function calculateNights($checkIn, $checkOut) {
    $d1 = new DateTime($checkIn);
    $d2 = new DateTime($checkOut);
    $diff = $d1->diff($d2);
    return max(1, $diff->days);
}

/**
 * Fetch System Setting value by key
 */
function getSetting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $res = $stmt->fetchColumn();
        return $res !== false ? $res : $default;
    } catch (PDOException $e) {
        return $default;
    }
}
