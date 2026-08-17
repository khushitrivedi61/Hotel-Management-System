-- =========================================================
-- GRAND ROYALE HOTEL & RESORT - DATABASE SCHEMA
-- Target Engine: MySQL 5.7+ / MariaDB 10.2+
-- =========================================================

CREATE DATABASE IF NOT EXISTS `hotel_management_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hotel_management_db`;

-- ---------------------------------------------------------
-- 1. USERS TABLE (Unified Authentication Engine)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'receptionist', 'housekeeping', 'customer') NOT NULL DEFAULT 'customer',
  `account_status` ENUM('active', 'inactive', 'suspended', 'blocked') NOT NULL DEFAULT 'active',
  `force_password_change` TINYINT(1) DEFAULT 0,
  `profile_image` VARCHAR(255) DEFAULT 'default-avatar.png',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 2. CUSTOMERS TABLE (Extended Customer Data)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `id_type` ENUM('Aadhaar Card', 'Passport', 'Driving License', 'Voter ID') DEFAULT 'Aadhaar Card',
  `id_number` VARCHAR(100) DEFAULT NULL,
  `id_proof_doc` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 3. STAFF TABLE (Employee Management)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `staff` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `employee_code` VARCHAR(50) NOT NULL UNIQUE,
  `department` ENUM('Reception', 'Housekeeping', 'Kitchen', 'Security', 'Manager') NOT NULL,
  `designation` VARCHAR(100) DEFAULT 'Staff Member',
  `salary` DECIMAL(10,2) DEFAULT 0.00,
  `date_of_joining` DATE DEFAULT NULL,
  `status` ENUM('Active', 'Inactive', 'On Leave', 'Terminated') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 4. ROOM TYPES TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `room_types` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `type_name` VARCHAR(100) NOT NULL UNIQUE,
  `base_price` DECIMAL(10,2) NOT NULL,
  `capacity` INT NOT NULL DEFAULT 2,
  `ac_status` ENUM('AC', 'Non AC') NOT NULL DEFAULT 'AC',
  `description` TEXT DEFAULT NULL,
  `cover_image` VARCHAR(255) DEFAULT 'default-room.jpg',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 5. ROOMS TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_number` VARCHAR(20) NOT NULL UNIQUE,
  `room_type_id` INT NOT NULL,
  `floor` INT NOT NULL DEFAULT 1,
  `price_per_night` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Available', 'Reserved', 'Occupied', 'Cleaning', 'Maintenance') NOT NULL DEFAULT 'Available',
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`room_type_id`) REFERENCES `room_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 6. AMENITIES TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `amenities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `icon_class` VARCHAR(100) DEFAULT 'fa-concierge-bell',
  `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 7. ROOM TYPE AMENITIES TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `room_type_amenities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_type_id` INT NOT NULL,
  `amenity_id` INT NOT NULL,
  FOREIGN KEY (`room_type_id`) REFERENCES `room_types`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`amenity_id`) REFERENCES `amenities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 8. ROOM IMAGES TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `room_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 9. BOOKINGS TABLE (Core Booking Engine)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_code` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` INT NOT NULL,
  `room_id` INT NOT NULL,
  `check_in_date` DATE NOT NULL,
  `check_out_date` DATE NOT NULL,
  `num_guests` INT NOT NULL DEFAULT 1,
  `status` ENUM('Pending', 'Approved', 'Checked-In', 'Checked-Out', 'Completed', 'Cancelled', 'Rejected') NOT NULL DEFAULT 'Pending',
  `special_requests` TEXT DEFAULT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 10. EXTRA SERVICES TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `extra_services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_name` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 11. BOOKING EXTRA SERVICES TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `booking_services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `quantity` INT DEFAULT 1,
  `total_price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `extra_services`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 12. COUPONS TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_percent` DECIMAL(5,2) NOT NULL,
  `max_discount` DECIMAL(10,2) DEFAULT NULL,
  `valid_from` DATE NOT NULL,
  `valid_to` DATE NOT NULL,
  `status` ENUM('Active', 'Expired', 'Disabled') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 13. PAYMENTS TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `payment_code` VARCHAR(50) NOT NULL UNIQUE,
  `booking_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('Cash', 'UPI', 'Credit Card', 'Debit Card', 'Net Banking', 'Wallet') NOT NULL,
  `payment_status` ENUM('Pending', 'Paid', 'Failed', 'Refunded', 'Partial Payment') NOT NULL DEFAULT 'Paid',
  `transaction_ref` VARCHAR(100) DEFAULT NULL,
  `paid_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 14. INVOICES TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `booking_id` INT NOT NULL,
  `payment_id` INT DEFAULT NULL,
  `issue_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `room_charges` DECIMAL(10,2) NOT NULL,
  `service_charges` DECIMAL(10,2) DEFAULT 0.00,
  `gst_amount` DECIMAL(10,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `grand_total` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 15. HOUSEKEEPING TASKS TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `housekeeping_tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT NOT NULL,
  `staff_id` INT DEFAULT NULL,
  `task_type` ENUM('Cleaning', 'Maintenance', 'Inspection', 'Linen Change') NOT NULL DEFAULT 'Cleaning',
  `status` ENUM('Pending', 'In Progress', 'Completed') NOT NULL DEFAULT 'Pending',
  `priority` ENUM('Low', 'Medium', 'High', 'Emergency') NOT NULL DEFAULT 'Medium',
  `notes` TEXT DEFAULT NULL,
  `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME DEFAULT NULL,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`staff_id`) REFERENCES `staff`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 16. MAINTENANCE REQUESTS TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `maintenance_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT NOT NULL,
  `reported_by_staff_id` INT DEFAULT NULL,
  `issue_description` TEXT NOT NULL,
  `priority` ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
  `status` ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME DEFAULT NULL,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reported_by_staff_id`) REFERENCES `staff`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 17. REVIEWS TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `booking_id` INT NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `review_title` VARCHAR(150) NOT NULL,
  `review_text` TEXT NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Hidden') DEFAULT 'Approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 18. ATTENDANCE TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `staff_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `clock_in` TIME DEFAULT NULL,
  `clock_out` TIME DEFAULT NULL,
  `status` ENUM('Present', 'Absent', 'Leave', 'Late') DEFAULT 'Present',
  FOREIGN KEY (`staff_id`) REFERENCES `staff`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 19. LEAVE REQUESTS TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `staff_id` INT NOT NULL,
  `leave_type` ENUM('Sick Leave', 'Casual Leave', 'Paid Leave', 'Emergency') NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`staff_id`) REFERENCES `staff`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 20. NOTIFICATIONS TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 21. CONTACT MESSAGES TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('Unread', 'Replied', 'Archived') DEFAULT 'Unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 22. ACTIVITY LOGS TABLE (Audit Trail)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT '127.0.0.1',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- 23. SETTINGS TABLE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `setting_group` VARCHAR(50) DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =========================================================
-- INITIAL SEED DATA
-- =========================================================

-- 1. Insert Default System Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('hotel_name', 'Grand Royale Hotel & Resort', 'general'),
('hotel_tagline', 'Where Luxury Meets Exceptional Elegance', 'general'),
('hotel_email', 'info@grandroyalehotel.com', 'general'),
('hotel_phone', '+91 98765 43210', 'general'),
('hotel_address', 'Beach Road, Luxury Enclave, Goa 403001, India', 'general'),
('gst_number', '22AAAAA0000A1Z5', 'tax'),
('tax_percentage', '18.00', 'tax'),
('currency_symbol', '₹', 'general'),
('checkin_time', '14:00', 'policy'),
('checkout_time', '11:00', 'policy')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- 2. Insert Default Amenities
INSERT INTO `amenities` (`id`, `name`, `icon_class`, `description`) VALUES
(1, 'High-Speed Wi-Fi', 'fa-wifi', 'Complimentary 1Gbps Fiber Wi-Fi'),
(2, 'Smart LED TV', 'fa-tv', '55 inch 4K Ultra HD TV with Netflix & Prime'),
(3, 'Central Air Conditioning', 'fa-snowflake', 'Climate controlled HVAC system'),
(4, 'Mini Refrigerator', 'fa-temperature-low', 'Stocked with beverages and snacks'),
(5, 'Ocean View Balcony', 'fa-water', 'Private balcony overlooking the Arabian Sea'),
(6, 'Complementary Breakfast', 'fa-utensils', 'Buffet breakfast included at Grand Dining'),
(7, 'Jacuzzi Bath', 'fa-hot-tub', 'Private Jacuzzi tub in bathroom'),
(8, '24/7 Room Service', 'fa-concierge-bell', 'Round-the-clock gourmet dining service')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- 3. Insert Room Types
INSERT INTO `room_types` (`id`, `type_name`, `base_price`, `capacity`, `ac_status`, `description`) VALUES
(1, 'Executive Suite', 5500.00, 2, 'AC', 'Spacious suite with king-size plush bed, ocean view balcony, and luxury lounge area.'),
(2, 'Presidential Villa', 12500.00, 4, 'AC', 'Ultra-luxurious multi-room villa featuring private Jacuzzi, dining space, and butler service.'),
(3, 'Deluxe Double', 3800.00, 4, 'AC', 'Elegantly furnished double bedroom with modern amenities accommodating up to 4 guests.'),
(4, 'Standard Classic', 2500.00, 2, 'Non AC', 'Cozy and comfortable standard room equipped with essential luxury amenities.')
ON DUPLICATE KEY UPDATE type_name = VALUES(type_name);

-- 4. Insert Physical Rooms
INSERT INTO `rooms` (`id`, `room_number`, `room_type_id`, `floor`, `price_per_night`, `status`, `description`) VALUES
(1, '101', 4, 1, 2500.00, 'Available', 'Ground floor classic room with quiet garden view.'),
(2, '102', 3, 1, 3800.00, 'Available', 'First floor deluxe room accommodating 4 guests.'),
(3, '201', 1, 2, 5500.00, 'Available', 'Second floor luxury suite with ocean balcony.'),
(4, '202', 1, 2, 5500.00, 'Reserved', 'Second floor luxury suite reserved for upcoming guest.'),
(5, '301', 2, 3, 12500.00, 'Available', 'Penthouse presidential villa with private Jacuzzi and sun deck.'),
(6, '302', 3, 3, 3800.00, 'Occupied', 'Third floor deluxe room currently checked-in.'),
(7, '103', 4, 1, 2500.00, 'Cleaning', 'Room currently undergoing housekeeping deep clean.'),
(8, '203', 1, 2, 5500.00, 'Maintenance', 'Under routine HVAC unit maintenance.')
ON DUPLICATE KEY UPDATE room_number = VALUES(room_number);

-- 5. Insert Coupons
INSERT INTO `coupons` (`id`, `code`, `discount_percent`, `max_discount`, `valid_from`, `valid_to`, `status`) VALUES
(1, 'WELCOME10', 10.00, 1000.00, '2026-01-01', '2026-12-31', 'Active'),
(2, 'SUMMER25', 25.00, 2500.00, '2026-05-01', '2026-08-31', 'Active'),
(3, 'FESTIVE20', 20.00, 2000.00, '2026-10-01', '2026-11-30', 'Active')
ON DUPLICATE KEY UPDATE code = VALUES(code);

-- 6. Insert Extra Services
INSERT INTO `extra_services` (`id`, `service_name`, `price`, `description`, `status`) VALUES
(1, 'Airport Pickup & Drop', 1200.00, 'Luxury sedan transfer from Airport to Hotel', 'Active'),
(2, 'Express Laundry', 500.00, 'Same-day dry cleaning and ironing service', 'Active'),
(3, 'Full Body Spa Therapy', 2500.00, '60-minute relaxing Swedish aromatherapy session', 'Active'),
(4, 'Gourmet Buffet Breakfast', 650.00, 'Multi-cuisine breakfast spread at Grand Dining', 'Active')
ON DUPLICATE KEY UPDATE service_name = VALUES(service_name);
