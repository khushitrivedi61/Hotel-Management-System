<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../functions/helper.php';
require_once __DIR__ . '/../../functions/auth.php';
require_once __DIR__ . '/../../functions/booking_functions.php';
require_once __DIR__ . '/../../functions/invoice_functions.php';

requireRole(['receptionist', 'admin']);

// Process Check-Out Action BEFORE rendering HTML headers
if (isset($_GET['do_checkout'])) {
    $bookingId = (int)$_GET['do_checkout'];
    
    // Process simulated payment if invoice doesn't exist yet
    $chkStmt = $pdo->prepare("SELECT id FROM invoices WHERE booking_id = ?");
    $chkStmt->execute([$bookingId]);
    if (!$chkStmt->fetchColumn()) {
        processPaymentAndInvoice($bookingId, 'Cash');
    }
    
    $res = updateBookingStatus($bookingId, 'Checked-Out', $_SESSION['user_id']);
    if ($res['success']) {
        setFlash('success', 'Guest checked-out successfully! Room set to Cleaning and Housekeeping notified.');
    } else {
        setFlash('danger', $res['message']);
    }
    redirect('staff/reception/checkout.php');
}

$pageTitle = "Process Check-Out & Billing - Receptionist Desk";
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$occupiedBookings = $pdo->query("
    SELECT b.*, u.name as customer_name, u.phone, r.room_number, rt.type_name as room_type 
    FROM bookings b 
    JOIN customers c ON b.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    JOIN room_types rt ON r.room_type_id = rt.id 
    WHERE b.status = 'Checked-In' 
    ORDER BY b.check_out_date ASC
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
                    <h5 class="fw-bold mb-0"><i class="fas fa-sign-out-alt me-2"></i>Occupied Rooms Ready for Check-Out & Billing</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Booking Code</th>
                                <th>Guest Name</th>
                                <th>Occupied Room</th>
                                <th>Scheduled Check-Out</th>
                                <th>Bill Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($occupiedBookings)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No currently occupied rooms pending check-out.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($occupiedBookings as $b): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo sanitize($b['booking_code']); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo sanitize($b['customer_name']); ?></div>
                                            <small class="text-muted"><?php echo sanitize($b['phone']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">Room #<?php echo sanitize($b['room_number']); ?></span>
                                            <div class="small text-muted"><?php echo sanitize($b['room_type']); ?></div>
                                        </td>
                                        <td><?php echo formatDate($b['check_out_date']); ?></td>
                                        <td class="fw-bold text-success"><?php echo formatCurrency($b['grand_total']); ?></td>
                                        <td>
                                            <a href="checkout.php?do_checkout=<?php echo $b['id']; ?>" class="btn btn-sm btn-danger fw-bold" data-confirm="Perform check-out and generate final bill for Room #<?php echo $b['room_number']; ?>?">
                                                <i class="fas fa-sign-out-alt me-1"></i> Complete Check-Out
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
