<?php
/**
 * GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
 * Database PDO Connection Engine & User Auto-Healer
 * Supports MySQL & Automatic SQLite Fallback for Zero-Config Cloud Deployments
 */

require_once __DIR__ . '/config.php';

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $dbHost = defined('DB_HOST') ? DB_HOST : 'localhost';
        $dbPort = defined('DB_PORT') ? DB_PORT : '3306';
        $dbName = defined('DB_NAME') ? DB_NAME : 'hotel_management_db';
        $dbUser = defined('DB_USER') ? DB_USER : 'root';
        $dbPass = defined('DB_PASS') ? DB_PASS : '';

        // Attempt 1: Connect to MySQL
        try {
            $dsn = "mysql:host=" . $dbHost . ";port=" . $dbPort . ";dbname=" . $dbName . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
            ensureDefaultUsersExist($pdo, 'mysql');

        } catch (PDOException $e) {
            // Attempt 2: Try creating MySQL database automatically if host is local
            if ($dbHost === 'localhost' || $dbHost === '127.0.0.1') {
                try {
                    $rootDsn = "mysql:host=" . $dbHost . ";port=" . $dbPort . ";charset=" . DB_CHARSET;
                    $tmpPdo = new PDO($rootDsn, $dbUser, $dbPass);
                    $tmpPdo->exec("CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    
                    $pdo = new PDO("mysql:host=" . $dbHost . ";port=" . $dbPort . ";dbname=" . $dbName . ";charset=" . DB_CHARSET, $dbUser, $dbPass, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                    
                    $sqlFile = ROOT_PATH . '/database/schema.sql';
                    if (file_exists($sqlFile)) {
                        $sql = file_get_contents($sqlFile);
                        $pdo->exec($sql);
                    }

                    ensureDefaultUsersExist($pdo, 'mysql');
                    return $pdo;

                } catch (PDOException $ex) {
                    // Fallthrough to SQLite zero-config fallback
                }
            }

            // Attempt 3: SQLite Fallback (Zero-Config Deployment for Render / Cloud)
            try {
                $sqlitePath = ROOT_PATH . '/database/hotel.sqlite';
                $dbDir = dirname($sqlitePath);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0777, true);
                }

                $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                initializeSQLiteSchema($pdo);
                ensureDefaultUsersExist($pdo, 'sqlite');

            } catch (Exception $sqliteEx) {
                die("<div style='font-family:sans-serif; padding:20px; background:#f8d7da; color:#721c24; border-radius:5px;'>
                    <h2>Database Connection Error</h2>
                    <p>Unable to initialize database connection.</p>
                    <p><strong>Error:</strong> " . htmlspecialchars($sqliteEx->getMessage()) . "</p>
                </div>");
            }
        }
    }
    return $pdo;
}

/**
 * Initialize SQLite Database Schema for Zero-Config Deployment
 */
function initializeSQLiteSchema($pdo) {
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'customer',
            account_status TEXT NOT NULL DEFAULT 'active',
            force_password_change INTEGER DEFAULT 0,
            profile_image TEXT DEFAULT 'default-avatar.png',
            last_login DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );",

        "CREATE TABLE IF NOT EXISTS customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            address TEXT,
            city TEXT,
            country TEXT DEFAULT 'India',
            id_type TEXT DEFAULT 'Aadhaar Card',
            id_number TEXT,
            id_proof_doc TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );",

        "CREATE TABLE IF NOT EXISTS staff (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            employee_code TEXT NOT NULL UNIQUE,
            department TEXT NOT NULL,
            designation TEXT DEFAULT 'Staff Member',
            salary REAL DEFAULT 0.00,
            date_of_joining DATE,
            status TEXT DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );",

        "CREATE TABLE IF NOT EXISTS room_types (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type_name TEXT NOT NULL UNIQUE,
            base_price REAL NOT NULL,
            capacity INTEGER NOT NULL DEFAULT 2,
            ac_status TEXT NOT NULL DEFAULT 'AC',
            description TEXT,
            cover_image TEXT DEFAULT 'default-room.jpg',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );",

        "CREATE TABLE IF NOT EXISTS rooms (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            room_number TEXT NOT NULL UNIQUE,
            room_type_id INTEGER NOT NULL,
            floor INTEGER NOT NULL DEFAULT 1,
            price_per_night REAL NOT NULL,
            status TEXT NOT NULL DEFAULT 'Available',
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );",

        "CREATE TABLE IF NOT EXISTS amenities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            icon_class TEXT DEFAULT 'fa-concierge-bell',
            description TEXT
        );",

        "CREATE TABLE IF NOT EXISTS room_type_amenities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            room_type_id INTEGER NOT NULL,
            amenity_id INTEGER NOT NULL
        );",

        "CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            booking_code TEXT NOT NULL UNIQUE,
            customer_id INTEGER NOT NULL,
            room_id INTEGER NOT NULL,
            check_in_date DATE NOT NULL,
            check_out_date DATE NOT NULL,
            num_guests INTEGER NOT NULL DEFAULT 1,
            status TEXT NOT NULL DEFAULT 'Pending',
            special_requests TEXT,
            subtotal REAL NOT NULL DEFAULT 0.00,
            discount_amount REAL NOT NULL DEFAULT 0.00,
            tax_amount REAL NOT NULL DEFAULT 0.00,
            grand_total REAL NOT NULL DEFAULT 0.00,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );",

        "CREATE TABLE IF NOT EXISTS extra_services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            service_name TEXT NOT NULL,
            price REAL NOT NULL,
            description TEXT,
            status TEXT DEFAULT 'Active'
        );",

        "CREATE TABLE IF NOT EXISTS coupons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT NOT NULL UNIQUE,
            discount_percent REAL NOT NULL,
            max_discount REAL,
            valid_from DATE NOT NULL,
            valid_to DATE NOT NULL,
            status TEXT DEFAULT 'Active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );",

        "CREATE TABLE IF NOT EXISTS payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            payment_code TEXT NOT NULL UNIQUE,
            booking_id INTEGER NOT NULL,
            customer_id INTEGER NOT NULL,
            amount REAL NOT NULL,
            payment_method TEXT NOT NULL,
            payment_status TEXT NOT NULL DEFAULT 'Paid',
            transaction_ref TEXT,
            paid_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );",

        "CREATE TABLE IF NOT EXISTS invoices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            invoice_number TEXT NOT NULL UNIQUE,
            booking_id INTEGER NOT NULL,
            payment_id INTEGER,
            issue_date DATE NOT NULL,
            due_date DATE NOT NULL,
            room_charges REAL NOT NULL,
            service_charges REAL DEFAULT 0.00,
            gst_amount REAL NOT NULL,
            discount_amount REAL DEFAULT 0.00,
            grand_total REAL NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );",

        "CREATE TABLE IF NOT EXISTS housekeeping_tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            room_id INTEGER NOT NULL,
            staff_id INTEGER,
            task_type TEXT NOT NULL DEFAULT 'Cleaning',
            status TEXT NOT NULL DEFAULT 'Pending',
            priority TEXT NOT NULL DEFAULT 'Medium',
            notes TEXT,
            assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME
        );",

        "CREATE TABLE IF NOT EXISTS reviews (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_id INTEGER NOT NULL,
            booking_id INTEGER NOT NULL,
            rating INTEGER NOT NULL,
            review_title TEXT NOT NULL,
            review_text TEXT NOT NULL,
            status TEXT DEFAULT 'Approved',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );",

        "CREATE TABLE IF NOT EXISTS activity_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action TEXT NOT NULL,
            description TEXT NOT NULL,
            ip_address TEXT DEFAULT '127.0.0.1',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );",

        "CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT NOT NULL UNIQUE,
            setting_value TEXT,
            setting_group TEXT DEFAULT 'general'
        );"
    ];

    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
        } catch (Exception $e) {}
    }
}

/**
 * Ensure default System Users & Seed Data exist
 */
function ensureDefaultUsersExist($pdo, $driver = 'mysql') {
    try {
        $pdo->exec("UPDATE room_types SET capacity = 4 WHERE LOWER(type_name) = 'deluxe double'");
    } catch (Exception $ex) {}

    // Seed Room Types if empty
    try {
        $rtCount = $pdo->query("SELECT COUNT(*) FROM room_types")->fetchColumn();
        if ($rtCount == 0) {
            $pdo->exec("INSERT INTO room_types (id, type_name, base_price, capacity, ac_status, description) VALUES
                (1, 'Executive Suite', 5500.00, 2, 'AC', 'Spacious suite with king-size plush bed, ocean view balcony, and luxury lounge area.'),
                (2, 'Presidential Villa', 12500.00, 4, 'AC', 'Ultra-luxurious multi-room villa featuring private Jacuzzi, dining space, and butler service.'),
                (3, 'Deluxe Double', 3800.00, 4, 'AC', 'Elegantly furnished double bedroom with modern amenities accommodating up to 4 guests.'),
                (4, 'Standard Classic', 2500.00, 2, 'Non AC', 'Cozy and comfortable standard room equipped with essential luxury amenities.');");

            $pdo->exec("INSERT INTO rooms (id, room_number, room_type_id, floor, price_per_night, status, description) VALUES
                (1, '101', 4, 1, 2500.00, 'Available', 'Ground floor classic room with quiet garden view.'),
                (2, '102', 3, 1, 3800.00, 'Available', 'First floor deluxe room accommodating 4 guests.'),
                (3, '201', 1, 2, 5500.00, 'Available', 'Second floor luxury suite with ocean balcony.'),
                (4, '202', 1, 2, 5500.00, 'Reserved', 'Second floor luxury suite reserved for upcoming guest.'),
                (5, '301', 2, 3, 12500.00, 'Available', 'Penthouse presidential villa with private Jacuzzi and sun deck.');");

            $pdo->exec("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
                ('hotel_name', 'Grand Royale Hotel & Resort', 'general'),
                ('hotel_tagline', 'Where Luxury Meets Exceptional Elegance', 'general'),
                ('currency_symbol', '₹', 'general');");
        }
    } catch (Exception $e) {}

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
                $pdo->prepare("INSERT INTO staff (user_id, employee_code, department, designation, salary, date_of_joining, status) VALUES (?, ?, ?, 'Staff', 30000.00, CURRENT_DATE, 'Active')")->execute([$newUserId, $u['code'], $u['dept']]);
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
