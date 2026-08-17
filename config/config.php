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

// System Constants (Safely wrapped with if (!defined()))
if (!defined('APP_NAME')) define('APP_NAME', 'Grand Royale Hotel & Resort');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');

// Base URL Setup (Dynamic Detection for Render Cloud Hosting & Localhost XAMPP)
if (!defined('BASE_URL')) {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $isHttps ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $envBaseUrl = getenv('BASE_URL');
    if (!empty($envBaseUrl)) {
        define('BASE_URL', rtrim($envBaseUrl, '/'));
    } else {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if (strpos($scriptDir, '/HotelManagementSystem') !== false) {
            define('BASE_URL', $protocol . '://' . $host . '/HotelManagementSystem');
        } else {
            define('BASE_URL', $protocol . '://' . $host);
        }
    }
}

if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));

// Database Configuration (Environment variables with Localhost XAMPP Fallbacks)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'hotel_management_db');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// Security & Session Timeout (30 minutes)
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 1800);

// Error Reporting (Log errors to file, suppress warnings on screen for clean UI)
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', ROOT_PATH . '/error_log.txt');

// Currency & Tax Settings
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '₹');
if (!defined('DEFAULT_GST_PERCENT')) define('DEFAULT_GST_PERCENT', 18.00);

// Timezone Setup
date_default_timezone_set('Asia/Kolkata');
