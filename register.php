<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/functions/helper.php';
require_once __DIR__ . '/functions/auth.php';

$error = '';
$success = '';

// Process Customer Registration BEFORE rendering HTML headers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $idType = $_POST['id_type'] ?? 'Aadhaar Card';
    $idNumber = trim($_POST['id_number'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Password and Confirm Password do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $res = registerCustomer($name, $email, $phone, $password, $address, $city, 'India', $idType, $idNumber);
        if ($res['success']) {
            setFlash('success', 'Registration successful! You can now log in.');
            redirect('login.php');
        } else {
            $error = $res['message'];
        }
    }
}

$pageTitle = "Customer Registration - Grand Royale Hotel & Resort";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-center p-4 border-bottom border-warning border-3">
                    <h4 class="fw-bold text-amber mb-0">Customer Registration</h4>
                    <p class="text-secondary small mb-0">Create your account to book luxury rooms & access exclusive guest privileges</p>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo sanitize($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name *</label>
                                <input type="text" name="name" class="form-control" placeholder="John Doe" required value="<?php echo sanitize($_POST['name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address *</label>
                                <input type="email" name="email" class="form-control" placeholder="john@example.com" required value="<?php echo sanitize($_POST['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mobile Number *</label>
                                <input type="tel" name="phone" class="form-control" placeholder="+91 9876543210" required value="<?php echo sanitize($_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" name="city" class="form-control" placeholder="e.g. Mumbai" value="<?php echo sanitize($_POST['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Government ID Type</label>
                                <select name="id_type" class="form-select">
                                    <option value="Aadhaar Card">Aadhaar Card</option>
                                    <option value="Passport">Passport</option>
                                    <option value="Driving License">Driving License</option>
                                    <option value="Voter ID">Voter ID</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">ID Document Number</label>
                                <input type="text" name="id_number" class="form-control" placeholder="Enter ID Document No." value="<?php echo sanitize($_POST['id_number'] ?? ''); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Residential Address</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="Full address details"><?php echo sanitize($_POST['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password *</label>
                                <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirm Password *</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark py-2 shadow-sm">
                                <i class="fas fa-user-check me-2"></i> Register Account
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">
                    <div class="text-center text-muted small">
                        Already registered? <a href="login.php" class="fw-bold text-warning text-decoration-none hover-gold">Log In Here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
