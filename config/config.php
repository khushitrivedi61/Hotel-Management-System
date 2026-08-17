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

// Base URL Setup (Dynamic Detection for Render Cloud Hosting & Localhost XAMPP)
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$protocol = $isHttps ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Check if environment variable BASE_URL is set, otherwise compute dynamically
$envBaseUrl = getenv('BASE_URL');
if (!empty($envBaseUrl)) {
    define('BASE_URL', rtrim($envBaseUrl, '/'));
} else {
    // If running under XAMPP subfolder
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (strpos($scriptDir, '/HotelManagementSystem') !== false) {
        define('BASE_URL', $protocol . '://' . $host . '/HotelManagementSystem');
    } else {
        define('BASE_URL', $protocol . '://' . $host);
    }
}

define('ROOT_PATH', dirname(__DIR__));

// Database Configuration (Environment variables with Localhost XAMPP Fallbacks)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'hotel_management_db');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

// Security & Session Timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Error Reporting
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', ROOT_PATH . '/error_log.txt');

// Currency & Tax Settings
define('CURRENCY_SYMBOL', '₹');
define('DEFAULT_GST_PERCENT', 18.00);

// Timezone Setup
date_default_timezone_set('Asia/Kolkata');
