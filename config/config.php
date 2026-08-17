<?php
/**
 * GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
 * Global Configuration File
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Start Output Buffering to prevent "Headers already sent" issues
if (ob_get_level() == 0) {
    ob_start();
}

// System Constants
define('APP_NAME', 'Grand Royale Hotel & Resort');
define('APP_VERSION', '1.0.0');

// Base URL Setup (Dynamic Detection for XAMPP / Localhost)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detect subfolder path safely
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptDir === '/') {
    $subfolder = '';
} else {
    // Standard XAMPP folder detection
    $subfolder = strpos($scriptDir, '/HotelManagementSystem') !== false ? '/HotelManagementSystem' : $scriptDir;
}

define('BASE_URL', $protocol . '://' . $host . '/HotelManagementSystem');
define('ROOT_PATH', dirname(__DIR__));

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP MySQL password is empty
define('DB_NAME', 'hotel_management_db');
define('DB_CHARSET', 'utf8mb4');

// Security & Session Timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Error Reporting (Display errors cleanly during development, log gracefully)
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', ROOT_PATH . '/error_log.txt');

// Currency & Tax Settings
define('CURRENCY_SYMBOL', '₹');
define('DEFAULT_GST_PERCENT', 18.00);

// Timezone Setup
date_default_timezone_set('Asia/Kolkata');
