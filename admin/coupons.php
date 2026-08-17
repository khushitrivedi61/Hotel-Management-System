<?php
$pageTitle = "Coupons & Offers - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code']));
    $discount = (float)$_POST['discount_percent'];
    $maxDiscount = (float)$_POST['max_discount'];
    $validFrom = $_POST['valid_from'];
    $validTo = $_POST['valid_to'];

    try {
        $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_percent, max_discount, valid_from, valid_to, status) VALUES (?, ?, ?, ?, ?, 'Active')");
        $stmt->execute([$code, $discount, $maxDiscount, $validFrom, $validTo]);
        setFlash('success', "Coupon offer {$code} created successfully.");
    } catch (PDOException $e) {
        setFlash('danger', "Failed to create coupon: " . $e->getMessage());
    }
    redirect('admin/coupons.php');
}

$coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
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
                    <h5 class="fw-bold mb-0"><i class="fas fa-ticket-alt me-2"></i>Promotional Offer Coupons</h5>
                    <button class="btn btn-warning btn-sm fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addCouponModal">
                        <i class="fas fa-plus me-1"></i> Create Coupon
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Coupon Code</th>
                                <th>Discount %</th>
                                <th>Max Cap (₹)</th>
                                <th>Valid From</th>
                                <th>Valid To</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $c): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><code><?php echo sanitize($c['code']); ?></code></td>
                                    <td class="fw-bold text-success"><?php echo $c['discount_percent']; ?>% OFF</td>
                                    <td><?php echo formatCurrency($c['max_discount']); ?></td>
                                    <td><?php echo formatDate($c['valid_from']); ?></td>
                                    <td><?php echo formatDate($c['valid_to']); ?></td>
                                    <td><span class="badge bg-success"><?php echo sanitize($c['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-warning">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Create New Coupon</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="coupons.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Coupon Code *</label>
                        <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. FESTIVE20" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Discount (%)</label>
                            <input type="number" step="0.01" name="discount_percent" class="form-control" placeholder="15" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Max Discount Cap (₹)</label>
                            <input type="number" step="0.01" name="max_discount" class="form-control" placeholder="1500">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Valid From</label>
                            <input type="date" name="valid_from" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Valid To</label>
                            <input type="date" name="valid_to" class="form-control" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Save Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
