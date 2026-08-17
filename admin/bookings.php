<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/helper.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/booking_functions.php';

requireRole(['admin', 'receptionist']);

// Process Action Status BEFORE rendering HTML headers
if (isset($_GET['status']) && isset($_GET['booking_id'])) {
    $bookingId = (int)$_GET['booking_id'];
    $newStatus = $_GET['status'];
    $res = updateBookingStatus($bookingId, $newStatus, $_SESSION['user_id']);
    if ($res['success']) {
        setFlash('success', $res['message']);
    } else {
        setFlash('danger', $res['message']);
    }
    redirect('admin/bookings.php');
}

$pageTitle = "Booking Management - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch All Bookings
$stmt = $pdo->query("
    SELECT b.*, u.name as customer_name, u.phone as customer_phone, r.room_number, rt.type_name as room_type 
    FROM bookings b 
    JOIN customers c ON b.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    JOIN room_types rt ON r.room_type_id = rt.id 
    ORDER BY b.id DESC
");
$bookings = $stmt->fetchAll();
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
                    <h5 class="fw-bold mb-0"><i class="fas fa-calendar-check me-2"></i>Global Reservation Management</h5>
                </div>

                <div class="p-3 bg-light border-bottom">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm table-search-input" data-target="bookingsTable" placeholder="Live search bookings...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="bookingsTable">
                        <thead class="table-light small">
                            <tr>
                                <th>Code</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check-In / Out</th>
                                <th>Grand Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo sanitize($b['booking_code']); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?php echo sanitize($b['customer_name']); ?></div>
                                        <small class="text-muted"><?php echo sanitize($b['customer_phone']); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">Room #<?php echo sanitize($b['room_number']); ?></div>
                                        <small class="text-muted"><?php echo sanitize($b['room_type']); ?></small>
                                    </td>
                                    <td class="small">
                                        <div><strong>In:</strong> <?php echo formatDate($b['check_in_date']); ?></div>
                                        <div><strong>Out:</strong> <?php echo formatDate($b['check_out_date']); ?></div>
                                    </td>
                                    <td class="fw-bold text-success"><?php echo formatCurrency($b['grand_total']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($b['status']=='Approved'?'primary':($b['status']=='Checked-In'?'success':($b['status']=='Pending'?'warning':($b['status']=='Cancelled'?'danger':'secondary')))); ?>">
                                            <?php echo sanitize($b['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Update Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                <?php if ($b['status'] === 'Pending'): ?>
                                                    <li><a class="dropdown-item text-success fw-bold" href="bookings.php?booking_id=<?php echo $b['id']; ?>&status=Approved"><i class="fas fa-check me-2"></i> Approve</a></li>
                                                    <li><a class="dropdown-item text-danger fw-bold" href="bookings.php?booking_id=<?php echo $b['id']; ?>&status=Rejected"><i class="fas fa-times me-2"></i> Reject</a></li>
                                                <?php endif; ?>
                                                <?php if ($b['status'] === 'Approved'): ?>
                                                    <li><a class="dropdown-item text-primary fw-bold" href="bookings.php?booking_id=<?php echo $b['id']; ?>&status=Checked-In"><i class="fas fa-key me-2"></i> Perform Check-In</a></li>
                                                <?php endif; ?>
                                                <?php if ($b['status'] === 'Checked-In'): ?>
                                                    <li><a class="dropdown-item text-info fw-bold" href="bookings.php?booking_id=<?php echo $b['id']; ?>&status=Checked-Out"><i class="fas fa-sign-out-alt me-2"></i> Perform Check-Out</a></li>
                                                <?php endif; ?>
                                                <?php if ($b['status'] !== 'Cancelled' && $b['status'] !== 'Completed'): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="bookings.php?booking_id=<?php echo $b['id']; ?>&status=Cancelled" data-confirm="Cancel this booking?"><i class="fas fa-ban me-2"></i> Cancel Booking</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
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
