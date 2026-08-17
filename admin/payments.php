<?php
$pageTitle = "Payments Ledger - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

$payments = $pdo->query("
    SELECT p.*, b.booking_code, u.name as customer_name 
    FROM payments p 
    JOIN bookings b ON p.booking_id = b.id 
    JOIN customers c ON p.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    ORDER BY p.id DESC
")->fetchAll();
?>

<div class="container-fluid px-lg-5 py-4">
    <?php displayFlash(); ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark text-warning p-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-credit-card me-2"></i>Global Payments & Transaction Ledger</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Payment Code</th>
                                <th>Booking Code</th>
                                <th>Guest Name</th>
                                <th>Amount Paid</th>
                                <th>Method</th>
                                <th>Transaction Ref</th>
                                <th>Date Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo sanitize($pay['payment_code']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo sanitize($pay['booking_code']); ?></span></td>
                                    <td class="fw-semibold"><?php echo sanitize($pay['customer_name']); ?></td>
                                    <td class="fw-bold text-success"><?php echo formatCurrency($pay['amount']); ?></td>
                                    <td><span class="badge bg-dark text-warning"><?php echo sanitize($pay['payment_method']); ?></span></td>
                                    <td><code><?php echo sanitize($pay['transaction_ref']); ?></code></td>
                                    <td class="small text-muted"><?php echo formatDate($pay['paid_at'], 'd M Y H:i'); ?></td>
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
