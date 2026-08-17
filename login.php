<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/functions/helper.php';
require_once __DIR__ . '/functions/auth.php';

// Process Login BEFORE HTML output so redirects work 100% cleanly without header warnings
if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin') redirect('admin/index.php');
    elseif ($role === 'receptionist') redirect('staff/reception/index.php');
    elseif ($role === 'housekeeping') redirect('staff/housekeeping/index.php');
    else redirect('customer/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = "Please provide both email and password.";
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            setFlash('success', 'Welcome back to Grand Royale Hotel!');
            
            if ($result['force_password_change']) {
                redirect('change-password.php');
            }
            
            $role = $result['role'];
            if ($role === 'admin') redirect('admin/index.php');
            elseif ($role === 'receptionist') redirect('staff/reception/index.php');
            elseif ($role === 'housekeeping') redirect('staff/housekeeping/index.php');
            else redirect('customer/index.php');
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = "Login - Grand Royale Hotel & Resort";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-center p-4 border-bottom border-warning border-3">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo" height="55" class="mb-2 rounded">
                    <h4 class="fw-bold text-amber mb-0">Portal Login</h4>
                    <p class="text-secondary small mb-0">Unified Login for Admin, Staff & Guests</p>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <?php displayFlash(); ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo sanitize($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-warning"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="e.g. admin@hotel.com" required value="<?php echo sanitize($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <a href="forgot-password.php" class="small text-warning text-decoration-none hover-gold">Forgot Password?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-warning"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark py-2 shadow-sm">
                                <i class="fas fa-sign-in-alt me-2"></i> Log In
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center text-muted small">
                        New Guest? <a href="register.php" class="fw-bold text-warning text-decoration-none hover-gold">Create a Customer Account</a>
                    </div>
                </div>
                
                <!-- Demo Credentials Box -->
                <div class="card-footer bg-light p-3 border-top text-center small text-secondary">
                    <div class="fw-bold text-dark mb-1"><i class="fas fa-info-circle text-primary me-1"></i> Demo Credentials</div>
                    <div>Admin: <code>admin@hotel.com</code> / <code>admin123</code></div>
                    <div>Reception: <code>reception@hotel.com</code> / <code>staff123</code></div>
                    <div>Housekeeping: <code>housekeeping@hotel.com</code> / <code>staff123</code></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
