<?php
$pageTitle = "Room Categories - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../functions/room_functions.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $typeName = trim($_POST['type_name']);
    $basePrice = (float)$_POST['base_price'];
    $capacity = (int)$_POST['capacity'];
    $acStatus = $_POST['ac_status'];
    $desc = trim($_POST['description']);

    try {
        $stmt = $pdo->prepare("INSERT INTO room_types (type_name, base_price, capacity, ac_status, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$typeName, $basePrice, $capacity, $acStatus, $desc]);
        setFlash('success', "Room type {$typeName} added successfully.");
    } catch (PDOException $e) {
        setFlash('danger', "Failed to add room type: " . $e->getMessage());
    }
    redirect('admin/room-types.php');
}

$roomTypes = getAllRoomTypes();
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
                    <h5 class="fw-bold mb-0"><i class="fas fa-layer-group me-2"></i>Room Categories & Base Tariffs</h5>
                    <button class="btn btn-warning btn-sm fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus me-1"></i> Add Category
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Category Name</th>
                                <th>Base Price / Night</th>
                                <th>Max Capacity</th>
                                <th>AC Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roomTypes as $rt): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo sanitize($rt['type_name']); ?></td>
                                    <td class="fw-bold text-success"><?php echo formatCurrency($rt['base_price']); ?></td>
                                    <td><i class="fas fa-user-friends text-warning me-1"></i> <?php echo $rt['capacity']; ?> Guests</td>
                                    <td><span class="badge bg-dark text-warning"><?php echo sanitize($rt['ac_status']); ?></span></td>
                                    <td class="small text-secondary"><?php echo sanitize($rt['description']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-warning">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Add New Room Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="room-types.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name *</label>
                        <input type="text" name="type_name" class="form-control" placeholder="e.g. Royal Ocean Suite" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Base Price per Night *</label>
                            <input type="number" step="0.01" name="base_price" class="form-control" placeholder="6000.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Capacity (Guests)</label>
                            <input type="number" name="capacity" class="form-control" value="2" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">AC Type</label>
                        <select name="ac_status" class="form-select">
                            <option value="AC">Air Conditioned (AC)</option>
                            <option value="Non AC">Non AC</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Category highlights"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
