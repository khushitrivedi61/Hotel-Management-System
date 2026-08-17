<?php
$pageTitle = "Forgot Password - Grand Royale Hotel & Resort";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $message = "If an account exists for {$email}, a password reset link has been dispatched to your email address.";
    }
}
?>

<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-warning text-center p-4 border-bottom border-warning">
                    <h5 class="fw-bold mb-0"><i class="fas fa-unlock-alt me-2"></i>Reset Your Password</h5>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <?php if ($message): ?>
                        <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i><?php echo sanitize($message); ?></div>
                    <?php endif; ?>

                    <form action="forgot-password.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Registered Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="enter registered email" required>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-warning fw-bold text-dark">Send Reset Link</button>
                        </div>
                    </form>
                    <div class="text-center mt-3 small">
                        <a href="login.php" class="text-decoration-none text-warning">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
