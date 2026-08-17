<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../functions/helper.php';
require_once __DIR__ . '/../../functions/auth.php';

requireRole(['housekeeping', 'admin']);

$userId = $_SESSION['user_id'];

// Process Task Status Action BEFORE rendering HTML headers
if (isset($_GET['task_id']) && isset($_GET['update_status'])) {
    $taskId = (int)$_GET['task_id'];
    $newStatus = $_GET['update_status'];
    
    $tStmt = $pdo->prepare("SELECT room_id FROM housekeeping_tasks WHERE id = ?");
    $tStmt->execute([$taskId]);
    $roomId = $tStmt->fetchColumn();
    
    if ($newStatus === 'Completed') {
        $pdo->prepare("UPDATE housekeeping_tasks SET status = 'Completed', completed_at = NOW() WHERE id = ?")->execute([$taskId]);
        // AUTOMATIC ROOM STATE TRANSITION: Mark room Available!
        $pdo->prepare("UPDATE rooms SET status = 'Available' WHERE id = ?")->execute([$roomId]);
        setFlash('success', 'Task marked Completed! Room is now marked Clean & Available for new guests.');
    } else {
        $pdo->prepare("UPDATE housekeeping_tasks SET status = ? WHERE id = ?")->execute([$newStatus, $taskId]);
        setFlash('info', "Task status updated to {$newStatus}.");
    }
    redirect('staff/housekeeping/index.php');
}

$pageTitle = "Housekeeping Portal - Grand Royale Hotel";
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

// Fetch Cleaning Tasks
$tasks = $pdo->query("
    SELECT ht.*, r.room_number, r.floor, rt.type_name 
    FROM housekeeping_tasks ht 
    JOIN rooms r ON ht.room_id = r.id 
    JOIN room_types rt ON r.room_type_id = rt.id 
    WHERE ht.status != 'Completed' 
    ORDER BY ht.priority DESC, ht.id DESC
")->fetchAll();
?>

<div class="container-fluid px-lg-5 py-4">
    <?php displayFlash(); ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-light mb-4 border-start border-warning border-5">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1 text-amber">Housekeeping & Room Sanitation Console</h4>
                        <p class="text-secondary small mb-0">View assigned room cleaning tasks and update room readiness for guests</p>
                    </div>
                    <span class="badge bg-info text-dark fw-bold px-3 py-2"><i class="fas fa-broom me-1"></i> Staff On Duty</span>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-tasks text-warning me-2"></i>Pending & Active Cleaning Assignments</h6>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Room #</th>
                                <th>Floor & Type</th>
                                <th>Task Details</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tasks)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">All assigned rooms are clean and ready! No pending tasks.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tasks as $t): ?>
                                    <tr>
                                        <td class="fw-bold fs-5 text-dark">Room #<?php echo sanitize($t['room_number']); ?></td>
                                        <td class="small">
                                            <div class="fw-semibold">Floor <?php echo $t['floor']; ?></div>
                                            <div class="text-muted"><?php echo sanitize($t['type_name']); ?></div>
                                        </td>
                                        <td class="small">
                                            <div class="fw-bold text-primary"><?php echo sanitize($t['task_type']); ?></div>
                                            <div class="text-secondary"><?php echo sanitize($t['notes'] ?: 'Standard cleaning'); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo ($t['priority']=='High' || $t['priority']=='Emergency'?'danger':'warning'); ?>">
                                                <?php echo sanitize($t['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo ($t['status']=='In Progress'?'info':'secondary'); ?>">
                                                <?php echo sanitize($t['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($t['status'] === 'Pending'): ?>
                                                <a href="index.php?task_id=<?php echo $t['id']; ?>&update_status=In+Progress" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-play me-1"></i> Start Task
                                                </a>
                                            <?php endif; ?>
                                            <a href="index.php?task_id=<?php echo $t['id']; ?>&update_status=Completed" class="btn btn-sm btn-success fw-bold">
                                                <i class="fas fa-check-double me-1"></i> Mark Clean & Ready
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
