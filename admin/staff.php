<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/helper.php';
require_once __DIR__ . '/../functions/auth.php';

requireRole('admin');

// Action: Handle Add / Edit / Toggle Staff BEFORE rendering HTML headers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = trim($_POST['password']);
        $department = $_POST['department'];
        $role = $_POST['role'];
        $salary = (float)($_POST['salary'] ?? 0);
        $empCode = 'EMP-' . strtoupper(substr($department, 0, 3)) . '-' . rand(100, 999);

        // Check duplicate email
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            setFlash('danger', 'Email address is already registered.');
        } else {
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);
            try {
                $pdo->beginTransaction();
                
                // Insert User Account with force_password_change = 1
                $uStmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, account_status, force_password_change) VALUES (?, ?, ?, ?, ?, 'active', 1)");
                $uStmt->execute([$name, $email, $phone, $hashedPass, $role]);
                $userId = $pdo->lastInsertId();

                // Insert Staff Record
                $sStmt = $pdo->prepare("INSERT INTO staff (user_id, employee_code, department, salary, date_of_joining) VALUES (?, ?, ?, ?, CURDATE())");
                $sStmt->execute([$userId, $empCode, $department, $salary]);

                $pdo->commit();
                logActivity('Staff Account Created', "Created staff user {$email} with role {$role}");
                setFlash('success', "Staff member {$name} ({$empCode}) created successfully.");
            } catch (PDOException $e) {
                $pdo->rollBack();
                setFlash('danger', "Failed to create staff: " . $e->getMessage());
            }
        }
        redirect('admin/staff.php');
    } elseif ($action === 'toggle_status') {
        $userId = (int)$_POST['user_id'];
        $currentStatus = $_POST['current_status'];
        $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

        $uStmt = $pdo->prepare("UPDATE users SET account_status = ? WHERE id = ?");
        $uStmt->execute([$newStatus, $userId]);
        logActivity('Staff Status Changed', "Staff user ID {$userId} account status set to {$newStatus}");
        setFlash('success', "Staff status updated to " . ucfirst($newStatus));
        redirect('admin/staff.php');
    }
}

if (isset($_GET['delete_id'])) {
    $delUserId = (int)$_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$delUserId]);
        logActivity('Staff Deleted', "Deleted staff user ID {$delUserId}");
        setFlash('success', "Staff member deleted successfully.");
    } catch (PDOException $e) {
        setFlash('danger', "Failed to delete staff: " . $e->getMessage());
    }
    redirect('admin/staff.php');
}

$pageTitle = "Staff & RBAC Management - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch All Staff Members
$staffStmt = $pdo->query("
    SELECT s.*, u.id as user_id, u.name, u.email, u.phone, u.role, u.account_status, u.last_login 
    FROM staff s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY s.id DESC
");
$staffMembers = $staffStmt->fetchAll();
?>

<div class="container-fluid px-lg-5 py-4">
    <?php displayFlash(); ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark text-warning p-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-users-cog me-2"></i>Staff & RBAC Access Management</h5>
                    <button class="btn btn-warning btn-sm fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                        <i class="fas fa-user-plus me-1"></i> Add Staff Member
                    </button>
                </div>

                <div class="p-3 bg-light border-bottom">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm table-search-input" data-target="staffTable" placeholder="Live search staff...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="staffTable">
                        <thead class="table-light small">
                            <tr>
                                <th>Emp Code</th>
                                <th>Staff Name</th>
                                <th>Email / Contact</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staffMembers as $st): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?php echo sanitize($st['employee_code']); ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo sanitize($st['name']); ?></div>
                                        <small class="text-muted">Joined: <?php echo formatDate($st['date_of_joining']); ?></small>
                                    </td>
                                    <td class="small">
                                        <div><i class="fas fa-envelope text-warning me-1"></i> <?php echo sanitize($st['email']); ?></div>
                                        <div><i class="fas fa-phone text-warning me-1"></i> <?php echo sanitize($st['phone']); ?></div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo sanitize($st['department']); ?></span></td>
                                    <td>
                                        <span class="badge bg-dark text-warning text-uppercase"><?php echo sanitize($st['role']); ?></span>
                                    </td>
                                    <td>
                                        <form action="staff.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?php echo $st['user_id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $st['account_status']; ?>">
                                            <button type="submit" class="btn btn-sm btn-<?php echo $st['account_status']=='active'?'success':'danger'; ?> py-0 px-2 small">
                                                <?php echo ucfirst($st['account_status']); ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="staff.php?delete_id=<?php echo $st['user_id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm="Are you sure you want to remove staff member <?php echo sanitize($st['name']); ?>?">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-warning">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Create New Staff Member</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="staff.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Staff Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Michael Scott" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="michael@hotel.com" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Mobile Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+91 9876543210" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Department *</label>
                            <select name="department" class="form-select" required>
                                <option value="Reception">Reception</option>
                                <option value="Housekeeping">Housekeeping</option>
                                <option value="Kitchen">Kitchen</option>
                                <option value="Security">Security</option>
                                <option value="Manager">Manager</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Access Role *</label>
                            <select name="role" class="form-select" required>
                                <option value="receptionist">Receptionist</option>
                                <option value="housekeeping">Housekeeping</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Default Password *</label>
                            <input type="password" name="password" class="form-control" placeholder="staff123" required minlength="6">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Monthly Salary (₹)</label>
                            <input type="number" step="0.01" name="salary" class="form-control" placeholder="30000.00">
                        </div>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-1"></i> The staff member will be required to change their password on first login.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Create Staff Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
