# GRAND ROYALE HOTEL & RESORT MANAGEMENT SYSTEM
### Professional BCA Major Project (Core PHP + MySQL + Bootstrap 5 + AJAX + Chart.js)

---

## 🌟 Overview
**Grand Royale Hotel & Resort Management System** is a complete, fully dynamic commercial-grade hotel software developed for deployment on **XAMPP**. Built without frameworks using **Core PHP**, **PDO**, **MySQL**, **Bootstrap 5**, **JavaScript (AJAX)**, **Chart.js**, and **Font Awesome**.

---

## 🔑 Default Credentials & Role-Based Access Control (RBAC)

All accounts share the unified login portal at `http://localhost/HotelManagementSystem/login.php`.

| Role | Email | Password | Access Level & Permissions |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@hotel.com` | `admin123` | Full System Control, Executive Dashboard, Room & Category CRUD, Staff & RBAC Management, Financial Reports, Settings & DB Backup |
| **Receptionist** | `reception@hotel.com` | `staff123` | Front Desk Console, Walk-in Registrations, Check-In Room Key Assignment, Check-Out & Final Billing, Live Room Matrix |
| **Housekeeping** | `housekeeping@hotel.com` | `staff123` | Room Cleaning Task Management, Automated Room Readiness Update (`Cleaning` -> `Available`), Maintenance Issue Reporting |
| **Customer** | `customer@example.com` | `customer123` | Room Availability Search, Instant Booking Engine, Simulated Multi-Method Payments, Download Tax Invoice, Review Rating Submission |

---

## ⚙️ XAMPP Installation & Setup Instructions

1. **Copy Project Folder**:
   Copy the `HotelManagementSystem` directory into your XAMPP root folder:
   `C:\xampp\htdocs\HotelManagementSystem` (or `D:\xampp\htdocs\HotelManagementSystem`)

2. **Start Apache & MySQL**:
   Open **XAMPP Control Panel** and click **Start** next to **Apache** and **MySQL**.

3. **Import MySQL Database**:
   - Open your browser and navigate to `http://localhost/phpmyadmin/`
   - Click on **New** to create a database named `hotel_management_db`
   - Click on the **Import** tab, browse for `D:\HotelManagementSystem\database\schema.sql`, and click **Go**.
   *(Note: The system also includes an auto-creation fallback in `config/db.php` if `schema.sql` is accessed directly).*

4. **Launch Application**:
   Navigate to `http://localhost/HotelManagementSystem/` in your browser.

---

## 📁 System Folder Structure

```
HotelManagementSystem/
├── admin/                 # Executive Control Panel (Dashboard, Rooms, Staff, Bookings, Reports, Settings, Logs, Backup)
├── staff/
│   ├── reception/        # Front Desk Operations (Check-In, Check-Out, Walk-Ins, Live Matrix)
│   └── housekeeping/     # Sanitation & Maintenance Tasks Console
├── customer/              # Customer Self-Service Portal (Search, Book, Invoices, Reviews, Profile)
├── assets/
│   ├── css/              # Theme Stylesheets & Custom UI Variables
│   ├── js/               # Main AJAX, Live Search & Chart.js Configs
│   ├── images/           # High-Res Logos & Hero Banners
│   └── uploads/          # Uploads for Rooms and User Profiles
├── config/                # Environment Constants & PDO DB Handler
├── database/              # MySQL Relational Schema Script (schema.sql)
├── functions/             # Helper Utilities, Authentication, Room & Booking Engines, Invoice Logic
├── includes/              # Reusable Header, Navbar, Footer, Sidebar Templates
├── login.php              # Unified Authentication Hub
├── register.php           # Customer Self-Registration
├── change-password.php    # Password Updater (Forces staff password change on first login)
├── index.php              # Public Luxury Hotel Landing Page
└── README.md
```

---

## 💳 Simulated Payment & Invoice System
Supports multi-method payment simulations:
- **UPI** (Google Pay / PhonePe / Paytm)
- **Credit Card / Debit Card**
- **Net Banking**
- **Cash at Front Desk**

Generates computer-formatted, GST-compliant Tax Invoices with itemized room charges, 18% GST calculation, promo coupon discounts, and instant print/PDF export.
