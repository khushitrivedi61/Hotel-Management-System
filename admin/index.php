<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/helper.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/room_functions.php';

requireRole('admin');

$pageTitle = "Executive Admin Dashboard - Grand Royale Hotel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch Dashboard Real-time Metrics
$roomCounts = getRoomStatusCounts();

// User Count
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

// Staff Count
$staffCount = $pdo->query("SELECT COUNT(*) FROM staff WHERE status = 'Active'")->fetchColumn();

// Today Check-ins & Check-outs (Cross-database MySQL/SQLite compatible date strings)
$todayStr = date('Y-m-d');
$monthStr = date('Y-m-');

$stmtIn = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE check_in_date = ? AND status IN ('Approved', 'Checked-In')");
$stmtIn->execute([$todayStr]);
$todayCheckIns = $stmtIn->fetchColumn();

$stmtOut = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE check_out_date = ? AND status = 'Checked-In'");
$stmtOut->execute([$todayStr]);
$todayCheckOuts = $stmtOut->fetchColumn();

// Financial Stats
$stmtRev = $pdo->prepare("SELECT SUM(amount) FROM payments WHERE paid_at LIKE ? AND payment_status = 'Paid'");
$stmtRev->execute([$monthStr . '%']);
$monthlyRevenue = $stmtRev->fetchColumn() ?: 0;

$pendingPayments = $pdo->query("SELECT SUM(grand_total) FROM bookings WHERE status = 'Pending'")->fetchColumn() ?: 0;

// Fetch Recent Activity Logs
$logStmt = $pdo->query("SELECT a.*, u.name as user_name FROM activity_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT 6");
$activityLogs = $logStmt->fetchAll();
?>

<div class="container-fluid px-lg-5 py-4">
    <?php displayFlash(); ?>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <!-- Main Dashboard Content -->
        <div class="col-lg-9">
            <!-- Welcome Header Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-light mb-4 position-relative overflow-hidden border-start border-warning border-5">
                <div class="d-flex justify-content-between align-items-center position-relative z-1">
                    <div>
                        <h4 class="fw-bold mb-1 brand-font text-amber">Welcome Back, <?php echo sanitize($_SESSION['user_name']); ?>!</h4>
                        <p class="text-secondary small mb-0">Live Resort Operational Status & Management Overview</p>
                    </div>
                    <span class="badge bg-warning text-dark fw-bold px-3 py-2 border border-light"><i class="fas fa-crown me-1"></i> Executive Portal</span>
                </div>
            </div>

            <!-- Top Real-Time Metrics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle p-3 fs-4"><i class="fas fa-bed"></i></div>
                            <div>
                                <h6 class="text-muted small fw-semibold mb-0">Total Rooms</h6>
                                <h3 class="fw-bold text-dark mb-0"><?php echo $roomCounts['Total']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success text-white rounded-circle p-3 fs-4"><i class="fas fa-check-circle"></i></div>
                            <div>
                                <h6 class="text-muted small fw-semibold mb-0">Available</h6>
                                <h3 class="fw-bold text-success mb-0"><?php echo $roomCounts['Available']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger text-white rounded-circle p-3 fs-4"><i class="fas fa-user-lock"></i></div>
                            <div>
                                <h6 class="text-muted small fw-semibold mb-0">Occupied</h6>
                                <h3 class="fw-bold text-danger mb-0"><?php echo $roomCounts['Occupied']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning text-dark rounded-circle p-3 fs-4"><i class="fas fa-broom"></i></div>
                            <div>
                                <h6 class="text-muted small fw-semibold mb-0">Cleaning</h6>
                                <h3 class="fw-bold text-warning mb-0"><?php echo $roomCounts['Cleaning']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial & Operational Stats Banner -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-chart-line text-warning me-2"></i>Financial Summary</h6>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                            <div>
                                <small class="text-muted d-block">This Month's Revenue</small>
                                <h3 class="fw-bold text-success mb-0"><?php echo formatCurrency($monthlyRevenue); ?></h3>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success px-3 py-2">Paid Invoices</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Pending Bookings Revenue</small>
                                <h4 class="fw-bold text-warning mb-0"><?php echo formatCurrency($pendingPayments); ?></h4>
                            </div>
                            <a href="bookings.php" class="btn btn-sm btn-outline-dark fw-bold">Review Requests</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-calendar-day text-warning me-2"></i>Today's Operations</h6>
                        <div class="row text-center g-3">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <i class="fas fa-sign-in-alt text-primary fs-3 mb-1"></i>
                                    <h4 class="fw-bold mb-0 text-dark"><?php echo $todayCheckIns; ?></h4>
                                    <small class="text-secondary">Expected Check-Ins</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <i class="fas fa-sign-out-alt text-info fs-3 mb-1"></i>
                                    <h4 class="fw-bold mb-0 text-dark"><?php echo $todayCheckOuts; ?></h4>
                                    <small class="text-secondary">Expected Check-Outs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Chart Visualizations -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-chart-bar text-warning me-2"></i>Monthly Revenue Trend</h6>
                            <span class="badge bg-light text-dark border">FY 2026</span>
                        </div>
                        <div style="height: 280px;">
                            <canvas id="adminRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-chart-pie text-warning me-2"></i>Room Status Breakdown</h6>
                        <div style="height: 230px;" class="d-flex align-items-center justify-content-center">
                            <canvas id="adminOccupancyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Audit Activity Logs -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-warning me-2"></i>Recent System Activity Logs</h6>
                    <a href="activity-logs.php" class="btn btn-sm btn-link text-warning text-decoration-none fw-bold">View All Logs</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Action</th>
                                <th>User</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activityLogs as $log): ?>
                                <tr>
                                    <td><span class="badge bg-dark text-warning"><?php echo sanitize($log['action']); ?></span></td>
                                    <td class="fw-semibold text-dark"><?php echo sanitize($log['user_name'] ?: 'System'); ?></td>
                                    <td class="small text-secondary"><?php echo sanitize($log['description']); ?></td>
                                    <td class="small font-monospace"><?php echo sanitize($log['ip_address']); ?></td>
                                    <td class="small text-muted"><?php echo formatDate($log['created_at'], 'd M, H:i'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
