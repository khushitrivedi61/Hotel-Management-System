<?php
$pageTitle = "Live Room Matrix - Receptionist Desk";
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../functions/room_functions.php';

requireRole(['receptionist', 'admin']);

$rooms = $pdo->query("
    SELECT r.*, rt.type_name 
    FROM rooms r 
    JOIN room_types rt ON r.room_type_id = rt.id 
    ORDER BY r.floor ASC, r.room_number ASC
")->fetchAll();
?>

<div class="container-fluid px-lg-5 py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark text-warning p-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-th me-2"></i>Live Room Status Grid Matrix</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success">Available</span>
                        <span class="badge bg-warning text-dark">Reserved</span>
                        <span class="badge bg-danger">Occupied</span>
                        <span class="badge bg-info text-dark">Cleaning</span>
                        <span class="badge bg-secondary">Maintenance</span>
                    </div>
                </div>

                <div class="card-body p-4 bg-white">
                    <div class="row g-3">
                        <?php foreach ($rooms as $rm): ?>
                            <div class="col-md-3 col-sm-6">
                                <div class="card border-0 shadow-sm p-3 rounded-4 text-center text-white bg-<?php echo ($rm['status']=='Available'?'success':($rm['status']=='Reserved'?'warning':($rm['status']=='Occupied'?'danger':($rm['status']=='Cleaning'?'info':'secondary')))); ?>">
                                    <h4 class="fw-bold mb-0">Room #<?php echo sanitize($rm['room_number']); ?></h4>
                                    <small class="opacity-90 d-block"><?php echo sanitize($rm['type_name']); ?></small>
                                    <div class="badge bg-dark mt-2 text-capitalize"><?php echo sanitize($rm['status']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
