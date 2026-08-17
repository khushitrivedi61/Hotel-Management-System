<?php
$pageTitle = "Financial Reports - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

$range = $_GET['range'] ?? 'monthly';

if ($range === 'daily') {
    $where = "DATE(paid_at) = CURDATE()";
} elseif ($range === 'weekly') {
    $where = "YEARWEEK(paid_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($range === 'yearly') {
    $where = "YEAR(paid_at) = YEAR(CURDATE())";
} else {
    $where = "MONTH(paid_at) = MONTH(CURDATE()) AND YEAR(paid_at) = YEAR(CURDATE())";
}

$reportPayments = $pdo->query("
    SELECT p.*, b.booking_code, u.name as customer_name, rt.type_name as room_type 
    FROM payments p 
    JOIN bookings b ON p.booking_id = b.id 
    JOIN room_types rt ON (SELECT room_type_id FROM rooms WHERE id = b.room_id) = rt.id
    JOIN customers c ON p.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    WHERE {$where} AND p.payment_status = 'Paid'
    ORDER BY p.id DESC
")->fetchAll();

$totalRevenue = 0;
foreach ($reportPayments as $rp) {
    $totalRevenue += (float)$rp['amount'];
}
?>

<div class="container-fluid px-lg-5 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 btn-print-hide">
        <h4 class="fw-bold text-dark mb-0"><i class="fas fa-file-invoice-dollar text-warning me-2"></i>Executive Financial Analytics & Audit Report</h4>
        <button onclick="window.print()" class="btn btn-warning fw-bold text-dark"><i class="fas fa-print me-1"></i> Print / Save Report PDF</button>
    </div>

    <div class="row g-4">
        <div class="col-lg-3 btn-print-hide">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 btn-print-hide">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Select Time Horizon Range</h6>
                    <div class="btn-group">
                        <a href="reports.php?range=daily" class="btn btn-sm btn-outline-dark <?php echo $range==='daily'?'active':''; ?>">Daily</a>
                        <a href="reports.php?range=weekly" class="btn btn-sm btn-outline-dark <?php echo $range==='weekly'?'active':''; ?>">Weekly</a>
                        <a href="reports.php?range=monthly" class="btn btn-sm btn-outline-dark <?php echo $range==='monthly'?'active':''; ?>">Monthly</a>
                        <a href="reports.php?range=yearly" class="btn btn-sm btn-outline-dark <?php echo $range==='yearly'?'active':''; ?>">Yearly</a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex justify-content-between align-items-start pb-4 border-bottom mb-4">
                    <div>
                        <h3 class="fw-bold brand-font text-dark mb-1">Grand Royale Hotel & Resort</h3>
                        <div class="text-secondary small">Financial Audit Statement - <?php echo ucfirst($range); ?> Horizon</div>
                    </div>
                    <div class="text-end">
                        <h2 class="fw-bold text-success mb-0"><?php echo formatCurrency($totalRevenue); ?></h2>
                        <small class="text-muted">Gross Realized Revenue</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Transaction Code</th>
                                <th>Booking Code</th>
                                <th>Guest Name</th>
                                <th>Room Category</th>
                                <th>Method</th>
                                <th>Realized Date</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reportPayments)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No payment transactions recorded for this time range.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportPayments as $rp): ?>
                                    <tr>
                                        <td class="fw-bold"><code><?php echo sanitize($rp['payment_code']); ?></code></td>
                                        <td><?php echo sanitize($rp['booking_code']); ?></td>
                                        <td><?php echo sanitize($rp['customer_name']); ?></td>
                                        <td><?php echo sanitize($rp['room_type']); ?></td>
                                        <td><?php echo sanitize($rp['payment_method']); ?></td>
                                        <td class="small"><?php echo formatDate($rp['paid_at'], 'd M Y'); ?></td>
                                        <td class="text-end fw-bold text-success"><?php echo formatCurrency($rp['amount']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-dark text-warning fs-5">
                                <td colspan="6" class="text-end fw-bold">Total Gross Revenue</td>
                                <td class="text-end fw-bold"><?php echo formatCurrency($totalRevenue); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
