<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../functions/helper.php';
require_once __DIR__ . '/../../functions/auth.php';
require_once __DIR__ . '/../../functions/booking_functions.php';

requireRole(['receptionist', 'admin']);

// Process Check-In Action BEFORE rendering HTML headers
if (isset($_GET['do_checkin'])) {
    $bookingId = (int)$_GET['do_checkin'];
    $res = updateBookingStatus($bookingId, 'Checked-In', $_SESSION['user_id']);
    if ($res['success']) {
        setFlash('success', 'Guest checked-in successfully! Room status updated to Occupied.');
    } else {
        setFlash('danger', $res['message']);
    }
    redirect('staff/reception/checkin.php');
}

$pageTitle = "Process Check-In - Receptionist Desk";
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$approvedBookings = $pdo->query("
    SELECT b.*, u.name as customer_name, u.phone, r.room_number, rt.type_name as room_type 
    FROM bookings b 
    JOIN customers c ON b.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    JOIN room_types rt ON r.room_type_id = rt.id 
    WHERE b.status = 'Approved' 
    ORDER BY b.check_in_date ASC
")->fetchAll();
?>

<div class="container-fluid px-lg-5 py-4">
    <?php displayFlash(); ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark text-warning p-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-key me-2"></i>Approved Reservations Ready for Check-In</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Booking Code</th>
                                <th>Guest Name</th>
                                <th>Assigned Room</th>
                                <th>Check-In Date</th>
                                <th>Grand Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($approvedBookings)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No approved reservations pending check-in.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($approvedBookings as $b): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo sanitize($b['booking_code']); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo sanitize($b['customer_name']); ?></div>
                                            <small class="text-muted"><?php echo sanitize($b['phone']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark text-warning">Room #<?php echo sanitize($b['room_number']); ?></span>
                                            <div class="small text-muted"><?php echo sanitize($b['room_type']); ?></div>
                                        </td>
                                        <td><?php echo formatDate($b['check_in_date']); ?></td>
                                        <td class="fw-bold text-success"><?php echo formatCurrency($b['grand_total']); ?></td>
                                        <td>
                                            <a href="checkin.php?do_checkin=<?php echo $b['id']; ?>" class="btn btn-sm btn-primary fw-bold">
                                                <i class="fas fa-key me-1"></i> Perform Check-In
                                            </a>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
