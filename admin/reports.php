<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/helper.php';
require_once __DIR__ . '/../functions/auth.php';

requireRole('admin');

$range = $_GET['range'] ?? 'monthly';

// Filter Date Calculations (Cross-database MySQL/SQLite compatible date strings)
$todayStr = date('Y-m-d');
$monthStr = date('Y-m-');
$yearStr = date('Y-');

if ($range === 'daily') {
    $where = "p.paid_at LIKE " . $pdo->quote($todayStr . '%');
} elseif ($range === 'weekly') {
    $weekStart = date('Y-m-d', strtotime('-7 days'));
    $where = "p.paid_at >= " . $pdo->quote($weekStart);
} elseif ($range === 'yearly') {
    $where = "p.paid_at LIKE " . $pdo->quote($yearStr . '%');
} else {
    $where = "p.paid_at LIKE " . $pdo->quote($monthStr . '%');
}

$reportPayments = $pdo->query("
    SELECT p.*, b.booking_code, u.name as customer_name
    FROM payments p 
    JOIN bookings b ON p.booking_id = b.id 
    JOIN customers c ON p.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    WHERE {$where} AND p.payment_status = 'Paid'
    ORDER BY p.id DESC
")->fetchAll();

$totalRevenue = 0;
foreach ($reportPayments as $rp) {
    $totalRevenue += (float)$rp['amount'];
}

$pageTitle = "Financial Revenue Reports - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-5 py-4">
    <?php displayFlash(); ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark text-warning p-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Financial & Revenue Reports</h5>
                    <button class="btn btn-warning btn-sm fw-bold text-dark" onclick="window.print();">
                        <i class="fas fa-print me-1"></i> Print Report
                    </button>
                </div>

                <div class="p-3 bg-light border-bottom">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="btn-group" role="group">
                            <a href="reports.php?range=daily" class="btn btn-sm btn-<?php echo $range==='daily'?'warning':'outline-dark'; ?> fw-bold">Daily</a>
                            <a href="reports.php?range=weekly" class="btn btn-sm btn-<?php echo $range==='weekly'?'warning':'outline-dark'; ?> fw-bold">Weekly</a>
                            <a href="reports.php?range=monthly" class="btn btn-sm btn-<?php echo $range==='monthly'?'warning':'outline-dark'; ?> fw-bold">Monthly</a>
                            <a href="reports.php?range=yearly" class="btn btn-sm btn-<?php echo $range==='yearly'?'warning':'outline-dark'; ?> fw-bold">Yearly</a>
                        </div>
                        <div class="fw-bold text-success fs-5">
                            Total Period Revenue: <?php echo formatCurrency($totalRevenue); ?>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Payment Ref</th>
                                <th>Booking Code</th>
                                <th>Guest Name</th>
                                <th>Payment Method</th>
                                <th>Paid Date</th>
                                <th>Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reportPayments)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No completed revenue transactions found for this period.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportPayments as $p): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo sanitize($p['payment_code']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo sanitize($p['booking_code']); ?></span></td>
                                        <td class="fw-semibold"><?php echo sanitize($p['customer_name']); ?></td>
                                        <td><span class="badge bg-dark text-warning"><?php echo sanitize($p['payment_method']); ?></span></td>
                                        <td class="small"><?php echo formatDate($p['paid_at'], 'd M Y, H:i'); ?></td>
                                        <td class="fw-bold text-success"><?php echo formatCurrency($p['amount']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
