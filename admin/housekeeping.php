<?php
$pageTitle = "Housekeeping Tasks - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

// Action: Handle Task Creation / Assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = (int)$_POST['room_id'];
    $staffId = !empty($_POST['staff_id']) ? (int)$_POST['staff_id'] : null;
    $taskType = $_POST['task_type'];
    $priority = $_POST['priority'];
    $notes = trim($_POST['notes']);

    try {
        $stmt = $pdo->prepare("INSERT INTO housekeeping_tasks (room_id, staff_id, task_type, status, priority, notes) VALUES (?, ?, ?, 'Pending', ?, ?)");
        $stmt->execute([$roomId, $staffId, $taskType, $priority, $notes]);
        
        // Update room status if task type is maintenance
        if ($taskType === 'Maintenance') {
            $pdo->prepare("UPDATE rooms SET status = 'Maintenance' WHERE id = ?")->execute([$roomId]);
        } elseif ($taskType === 'Cleaning') {
            $pdo->prepare("UPDATE rooms SET status = 'Cleaning' WHERE id = ?")->execute([$roomId]);
        }
        
        logActivity('Housekeeping Task Created', "Assigned {$taskType} task for Room ID {$roomId}");
        setFlash('success', "Housekeeping task assigned successfully.");
    } catch (PDOException $e) {
        setFlash('danger', "Task creation failed: " . $e->getMessage());
    }
    redirect('admin/housekeeping.php');
}

// Fetch All Tasks
$tasks = $pdo->query("
    SELECT ht.*, r.room_number, u.name as staff_name 
    FROM housekeeping_tasks ht 
    JOIN rooms r ON ht.room_id = r.id 
    LEFT JOIN staff s ON ht.staff_id = s.id 
    LEFT JOIN users u ON s.user_id = u.id 
    ORDER BY ht.id DESC
")->fetchAll();

$rooms = $pdo->query("SELECT id, room_number FROM rooms ORDER BY room_number ASC")->fetchAll();
$housekeepingStaff = $pdo->query("
    SELECT s.id, u.name 
    FROM staff s 
    JOIN users u ON s.user_id = u.id 
    WHERE s.department = 'Housekeeping' AND s.status = 'Active'
")->fetchAll();
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
                    <h5 class="fw-bold mb-0"><i class="fas fa-broom me-2"></i>Housekeeping & Maintenance Task Control</h5>
                    <button class="btn btn-warning btn-sm fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                        <i class="fas fa-plus me-1"></i> Assign New Task
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Room</th>
                                <th>Assigned Housekeeper</th>
                                <th>Task Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Assigned At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $t): ?>
                                <tr>
                                    <td class="fw-bold">Room #<?php echo sanitize($t['room_number']); ?></td>
                                    <td><?php echo sanitize($t['staff_name'] ?: 'Unassigned'); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo sanitize($t['task_type']); ?></span></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($t['priority']=='High' || $t['priority']=='Emergency'?'danger':'warning'); ?>">
                                            <?php echo sanitize($t['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo ($t['status']=='Completed'?'success':($t['status']=='In Progress'?'info':'secondary')); ?>">
                                            <?php echo sanitize($t['status']); ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?php echo formatDate($t['assigned_at'], 'd M Y H:i'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-warning">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Assign Housekeeping Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="housekeeping.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Room</label>
                        <select name="room_id" class="form-select" required>
                            <?php foreach ($rooms as $rm): ?>
                                <option value="<?php echo $rm['id']; ?>">Room #<?php echo sanitize($rm['room_number']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign Housekeeper Staff</label>
                        <select name="staff_id" class="form-select">
                            <option value="">Unassigned (Open Task Pool)</option>
                            <?php foreach ($housekeepingStaff as $hs): ?>
                                <option value="<?php echo $hs['id']; ?>"><?php echo sanitize($hs['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Task Type</label>
                            <select name="task_type" class="form-select">
                                <option value="Cleaning">Cleaning</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Inspection">Inspection</option>
                                <option value="Linen Change">Linen Change</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Emergency">Emergency</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Task Instructions</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Specific cleaning instructions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
