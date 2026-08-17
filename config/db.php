<?php
/**
 * GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
 * Database PDO Connection Engine & User Auto-Healer
 */

require_once __DIR__ . '/config.php';

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $port = defined('DB_PORT') ? DB_PORT : '3306';
            $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Auto-ensure default accounts, capacity updates, and password hashes
            ensureDefaultUsersExist($pdo);

        } catch (PDOException $e) {
            // Attempt auto-creation of database if db doesn't exist yet
            try {
                $port = defined('DB_PORT') ? DB_PORT : '3306';
                $rootDsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";charset=" . DB_CHARSET;
                $tmpPdo = new PDO($rootDsn, DB_USER, DB_PASS);
                $tmpPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Re-connect
                $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                
                // Import schema if schema file exists
                $sqlFile = ROOT_PATH . '/database/schema.sql';
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    $pdo->exec($sql);
                }

                ensureDefaultUsersExist($pdo);

            } catch (PDOException $ex) {
                die("<div style='font-family:sans-serif; padding:20px; background:#f8d7da; color:#721c24; border-radius:5px;'>
                    <h2>Database Connection Error</h2>
                    <p>Unable to connect to MySQL database.</p>
                    <p><strong>Error:</strong> " . htmlspecialchars($ex->getMessage()) . "</p>
                    <p><em>Please ensure your MySQL database is active and environment variables are set.</em></p>
                </div>");
            }
        }
    }
    return $pdo;
}

/**
 * Ensure default System Users & Room Capacity updates exist
 */
function ensureDefaultUsersExist($pdo) {
    try {
        $pdo->exec("UPDATE room_types SET capacity = 4 WHERE LOWER(type_name) = 'deluxe double'");
    } catch (Exception $ex) {}

    $defaults = [
        [
            'name' => 'System Administrator',
            'email' => 'admin@hotel.com',
            'phone' => '9876543210',
            'password' => 'admin123',
            'role' => 'admin'
        ],
        [
            'name' => 'Sarah Jenkins',
            'email' => 'reception@hotel.com',
            'phone' => '9876543211',
            'password' => 'staff123',
            'role' => 'receptionist',
            'dept' => 'Reception',
            'code' => 'EMP-REC-001'
        ],
        [
            'name' => 'Michael Scott',
            'email' => 'housekeeping@hotel.com',
            'phone' => '9876543212',
            'password' => 'staff123',
            'role' => 'housekeeping',
            'dept' => 'Housekeeping',
            'code' => 'EMP-HSK-001'
        ],
        [
            'name' => 'Rajesh Kumar',
            'email' => 'customer@example.com',
            'phone' => '9876543213',
            'password' => 'customer123',
            'role' => 'customer'
        ]
    ];

    foreach ($defaults as $u) {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $stmt->execute([$u['email']]);
        $existing = $stmt->fetch();

        if (!$existing) {
            $hash = password_hash($u['password'], PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, account_status, force_password_change) VALUES (?, ?, ?, ?, ?, 'active', 0)");
            $ins->execute([$u['name'], $u['email'], $u['phone'], $hash, $u['role']]);
            $newUserId = $pdo->lastInsertId();

            if ($u['role'] === 'customer') {
                $pdo->prepare("INSERT INTO customers (user_id, address, city, country, id_type, id_number) VALUES (?, '45 Park Avenue', 'Bangalore', 'India', 'Aadhaar Card', '4589-1234-5678')")->execute([$newUserId]);
            } elseif ($u['role'] === 'receptionist' || $u['role'] === 'housekeeping') {
                $pdo->prepare("INSERT INTO staff (user_id, employee_code, department, designation, salary, date_of_joining, status) VALUES (?, ?, ?, 'Staff', 30000.00, CURDATE(), 'Active')")->execute([$newUserId, $u['code'], $u['dept']]);
            }
        } else {
            if (!password_verify($u['password'], $existing['password'])) {
                $newHash = password_hash($u['password'], PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->execute([$newHash, $existing['id']]);
            }
        }
    }
}

// Global PDO Instance variable for quick access
$pdo = getDBConnection();
