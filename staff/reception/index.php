<?php
$pageTitle = "Receptionist Desk - Grand Royale Hotel";
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../functions/room_functions.php';

requireRole(['receptionist', 'admin']);

$rCounts = getRoomStatusCounts();

// Today Check-Ins
$checkIns = $pdo->query("
    SELECT b.*, u.name as customer_name, r.room_number 
    FROM bookings b 
    JOIN customers c ON b.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.check_in_date = CURDATE() AND b.status IN ('Approved', 'Checked-In')
")->fetchAll();

// Today Check-Outs
$checkOuts = $pdo->query("
    SELECT b.*, u.name as customer_name, r.room_number 
    FROM bookings b 
    JOIN customers c ON b.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.check_out_date = CURDATE() AND b.status = 'Checked-In'
")->fetchAll();
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
                        <h4 class="fw-bold mb-1 text-amber">Reception Front Desk Operations</h4>
                        <p class="text-secondary small mb-0">Guest check-ins, check-outs, walk-in registration, and room key assignments</p>
                    </div>
                    <div>
                        <a href="walkin.php" class="btn btn-warning fw-bold text-dark me-2"><i class="fas fa-walking me-1"></i> Walk-In Guest</a>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card text-center">
                        <h3 class="fw-bold text-success mb-0"><?php echo $rCounts['Available']; ?></h3>
                        <small class="text-secondary">Available Rooms</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card text-center">
                        <h3 class="fw-bold text-danger mb-0"><?php echo $rCounts['Occupied']; ?></h3>
                        <small class="text-secondary">Occupied Rooms</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card text-center">
                        <h3 class="fw-bold text-info mb-0"><?php echo count($checkIns); ?></h3>
                        <small class="text-secondary">Today Check-Ins</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white stat-card text-center">
                        <h3 class="fw-bold text-warning mb-0"><?php echo count($checkOuts); ?></h3>
                        <small class="text-secondary">Today Check-Outs</small>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Check Ins -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-key me-2"></i>Today's Arrivals (Check-In)</h6>
                            <a href="checkin.php" class="btn btn-sm btn-outline-primary">Process Check-In</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($checkIns as $ci): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo sanitize($ci['customer_name']); ?></td>
                                            <td>Room #<?php echo sanitize($ci['room_number']); ?></td>
                                            <td><span class="badge bg-primary"><?php echo sanitize($ci['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Check Outs -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-danger"><i class="fas fa-sign-out-alt me-2"></i>Today's Departures (Check-Out)</h6>
                            <a href="checkout.php" class="btn btn-sm btn-outline-danger">Process Check-Out</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($checkOuts as $co): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo sanitize($co['customer_name']); ?></td>
                                            <td>Room #<?php echo sanitize($co['room_number']); ?></td>
                                            <td><span class="badge bg-success"><?php echo sanitize($co['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
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
