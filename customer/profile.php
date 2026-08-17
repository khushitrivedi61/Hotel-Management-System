<?php
$pageTitle = "My Profile - Grand Royale Hotel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('customer');

$userId = $_SESSION['user_id'];

// Get Customer Profile Details
$stmt = $pdo->prepare("
    SELECT u.name, u.email, u.phone, c.address, c.city, c.country, c.id_type, c.id_number 
    FROM users u 
    JOIN customers c ON u.id = c.user_id 
    WHERE u.id = ? LIMIT 1
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $idType = $_POST['id_type'];
    $idNumber = trim($_POST['id_number']);

    try {
        $pdo->beginTransaction();
        $uStmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        $uStmt->execute([$name, $phone, $userId]);

        $cStmt = $pdo->prepare("UPDATE customers SET address = ?, city = ?, id_type = ?, id_number = ? WHERE user_id = ?");
        $cStmt->execute([$address, $city, $idType, $idNumber, $userId]);

        $pdo->commit();
        $_SESSION['user_name'] = $name;
        $success = "Profile updated successfully!";
        
        // Refresh User Data
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Update failed: " . $e->getMessage();
    }
}
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-warning p-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-user-edit me-2"></i>Update Account Profile</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo sanitize($success); ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo sanitize($error); ?></div>
                    <?php endif; ?>

                    <form action="profile.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo sanitize($user['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address (Read-only)</label>
                                <input type="email" class="form-control bg-light" value="<?php echo sanitize($user['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo sanitize($user['phone']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" name="city" class="form-control" value="<?php echo sanitize($user['city']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">ID Type</label>
                                <select name="id_type" class="form-select">
                                    <option value="Aadhaar Card" <?php echo $user['id_type']=='Aadhaar Card'?'selected':''; ?>>Aadhaar Card</option>
                                    <option value="Passport" <?php echo $user['id_type']=='Passport'?'selected':''; ?>>Passport</option>
                                    <option value="Driving License" <?php echo $user['id_type']=='Driving License'?'selected':''; ?>>Driving License</option>
                                    <option value="Voter ID" <?php echo $user['id_type']=='Voter ID'?'selected':''; ?>>Voter ID</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">ID Document Number</label>
                                <input type="text" name="id_number" class="form-control" value="<?php echo sanitize($user['id_number']); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Residential Address</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo sanitize($user['address']); ?></textarea>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-warning fw-bold text-dark">Save Profile Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
