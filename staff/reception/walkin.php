<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../functions/helper.php';
require_once __DIR__ . '/../../functions/auth.php';
require_once __DIR__ . '/../../functions/room_functions.php';
require_once __DIR__ . '/../../functions/booking_functions.php';

requireRole(['receptionist', 'admin']);

$availableRooms = getAvailableRooms(date('Y-m-d'), date('Y-m-d', strtotime('+1 day')));
$error = '';

// Process Walk-in POST submission BEFORE rendering HTML headers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $idType = $_POST['id_type'];
    $idNumber = trim($_POST['id_number']);
    $roomId = (int)$_POST['room_id'];
    $checkIn = $_POST['check_in_date'];
    $checkOut = $_POST['check_out_date'];

    if (empty($name) || empty($email) || empty($phone) || empty($roomId)) {
        $error = "Please fill in all required guest & room fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $custStmt = $pdo->prepare("SELECT id FROM customers WHERE user_id = ?");
            $custStmt->execute([$user['id']]);
            $customerId = $custStmt->fetchColumn();
        } else {
            $reg = registerCustomer($name, $email, $phone, 'staff123', 'Walk-in Guest Address', 'Local City', 'India', $idType, $idNumber);
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $custStmt = $pdo->prepare("SELECT id FROM customers WHERE user_id = ?");
            $custStmt->execute([$user['id']]);
            $customerId = $custStmt->fetchColumn();
        }

        $res = createBooking($customerId, $roomId, $checkIn, $checkOut, 1, 'Walk-in guest registered by Reception desk');
        if ($res['success']) {
            updateBookingStatus($res['booking_id'], 'Checked-In', $_SESSION['user_id']);
            setFlash('success', "Walk-in booking completed! Guest checked into room immediately.");
            redirect('staff/reception/index.php');
        } else {
            $error = $res['message'];
        }
    }
}

$pageTitle = "Walk-in Reservation - Receptionist Desk";
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<div class="container-fluid px-lg-5 py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark text-warning p-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-walking me-2"></i>Instant Walk-In Guest Registration & Booking</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo sanitize($error); ?></div>
                    <?php endif; ?>

                    <form action="walkin.php" method="POST">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Guest Details</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Guest Full Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address *</label>
                                <input type="email" name="email" class="form-control" placeholder="guest@example.com" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mobile Phone *</label>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Govt ID Type</label>
                                <select name="id_type" class="form-select">
                                    <option value="Aadhaar Card">Aadhaar Card</option>
                                    <option value="Passport">Passport</option>
                                    <option value="Driving License">Driving License</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">ID Number</label>
                                <input type="text" name="id_number" class="form-control">
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Room & Dates Assignment</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Select Available Room *</label>
                                <select name="room_id" class="form-select" required>
                                    <?php foreach ($availableRooms as $ar): ?>
                                        <option value="<?php echo $ar['id']; ?>">Room #<?php echo sanitize($ar['room_number']); ?> (<?php echo sanitize($ar['type_name']); ?> - <?php echo formatCurrency($ar['price_per_night']); ?>/night)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Check-In Date</label>
                                <input type="date" name="check_in_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Check-Out Date</label>
                                <input type="date" name="check_out_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark py-3">
                                <i class="fas fa-check-circle me-2"></i> Register & Instant Check-In
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
