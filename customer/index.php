<?php
$pageTitle = "Customer Dashboard - Grand Royale Hotel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../functions/booking_functions.php';

requireRole('customer');

$userId = $_SESSION['user_id'];

// Get Customer Record
$custStmt = $pdo->prepare("SELECT id FROM customers WHERE user_id = ?");
$custStmt->execute([$userId]);
$customerId = $custStmt->fetchColumn();

// Fetch Customer Stats
$bStatsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN status IN ('Approved', 'Checked-In', 'Pending') THEN 1 ELSE 0 END) as active_bookings,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_bookings,
        SUM(CASE WHEN status != 'Cancelled' THEN grand_total ELSE 0 END) as total_spent
    FROM bookings 
    WHERE customer_id = ?
");
$bStatsStmt->execute([$customerId]);
$stats = $bStatsStmt->fetch();

// Fetch Recent Bookings
$recentStmt = $pdo->prepare("
    SELECT b.*, r.room_number, rt.type_name as room_type 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    JOIN room_types rt ON r.room_type_id = rt.id 
    WHERE b.customer_id = ? 
    ORDER BY b.id DESC LIMIT 5
");
$recentStmt->execute([$customerId]);
$recentBookings = $recentStmt->fetchAll();

// Fetch Unread Notifications
$nStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 5");
$nStmt->execute([$userId]);
$notifications = $nStmt->fetchAll();
?>

<div class="container py-4">
    <?php displayFlash(); ?>
    
    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>
        
        <div class="col-lg-9">
            <!-- Welcome Header -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-light mb-4 border-start border-warning border-5">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1 text-amber">Welcome Back, <?php echo sanitize($_SESSION['user_name']); ?>!</h4>
                        <p class="text-secondary small mb-0">Manage your reservations, view invoices, and explore luxury suites</p>
                    </div>
                    <a href="search.php" class="btn btn-warning fw-bold text-dark"><i class="fas fa-plus me-1"></i> Book New Room</a>
                </div>
            </div>

            <!-- Stats Grid Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center stat-card">
                        <h3 class="fw-bold text-primary mb-0"><?php echo (int)$stats['total_bookings']; ?></h3>
                        <small class="text-secondary">Total Bookings</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center stat-card">
                        <h3 class="fw-bold text-warning mb-0"><?php echo (int)$stats['active_bookings']; ?></h3>
                        <small class="text-secondary">Active Stay</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center stat-card">
                        <h3 class="fw-bold text-success mb-0"><?php echo (int)$stats['completed_bookings']; ?></h3>
                        <small class="text-secondary">Completed</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center stat-card">
                        <h3 class="fw-bold text-dark mb-0"><?php echo formatCurrency($stats['total_spent'] ?: 0); ?></h3>
                        <small class="text-secondary">Total Invested</small>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fas fa-calendar-check text-warning me-2"></i>My Recent Reservations</h6>
                    <a href="my-bookings.php" class="small text-warning fw-semibold text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Booking Code</th>
                                <th>Room</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentBookings)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No reservations found. <a href="search.php">Book your first room now!</a></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentBookings as $b): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo sanitize($b['booking_code']); ?></td>
                                        <td>
                                            <div class="fw-semibold">Room #<?php echo sanitize($b['room_number']); ?></div>
                                            <small class="text-muted"><?php echo sanitize($b['room_type']); ?></small>
                                        </td>
                                        <td><?php echo formatDate($b['check_in_date']); ?></td>
                                        <td><?php echo formatDate($b['check_out_date']); ?></td>
                                        <td class="fw-bold text-success"><?php echo formatCurrency($b['grand_total']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($b['status']=='Approved'?'primary':($b['status']=='Checked-In'?'success':($b['status']=='Pending'?'warning':'secondary'))); ?>">
                                                <?php echo sanitize($b['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="my-bookings.php" class="btn btn-sm btn-outline-dark"><i class="fas fa-eye"></i> Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notifications Center -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h6 class="fw-bold mb-3"><i class="fas fa-bell text-warning me-2"></i>Recent Notifications</h6>
                <?php if (empty($notifications)): ?>
                    <p class="text-muted small mb-0">No new notifications.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $n): ?>
                            <div class="list-group-item px-0 py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-1 fw-bold text-dark fs-6"><?php echo sanitize($n['title']); ?></h6>
                                    <small class="text-muted"><?php echo formatDate($n['created_at'], 'd M H:i'); ?></small>
                                </div>
                                <p class="text-secondary small mb-0"><?php echo sanitize($n['message']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
