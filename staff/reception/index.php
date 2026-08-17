<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../functions/helper.php';
require_once __DIR__ . '/../../functions/auth.php';
require_once __DIR__ . '/../../functions/room_functions.php';

requireRole(['receptionist', 'admin']);

$roomCounts = getRoomStatusCounts();
$todayStr = date('Y-m-d');

// Today Check-Ins (Cross-database MySQL/SQLite compatible date strings)
$stmtIn = $pdo->prepare("
    SELECT b.*, u.name as customer_name, r.room_number 
    FROM bookings b 
    JOIN customers c ON b.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.check_in_date = ? AND b.status IN ('Approved', 'Checked-In')
");
$stmtIn->execute([$todayStr]);
$checkIns = $stmtIn->fetchAll();

// Today Check-Outs
$stmtOut = $pdo->prepare("
    SELECT b.*, u.name as customer_name, r.room_number 
    FROM bookings b 
    JOIN customers c ON b.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.check_out_date = ? AND b.status = 'Checked-In'
");
$stmtOut->execute([$todayStr]);
$checkOuts = $stmtOut->fetchAll();

$pageTitle = "Receptionist Desk - Grand Royale Hotel";
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<div class="container-fluid px-lg-5 py-4">
    <?php displayFlash(); ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-light mb-4 border-start border-warning border-5">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1 text-amber">Front Desk Receptionist Console</h4>
                        <p class="text-secondary small mb-0">Manage guest walk-ins, check-in arrivals, and check-out billing</p>
                    </div>
                    <a href="walkin.php" class="btn btn-warning fw-bold text-dark"><i class="fas fa-walking me-1"></i> New Walk-In Guest</a>
                </div>
            </div>

            <!-- Live Room Status Badges -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted fw-bold">Available Rooms</small>
                                <h3 class="fw-bold text-success mb-0"><?php echo $roomCounts['Available']; ?></h3>
                            </div>
                            <div class="bg-success text-white rounded-circle p-3 fs-5"><i class="fas fa-door-open"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted fw-bold">Occupied Rooms</small>
                                <h3 class="fw-bold text-danger mb-0"><?php echo $roomCounts['Occupied']; ?></h3>
                            </div>
                            <div class="bg-danger text-white rounded-circle p-3 fs-5"><i class="fas fa-user-check"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted fw-bold">Reserved</small>
                                <h3 class="fw-bold text-primary mb-0"><?php echo $roomCounts['Reserved']; ?></h3>
                            </div>
                            <div class="bg-primary text-white rounded-circle p-3 fs-5"><i class="fas fa-bookmark"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted fw-bold">Cleaning</small>
                                <h3 class="fw-bold text-warning mb-0"><?php echo $roomCounts['Cleaning']; ?></h3>
                            </div>
                            <div class="bg-warning text-dark rounded-circle p-3 fs-5"><i class="fas fa-broom"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operations Tables -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sign-in-alt text-primary me-2"></i>Today's Expected Check-Ins</h6>
                            <a href="checkin.php" class="btn btn-sm btn-outline-primary">Process All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Code</th>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($checkIns)): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-3">No pending check-ins today.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($checkIns as $ci): ?>
                                            <tr>
                                                <td class="fw-bold small"><?php echo sanitize($ci['booking_code']); ?></td>
                                                <td class="small"><?php echo sanitize($ci['customer_name']); ?></td>
                                                <td><span class="badge bg-dark text-warning">Room #<?php echo sanitize($ci['room_number']); ?></span></td>
                                                <td>
                                                    <a href="checkin.php?do_checkin=<?php echo $ci['id']; ?>" class="btn btn-sm btn-success py-0 px-2 small">Check-In</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sign-out-alt text-danger me-2"></i>Today's Expected Check-Outs</h6>
                            <a href="checkout.php" class="btn btn-sm btn-outline-danger">Process All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Code</th>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($checkOuts)): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-3">No pending check-outs today.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($checkOuts as $co): ?>
                                            <tr>
                                                <td class="fw-bold small"><?php echo sanitize($co['booking_code']); ?></td>
                                                <td class="small"><?php echo sanitize($co['customer_name']); ?></td>
                                                <td><span class="badge bg-danger">Room #<?php echo sanitize($co['room_number']); ?></span></td>
                                                <td>
                                                    <a href="checkout.php?do_checkout=<?php echo $co['id']; ?>" class="btn btn-sm btn-danger py-0 px-2 small">Check-Out</a>
                                                </td>
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
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
