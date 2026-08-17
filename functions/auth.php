<?php
/**
 * GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
 * Authentication & RBAC Engine
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helper.php';
require_once __DIR__ . '/log_function.php';

/**
 * Log user in
 */
function loginUser($email, $password) {
    global $pdo;
    
    $email = trim($email);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email address or password.'];
    }
    
    if ($user['account_status'] !== 'active') {
        return ['success' => false, 'message' => 'Your account is currently ' . ucfirst($user['account_status']) . '. Please contact administration.'];
    }
    
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email address or password.'];
    }
    
    // Set Session Variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['force_password_change'] = $user['force_password_change'];
    $_SESSION['profile_image'] = $user['profile_image'] ?? 'default-avatar.png';
    
    // Update Last Login
    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    logActivity('User Login', "User {$user['email']} logged in as " . ucfirst($user['role']), $user['id']);
    
    return [
        'success' => true, 
        'role' => $user['role'], 
        'force_password_change' => $user['force_password_change']
    ];
}

/**
 * Register a Customer
 */
function registerCustomer($name, $email, $phone, $password, $address = '', $city = '', $country = 'India', $idType = 'Aadhaar Card', $idNumber = '') {
    global $pdo;
    
    $email = trim($email);
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email address is already registered.'];
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $pdo->beginTransaction();
        
        // 1. Insert into Users table
        $userStmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, account_status) VALUES (?, ?, ?, ?, 'customer', 'active')");
        $userStmt->execute([$name, $email, $phone, $hashedPassword]);
        $userId = $pdo->lastInsertId();
        
        // 2. Insert into Customers table
        $custStmt = $pdo->prepare("INSERT INTO customers (user_id, address, city, country, id_type, id_number) VALUES (?, ?, ?, ?, ?, ?)");
        $custStmt->execute([$userId, $address, $city, $country, $idType, $idNumber]);
        
        $pdo->commit();
        
        logActivity('Customer Registration', "New customer registered: {$email}", $userId);
        
        return ['success' => true, 'message' => 'Registration successful! You can now log in.'];
    } catch (PDOException $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

/**
 * Check if current user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Enforce RBAC access on restricted pages
 */
function requireRole($allowedRoles) {
    if (!isLoggedIn()) {
        setFlash('danger', 'Please log in to access this page.');
        redirect('login.php');
    }
    
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        setFlash('danger', 'Unauthorized access denied for your user role.');
        redirect('index.php');
    }
    
    // Check if user is forced to change password
    if (isset($_SESSION['force_password_change']) && $_SESSION['force_password_change'] == 1 && basename($_SERVER['PHP_SELF']) !== 'change-password.php') {
        setFlash('warning', 'For security reasons, you must change your password on first login.');
        redirect('change-password.php');
    }
}

/**
 * Log User Out
 */
function logoutUser() {
    if (isset($_SESSION['user_id'])) {
        logActivity('User Logout', "User ID {$_SESSION['user_id']} logged out.", $_SESSION['user_id']);
    }
    session_unset();
    session_destroy();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    setFlash('info', 'You have been logged out successfully.');
    redirect('login.php');
}
