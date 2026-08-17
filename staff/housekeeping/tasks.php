<?php
$pageTitle = "Task History - Housekeeping Panel";
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

requireRole(['housekeeping', 'admin']);

$allTasks = $pdo->query("
    SELECT ht.*, r.room_number 
    FROM housekeeping_tasks ht 
    JOIN rooms r ON ht.room_id = r.id 
    ORDER BY ht.id DESC
")->fetchAll();
?>

<div class="container-fluid px-lg-5 py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark text-warning p-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-tasks me-2"></i>Complete Housekeeping Task Log</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Room Number</th>
                                <th>Task Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Assigned At</th>
                                <th>Completed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allTasks as $t): ?>
                                <tr>
                                    <td class="fw-bold">Room #<?php echo sanitize($t['room_number']); ?></td>
                                    <td><?php echo sanitize($t['task_type']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo sanitize($t['priority']); ?></span></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($t['status']=='Completed'?'success':'warning'); ?>">
                                            <?php echo sanitize($t['status']); ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?php echo formatDate($t['assigned_at'], 'd M Y H:i'); ?></td>
                                    <td class="small text-muted"><?php echo formatDate($t['completed_at'], 'd M Y H:i'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
