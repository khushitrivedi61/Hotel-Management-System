<?php
$pageTitle = "Activity Audit Logs - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

$logs = $pdo->query("
    SELECT a.*, u.name as user_name 
    FROM activity_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.id DESC LIMIT 100
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
                <div class="card-header bg-dark text-warning p-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-history me-2"></i>System Security & Audit Trail Logs</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Timestamp</th>
                                <th>Performed By</th>
                                <th>Action Event</th>
                                <th>Audit Description</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="small text-muted"><?php echo formatDate($log['created_at'], 'd M Y H:i:s'); ?></td>
                                    <td class="fw-semibold"><?php echo sanitize($log['user_name'] ?: 'System'); ?></td>
                                    <td><span class="badge bg-dark text-warning"><?php echo sanitize($log['action']); ?></span></td>
                                    <td class="small text-secondary"><?php echo sanitize($log['description']); ?></td>
                                    <td><code><?php echo sanitize($log['ip_address']); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
