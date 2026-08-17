<?php
$pageTitle = "Extra Services - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['service_name']);
    $price = (float)$_POST['price'];
    $desc = trim($_POST['description']);

    try {
        $stmt = $pdo->prepare("INSERT INTO extra_services (service_name, price, description, status) VALUES (?, ?, ?, 'Active')");
        $stmt->execute([$name, $price, $desc]);
        setFlash('success', "Service {$name} added successfully.");
    } catch (PDOException $e) {
        setFlash('danger', "Failed to add service: " . $e->getMessage());
    }
    redirect('admin/extra-services.php');
}

$services = $pdo->query("SELECT * FROM extra_services ORDER BY id DESC")->fetchAll();
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
                    <h5 class="fw-bold mb-0"><i class="fas fa-concierge-bell me-2"></i>Resort Extra Services & Addons</h5>
                    <button class="btn btn-warning btn-sm fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fas fa-plus me-1"></i> Add Service
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Service Name</th>
                                <th>Price (₹)</th>
                                <th>Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $s): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo sanitize($s['service_name']); ?></td>
                                    <td class="fw-bold text-success"><?php echo formatCurrency($s['price']); ?></td>
                                    <td class="small text-secondary"><?php echo sanitize($s['description']); ?></td>
                                    <td><span class="badge bg-success"><?php echo sanitize($s['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-warning">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Add Extra Resort Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="extra-services.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Service Name *</label>
                        <input type="text" name="service_name" class="form-control" placeholder="e.g. Airport Pickup" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Price (₹) *</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="1200.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Service description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Save Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
