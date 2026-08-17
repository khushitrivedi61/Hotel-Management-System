<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/functions/helper.php';
require_once __DIR__ . '/functions/auth.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Process Password Change BEFORE rendering HTML headers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all password fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userPass = $stmt->fetchColumn();
        
        if (!password_verify($current_password, $userPass)) {
            $error = "Current password is incorrect.";
        } else {
            $newHash = password_hash($new_password, PASSWORD_DEFAULT);
            $uStmt = $pdo->prepare("UPDATE users SET password = ?, force_password_change = 0 WHERE id = ?");
            $uStmt->execute([$newHash, $_SESSION['user_id']]);
            
            $_SESSION['force_password_change'] = 0;
            logActivity('Password Changed', "User ID {$_SESSION['user_id']} updated their password.");
            
            setFlash('success', 'Your password has been changed successfully!');
            
            $role = $_SESSION['user_role'];
            if ($role === 'admin') redirect('admin/index.php');
            elseif ($role === 'receptionist') redirect('staff/reception/index.php');
            elseif ($role === 'housekeeping') redirect('staff/housekeeping/index.php');
            else redirect('customer/index.php');
        }
    }
}

$pageTitle = "Change Password - Grand Royale Hotel & Resort";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-warning text-center p-4 border-bottom border-warning">
                    <h5 class="fw-bold mb-0"><i class="fas fa-key me-2"></i>Change Account Password</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <?php if (isset($_SESSION['force_password_change']) && $_SESSION['force_password_change'] == 1): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i><strong>Security Notice:</strong> You are required to update your default staff password before continuing.
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo sanitize($error); ?></div>
                    <?php endif; ?>

                    <form action="change-password.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-warning fw-bold text-dark">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
