<?php
$pageTitle = "Admin Dashboard - Grand Royale Hotel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../functions/room_functions.php';

requireRole('admin');

// Fetch Live Dashboard Statistics from MySQL
$rCounts = getRoomStatusCounts();

// Customers Count
$custCount = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();

// Staff Count
$staffCount = $pdo->query("SELECT COUNT(*) FROM staff WHERE status = 'Active'")->fetchColumn();

// Today Check-ins & Check-outs
$todayCheckIns = $pdo->query("SELECT COUNT(*) FROM bookings WHERE check_in_date = CURDATE() AND status IN ('Approved', 'Checked-In')")->fetchColumn();
$todayCheckOuts = $pdo->query("SELECT COUNT(*) FROM bookings WHERE check_out_date = CURDATE() AND status = 'Checked-In'")->fetchColumn();

// Financial Stats
$monthlyRevenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE MONTH(paid_at) = MONTH(CURDATE()) AND YEAR(paid_at) = YEAR(CURDATE()) AND payment_status = 'Paid'")->fetchColumn() ?: 0;
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

        <!-- Main Content Area -->
        <div class="col-lg-9">
            <!-- Header Welcome Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-light mb-4 border-start border-warning border-5">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1 text-amber">Executive Admin Control Console</h4>
                        <p class="text-secondary small mb-0">Real-time performance analytics, room availability matrix, and financial tracking</p>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2 fw-bold"><i class="fas fa-signal me-1"></i> Live System Online</span>
                </div>
            </div>

            <!-- 11 Key Live Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-semibold">Total Rooms</small>
                                <h3 class="fw-bold text-dark mb-0"><?php echo $rCounts['Total']; ?></h3>
                            </div>
                            <div class="p-3 bg-light text-primary rounded-circle"><i class="fas fa-door-open fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card border-start border-success border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-semibold">Available Rooms</small>
                                <h3 class="fw-bold text-success mb-0"><?php echo $rCounts['Available']; ?></h3>
                            </div>
                            <div class="p-3 bg-light text-success rounded-circle"><i class="fas fa-check-circle fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card border-start border-danger border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-semibold">Occupied Rooms</small>
                                <h3 class="fw-bold text-danger mb-0"><?php echo $rCounts['Occupied']; ?></h3>
                            </div>
                            <div class="p-3 bg-light text-danger rounded-circle"><i class="fas fa-user-lock fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card border-start border-warning border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-semibold">Reserved Rooms</small>
                                <h3 class="fw-bold text-warning mb-0"><?php echo $rCounts['Reserved']; ?></h3>
                            </div>
                            <div class="p-3 bg-light text-warning rounded-circle"><i class="fas fa-clock fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-semibold">Under Maintenance</small>
                                <h3 class="fw-bold text-secondary mb-0"><?php echo $rCounts['Maintenance']; ?></h3>
                            </div>
                            <div class="p-3 bg-light text-secondary rounded-circle"><i class="fas fa-tools fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-semibold">Today Check-Ins</small>
                                <h3 class="fw-bold text-info mb-0"><?php echo $todayCheckIns; ?></h3>
                            </div>
                            <div class="p-3 bg-light text-info rounded-circle"><i class="fas fa-key fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-semibold">Today Check-Outs</small>
                                <h3 class="fw-bold text-primary mb-0"><?php echo $todayCheckOuts; ?></h3>
                            </div>
                            <div class="p-3 bg-light text-primary rounded-circle"><i class="fas fa-sign-out-alt fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-4 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-semibold">Total Customers</small>
                                <h3 class="fw-bold text-dark mb-0"><?php echo $custCount; ?></h3>
                            </div>
                            <div class="p-3 bg-light text-dark rounded-circle"><i class="fas fa-users fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card border-start border-success border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-semibold">Monthly Revenue</small>
                                <h3 class="fw-bold text-success mb-0"><?php echo formatCurrency($monthlyRevenue); ?></h3>
                            </div>
                            <div class="p-3 bg-light text-success rounded-circle"><i class="fas fa-wallet fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card border-start border-warning border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-semibold">Pending Receivables</small>
                                <h3 class="fw-bold text-warning mb-0"><?php echo formatCurrency($pendingPayments); ?></h3>
                            </div>
                            <div class="p-3 bg-light text-warning rounded-circle"><i class="fas fa-hourglass-half fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-12 col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-secondary fw-semibold">Active Staff Members</small>
                                <h3 class="fw-bold text-dark mb-0"><?php echo $staffCount; ?> Active</h3>
                            </div>
                            <div class="p-3 bg-light text-dark rounded-circle"><i class="fas fa-user-shield fs-4"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4 Dynamic Chart visual Canvases -->
            <div class="row g-4 mb-4">
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h6 class="fw-bold mb-3"><i class="fas fa-chart-line text-warning me-2"></i>Monthly Revenue Velocity (₹)</h6>
                        <canvas id="monthlyRevenueChart" height="200"></canvas>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h6 class="fw-bold mb-3"><i class="fas fa-chart-pie text-warning me-2"></i>Live Occupancy Rate Breakdown</h6>
                        <canvas id="occupancyDoughnutChart" height="200"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h6 class="fw-bold mb-3"><i class="fas fa-chart-bar text-warning me-2"></i>Monthly Reservations Count</h6>
                        <canvas id="bookingsBarChart" height="200"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h6 class="fw-bold mb-3"><i class="fas fa-compass text-warning me-2"></i>Room Category Popularity Share</h6>
                        <canvas id="roomTypePolarChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent System Audit Logs -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="fw-bold mb-0"><i class="fas fa-history text-warning me-2"></i>Recent System Activity Audit Logs</h6>
                    <a href="activity-logs.php" class="small text-warning fw-semibold text-decoration-none">View All Logs</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activityLogs as $log): ?>
                                <tr>
                                    <td class="small text-muted"><?php echo formatDate($log['created_at'], 'd M Y H:i'); ?></td>
                                    <td class="fw-semibold"><?php echo sanitize($log['user_name'] ?: 'System'); ?></td>
                                    <td><span class="badge bg-dark text-warning"><?php echo sanitize($log['action']); ?></span></td>
                                    <td class="small"><?php echo sanitize($log['description']); ?></td>
                                    <td><code><?php echo sanitize($log['ip_address']); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/assets/js/chart-config.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    renderAdminCharts(
        { labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'], values: [35000, 48000, 62000, 79000, 92000, 110000, <?php echo (float)$monthlyRevenue; ?>] },
        { labels: ['Available', 'Occupied', 'Reserved', 'Cleaning', 'Maintenance'], values: [<?php echo $rCounts['Available']; ?>, <?php echo $rCounts['Occupied']; ?>, <?php echo $rCounts['Reserved']; ?>, <?php echo $rCounts['Cleaning']; ?>, <?php echo $rCounts['Maintenance']; ?>] },
        { labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'], values: [10, 15, 22, 28, 34, 40, 48] },
        { labels: ['Executive Suite', 'Presidential Villa', 'Deluxe Double', 'Standard Classic'], values: [40, 20, 25, 15] }
    );
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
