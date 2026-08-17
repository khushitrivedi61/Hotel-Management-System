<?php
$role = $_SESSION['user_role'] ?? 'guest';
$activePage = basename($_SERVER['PHP_SELF']);
?>
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-dark text-warning p-3 d-flex align-items-center gap-2 border-bottom border-warning">
        <i class="fas fa-user-shield fs-5"></i>
        <h6 class="mb-0 fw-bold text-uppercase tracking-wider"><?php echo ucfirst($role); ?> Navigation</h6>
    </div>
    <div class="list-group list-group-flush py-2">
        <?php if ($role === 'admin'): ?>
            <a href="<?php echo BASE_URL; ?>/admin/index.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'index.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-chart-line text-primary me-2 width-20"></i> Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/rooms.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'rooms.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-door-open text-primary me-2 width-20"></i> Rooms Management
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/room-types.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'room-types.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-layer-group text-primary me-2 width-20"></i> Room Categories
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/staff.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'staff.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-users-cog text-primary me-2 width-20"></i> Staff & RBAC
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/customers.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'customers.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-user-friends text-primary me-2 width-20"></i> Customers
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/bookings.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'bookings.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-calendar-check text-primary me-2 width-20"></i> Booking Control
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/housekeeping.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'housekeeping.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-broom text-primary me-2 width-20"></i> Housekeeping Tasks
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/payments.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'payments.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-credit-card text-primary me-2 width-20"></i> Payments & Ledger
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'reports.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-file-invoice-dollar text-primary me-2 width-20"></i> Financial Reports
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/coupons.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'coupons.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-ticket-alt text-primary me-2 width-20"></i> Coupon Offers
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/extra-services.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'extra-services.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-concierge-bell text-primary me-2 width-20"></i> Extra Services
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/reviews.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'reviews.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-star text-primary me-2 width-20"></i> Guest Reviews
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/messages.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'messages.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-envelope-open-text text-primary me-2 width-20"></i> Contact Inquiries
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/activity-logs.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'activity-logs.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-history text-primary me-2 width-20"></i> Audit Logs
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/settings.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'settings.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-cog text-primary me-2 width-20"></i> Hotel Settings
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/backup.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'backup.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-database text-primary me-2 width-20"></i> SQL DB Backup
            </a>

        <?php elseif ($role === 'receptionist'): ?>
            <a href="<?php echo BASE_URL; ?>/staff/reception/index.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'index.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-tachometer-alt text-primary me-2 width-20"></i> Reception Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/staff/reception/walkin.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'walkin.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-walking text-primary me-2 width-20"></i> Walk-in Reservation
            </a>
            <a href="<?php echo BASE_URL; ?>/staff/reception/checkin.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'checkin.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-key text-primary me-2 width-20"></i> Guest Check-In
            </a>
            <a href="<?php echo BASE_URL; ?>/staff/reception/checkout.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'checkout.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-sign-out-alt text-primary me-2 width-20"></i> Guest Check-Out & Billing
            </a>
            <a href="<?php echo BASE_URL; ?>/staff/reception/room-status.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'room-status.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-bed text-primary me-2 width-20"></i> Live Room Status Grid
            </a>

        <?php elseif ($role === 'housekeeping'): ?>
            <a href="<?php echo BASE_URL; ?>/staff/housekeeping/index.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'index.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-tachometer-alt text-primary me-2 width-20"></i> Housekeeping Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/staff/housekeeping/tasks.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'tasks.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-tasks text-primary me-2 width-20"></i> Cleaning Assignments
            </a>
            <a href="<?php echo BASE_URL; ?>/staff/housekeeping/maintenance.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'maintenance.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-tools text-primary me-2 width-20"></i> Maintenance Issues
            </a>

        <?php elseif ($role === 'customer'): ?>
            <a href="<?php echo BASE_URL; ?>/customer/index.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'index.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-tachometer-alt text-primary me-2 width-20"></i> Customer Overview
            </a>
            <a href="<?php echo BASE_URL; ?>/customer/search.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'search.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-search text-primary me-2 width-20"></i> Book a Room
            </a>
            <a href="<?php echo BASE_URL; ?>/customer/my-bookings.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'my-bookings.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-calendar-alt text-primary me-2 width-20"></i> My Reservations
            </a>
            <a href="<?php echo BASE_URL; ?>/customer/profile.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'profile.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
                <i class="fas fa-user-circle text-primary me-2 width-20"></i> Account Profile
            </a>
        <?php endif; ?>
        
        <a href="<?php echo BASE_URL; ?>/change-password.php" class="list-group-item list-group-item-action border-0 px-3 py-2 fw-medium <?php echo $activePage === 'change-password.php' ? 'active bg-warning text-dark fw-bold' : ''; ?>">
            <i class="fas fa-key text-secondary me-2 width-20"></i> Security & Password
        </a>
        <a href="<?php echo BASE_URL; ?>/logout.php" class="list-group-item list-group-item-action border-0 px-3 py-2 text-danger fw-semibold">
            <i class="fas fa-sign-out-alt me-2 width-20"></i> Sign Out
        </a>
    </div>
</div>
