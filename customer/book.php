<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/helper.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/room_functions.php';
require_once __DIR__ . '/../functions/booking_functions.php';

requireRole('customer');

$userId = $_SESSION['user_id'];
$roomId = $_GET['room_id'] ?? $_POST['room_id'] ?? null;
$checkIn = $_GET['check_in'] ?? $_POST['check_in'] ?? date('Y-m-d');
$checkOut = $_GET['check_out'] ?? $_POST['check_out'] ?? date('Y-m-d', strtotime('+1 day'));

if (!$roomId) {
    setFlash('warning', 'Please select a room to proceed with booking.');
    redirect('customer/search.php');
}

$room = getRoomById($roomId);
if (!$room) {
    setFlash('danger', 'Invalid room selection.');
    redirect('customer/search.php');
}

// Fetch Customer Record
$custStmt = $pdo->prepare("SELECT id FROM customers WHERE user_id = ?");
$custStmt->execute([$userId]);
$customerId = $custStmt->fetchColumn();

// Process POST submission BEFORE headers are rendered
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numGuests = (int)($_POST['num_guests'] ?? 1);
    $specialRequests = trim($_POST['special_requests'] ?? '');
    $couponCode = trim($_POST['coupon_code'] ?? '');
    $selectedServices = $_POST['services'] ?? [];

    $res = createBooking($customerId, $roomId, $checkIn, $checkOut, $numGuests, $specialRequests, $couponCode, $selectedServices);
    
    if ($res['success']) {
        setFlash('success', "Booking request submitted successfully! Booking Code: {$res['booking_code']}");
        redirect('customer/my-bookings.php');
    } else {
        $error = $res['message'];
    }
}

// Fetch Extra Services
$srvStmt = $pdo->query("SELECT * FROM extra_services WHERE status = 'Active'");
$extraServices = $srvStmt->fetchAll();

$pageTitle = "Complete Reservation - Grand Royale Hotel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$nights = calculateNights($checkIn, $checkOut);
$estimatedSubtotal = $nights * (float)$room['price_per_night'];
$estimatedTax = ($estimatedSubtotal * 18) / 100;
$estimatedTotal = $estimatedSubtotal + $estimatedTax;
$imgFile = getRoomImageFileName($room['type_name']);
$displayCapacity = (strtolower($room['type_name']) === 'deluxe double') ? 4 : $room['capacity'];
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark text-warning p-4 border-bottom border-warning">
                    <h4 class="fw-bold mb-0"><i class="fas fa-file-signature me-2"></i>Complete Your Luxury Reservation</h4>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo sanitize($error); ?></div>
                    <?php endif; ?>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light p-3 rounded-3 d-flex flex-row gap-3 align-items-center">
                                <img src="<?php echo BASE_URL; ?>/assets/images/<?php echo $imgFile; ?>" alt="Room" class="rounded" style="width: 100px; height: 80px; object-fit: cover;">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Room Summary</h6>
                                    <h5 class="fw-bold text-warning mb-1"><?php echo sanitize($room['type_name']); ?></h5>
                                    <div class="small text-secondary mb-1">Room #<?php echo sanitize($room['room_number']); ?> | Floor <?php echo $room['floor']; ?></div>
                                    <div class="badge bg-dark text-warning px-2 py-1"><?php echo formatCurrency($room['price_per_night']); ?> / Night</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 bg-light p-3 rounded-3">
                                <h6 class="fw-bold text-dark mb-2">Stay Duration</h6>
                                <div class="d-flex justify-content-between text-secondary small mb-1">
                                    <span>Check-In:</span> <strong class="text-dark"><?php echo formatDate($checkIn); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between text-secondary small mb-1">
                                    <span>Check-Out:</span> <strong class="text-dark"><?php echo formatDate($checkOut); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between text-secondary small fw-bold text-dark border-top pt-2 mt-2">
                                    <span>Total Nights:</span> <span><?php echo $nights; ?> Night(s)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="book.php?room_id=<?php echo $roomId; ?>" method="POST">
                        <input type="hidden" name="room_id" value="<?php echo $roomId; ?>">
                        <input type="hidden" name="check_in" value="<?php echo $checkIn; ?>">
                        <input type="hidden" name="check_out" value="<?php echo $checkOut; ?>">
                        <input type="hidden" id="room_price_val" value="<?php echo $room['price_per_night']; ?>">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Number of Guests</label>
                                <select name="num_guests" class="form-select">
                                    <?php for($g=1; $g<=$displayCapacity; $g++): ?>
                                        <option value="<?php echo $g; ?>"><?php echo $g; ?> Guest(s)</option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Coupon / Promo Code</label>
                                <input type="text" name="coupon_code" class="form-control text-uppercase" placeholder="e.g. WELCOME10">
                            </div>
                        </div>

                        <!-- Extra Services Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Select Additional Resort Services</label>
                            <div class="row g-2">
                                <?php foreach ($extraServices as $srv): ?>
                                    <div class="col-md-6">
                                        <div class="form-check card border p-3 rounded-3">
                                            <input class="form-check-input me-2" type="checkbox" name="services[]" value="<?php echo $srv['id']; ?>" id="srv_<?php echo $srv['id']; ?>">
                                            <label class="form-check-label d-flex justify-content-between w-100" for="srv_<?php echo $srv['id']; ?>">
                                                <span><strong><?php echo sanitize($srv['service_name']); ?></strong></span>
                                                <span class="text-warning fw-bold">+ <?php echo formatCurrency($srv['price']); ?></span>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Special Requests (Optional)</label>
                            <textarea name="special_requests" class="form-control" rows="2" placeholder="e.g. High floor room, extra towels, early check-in preference"></textarea>
                        </div>

                        <div class="card border-warning border-2 p-3 bg-light rounded-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Estimated Grand Total (Inc. 18% GST)</h6>
                                    <small class="text-muted">Final total computed at booking confirmation</small>
                                </div>
                                <h4 class="fw-bold text-success mb-0" id="calculated_total_price"><?php echo formatCurrency($estimatedTotal); ?></h4>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark py-3 shadow">
                                <i class="fas fa-check-circle me-2"></i> Confirm & Book Reservation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
