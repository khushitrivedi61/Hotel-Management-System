<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/helper.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/room_functions.php';

requireRole('admin');

// Action: Handle Room Add/Edit/Delete BEFORE rendering HTML headers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $roomNumber = trim($_POST['room_number']);
        $roomTypeId = (int)$_POST['room_type_id'];
        $floor = (int)$_POST['floor'];
        $price = (float)$_POST['price_per_night'];
        $status = $_POST['status'];
        $desc = trim($_POST['description']);

        try {
            $stmt = $pdo->prepare("INSERT INTO rooms (room_number, room_type_id, floor, price_per_night, status, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$roomNumber, $roomTypeId, $floor, $price, $status, $desc]);
            logActivity('Room Added', "Added new Room #{$roomNumber}");
            setFlash('success', "Room #{$roomNumber} added successfully.");
        } catch (PDOException $e) {
            setFlash('danger', "Failed to add room: " . $e->getMessage());
        }
        redirect('admin/rooms.php');
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $roomNumber = trim($_POST['room_number']);
        $roomTypeId = (int)$_POST['room_type_id'];
        $floor = (int)$_POST['floor'];
        $price = (float)$_POST['price_per_night'];
        $status = $_POST['status'];
        $desc = trim($_POST['description']);

        try {
            $stmt = $pdo->prepare("UPDATE rooms SET room_number = ?, room_type_id = ?, floor = ?, price_per_night = ?, status = ?, description = ? WHERE id = ?");
            $stmt->execute([$roomNumber, $roomTypeId, $floor, $price, $status, $desc, $id]);
            logActivity('Room Updated', "Updated Room #{$roomNumber}");
            setFlash('success', "Room #{$roomNumber} updated successfully.");
        } catch (PDOException $e) {
            setFlash('danger', "Failed to update room: " . $e->getMessage());
        }
        redirect('admin/rooms.php');
    }
}

if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$delId]);
        logActivity('Room Deleted', "Deleted Room ID {$delId}");
        setFlash('success', "Room deleted successfully.");
    } catch (PDOException $e) {
        setFlash('danger', "Cannot delete room: " . $e->getMessage());
    }
    redirect('admin/rooms.php');
}

$pageTitle = "Room Management - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch Rooms List
$roomsStmt = $pdo->query("
    SELECT r.*, rt.type_name 
    FROM rooms r 
    JOIN room_types rt ON r.room_type_id = rt.id 
    ORDER BY r.floor ASC, r.room_number ASC
");
$rooms = $roomsStmt->fetchAll();
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
                    <h5 class="fw-bold mb-0"><i class="fas fa-door-open me-2"></i>Rooms Inventory Management</h5>
                    <button class="btn btn-warning btn-sm fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="fas fa-plus me-1"></i> Add New Room
                    </button>
                </div>

                <div class="p-3 bg-light border-bottom">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm table-search-input" data-target="roomsTable" placeholder="Live search rooms...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="roomsTable">
                        <thead class="table-light small">
                            <tr>
                                <th>Room #</th>
                                <th>Category</th>
                                <th>Floor</th>
                                <th>Price / Night</th>
                                <th>Current Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $r): ?>
                                <tr>
                                    <td class="fw-bold fs-6">#<?php echo sanitize($r['room_number']); ?></td>
                                    <td><?php echo sanitize($r['type_name']); ?></td>
                                    <td>Floor <?php echo $r['floor']; ?></td>
                                    <td class="fw-bold text-success"><?php echo formatCurrency($r['price_per_night']); ?></td>
                                    <td>
                                        <span class="badge badge-status-<?php echo strtolower($r['status']); ?>">
                                            <?php echo sanitize($r['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editRoomModal_<?php echo $r['id']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="rooms.php?delete_id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm="Are you sure you want to delete Room #<?php echo $r['room_number']; ?>?">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>

                                <!-- Edit Room Modal -->
                                <div class="modal fade" id="editRoomModal_<?php echo $r['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-dark text-warning">
                                                <h5 class="modal-title fw-bold">Edit Room #<?php echo sanitize($r['room_number']); ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="rooms.php" method="POST">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Room Number</label>
                                                        <input type="text" name="room_number" class="form-control" value="<?php echo sanitize($r['room_number']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Category</label>
                                                        <select name="room_type_id" class="form-select">
                                                            <?php foreach ($roomTypes as $rt): ?>
                                                                <option value="<?php echo $rt['id']; ?>" <?php echo $r['room_type_id'] == $rt['id'] ? 'selected' : ''; ?>><?php echo sanitize($rt['type_name']); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">Floor</label>
                                                            <input type="number" name="floor" class="form-control" value="<?php echo $r['floor']; ?>" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">Price per Night</label>
                                                            <input type="number" step="0.01" name="price_per_night" class="form-control" value="<?php echo $r['price_per_night']; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="Available" <?php echo $r['status']=='Available'?'selected':''; ?>>Available</option>
                                                            <option value="Reserved" <?php echo $r['status']=='Reserved'?'selected':''; ?>>Reserved</option>
                                                            <option value="Occupied" <?php echo $r['status']=='Occupied'?'selected':''; ?>>Occupied</option>
                                                            <option value="Cleaning" <?php echo $r['status']=='Cleaning'?'selected':''; ?>>Cleaning</option>
                                                            <option value="Maintenance" <?php echo $r['status']=='Maintenance'?'selected':''; ?>>Maintenance</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Description</label>
                                                        <textarea name="description" class="form-control" rows="2"><?php echo sanitize($r['description']); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-warning fw-bold text-dark">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-warning">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Add New Room</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="rooms.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Room Number</label>
                        <input type="text" name="room_number" class="form-control" placeholder="e.g. 104" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="room_type_id" class="form-select" required>
                            <?php foreach ($roomTypes as $rt): ?>
                                <option value="<?php echo $rt['id']; ?>"><?php echo sanitize($rt['type_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Floor</label>
                            <input type="number" name="floor" class="form-control" value="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Price per Night</label>
                            <input type="number" step="0.01" name="price_per_night" class="form-control" placeholder="3500.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Initial Status</label>
                        <select name="status" class="form-select">
                            <option value="Available">Available</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Room details & balcony view"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Add Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
