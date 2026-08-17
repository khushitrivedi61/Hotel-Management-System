<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/helper.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/booking_functions.php';
require_once __DIR__ . '/../functions/invoice_functions.php';

requireRole('customer');

$userId = $_SESSION['user_id'];

// Get Customer ID
$custStmt = $pdo->prepare("SELECT id FROM customers WHERE user_id = ?");
$custStmt->execute([$userId]);
$customerId = $custStmt->fetchColumn();

// Action: Handle Payment Simulation BEFORE HTML headers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay') {
    $bookingId = (int)$_POST['booking_id'];
    $method = $_POST['payment_method'] ?? 'UPI';
    $res = processPaymentAndInvoice($bookingId, $method);
    if ($res['success']) {
        setFlash('success', 'Payment of simulated transaction was successful! Invoice generated.');
        redirect('customer/invoice.php?id=' . $res['invoice_id']);
    } else {
        setFlash('danger', $res['message']);
    }
}

// Action: Handle Booking Cancellation BEFORE HTML headers
if (isset($_GET['cancel_id'])) {
    $cancelId = (int)$_GET['cancel_id'];
    $res = updateBookingStatus($cancelId, 'Cancelled', $userId);
    if ($res['success']) {
        setFlash('success', 'Booking cancelled successfully.');
    } else {
        setFlash('danger', $res['message']);
    }
    redirect('customer/my-bookings.php');
}

$pageTitle = "My Reservations - Grand Royale Hotel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch Customer's Bookings
$stmt = $pdo->prepare("
    SELECT b.*, r.room_number, rt.type_name as room_type, inv.id as invoice_id 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    JOIN room_types rt ON r.room_type_id = rt.id 
    LEFT JOIN invoices inv ON b.id = inv.booking_id 
    WHERE b.customer_id = ? 
    ORDER BY b.id DESC
");
$stmt->execute([$customerId]);
$bookings = $stmt->fetchAll();
?>

<div class="container py-4">
    <?php displayFlash(); ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-warning p-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2"></i>My Reservations</h5>
                    <a href="search.php" class="btn btn-warning btn-sm fw-bold text-dark"><i class="fas fa-plus me-1"></i> New Booking</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Code</th>
                                <th>Room</th>
                                <th>Check-In / Out</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Invoice</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No bookings found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo sanitize($b['booking_code']); ?></td>
                                        <td>
                                            <div class="fw-semibold">Room #<?php echo sanitize($b['room_number']); ?></div>
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
                                            <?php if ($b['invoice_id']): ?>
                                                <a href="invoice.php?id=<?php echo $b['invoice_id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-print me-1"></i> Invoice
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">Pending Payment</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($b['status'] === 'Pending' || $b['status'] === 'Approved'): ?>
                                                <!-- Simulated Payment Modal Trigger -->
                                                <?php if (!$b['invoice_id']): ?>
                                                    <button type="button" class="btn btn-sm btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#payModal_<?php echo $b['id']; ?>">
                                                        <i class="fas fa-credit-card me-1"></i> Pay Now
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <a href="my-bookings.php?cancel_id=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm="Are you sure you want to cancel this booking?">
                                                    Cancel
                                                </a>
                                            <?php elseif ($b['status'] === 'Completed'): ?>
                                                <a href="review.php?booking_id=<?php echo $b['id']; ?>" class="btn btn-sm btn-warning fw-bold text-dark">
                                                    <i class="fas fa-star me-1"></i> Write Review
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Payment Modal -->
                                    <?php if (!$b['invoice_id']): ?>
                                        <div class="modal fade" id="payModal_<?php echo $b['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4">
                                                    <div class="modal-header bg-dark text-warning">
                                                        <h5 class="modal-title fw-bold"><i class="fas fa-credit-card me-2"></i>Simulated Payment Checkout</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="my-bookings.php" method="POST">
                                                        <input type="hidden" name="action" value="pay">
                                                        <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                        <div class="modal-body p-4">
                                                            <div class="alert alert-info small mb-3">
                                                                Select any payment method to test instant invoice creation.
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Grand Total Payable</label>
                                                                <input type="text" class="form-control fw-bold text-success fs-5" value="<?php echo formatCurrency($b['grand_total']); ?>" readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Payment Method</label>
                                                                <select name="payment_method" class="form-select">
                                                                    <option value="UPI">UPI (Google Pay / PhonePe / Paytm)</option>
                                                                    <option value="Credit Card">Credit Card</option>
                                                                    <option value="Debit Card">Debit Card</option>
                                                                    <option value="Net Banking">Net Banking</option>
                                                                    <option value="Cash">Cash at Check-In</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-warning fw-bold text-dark">Simulate Payment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
