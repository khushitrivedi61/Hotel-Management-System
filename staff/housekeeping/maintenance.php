<?php
$pageTitle = "Maintenance Requests - Housekeeping Panel";
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

requireRole(['housekeeping', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = (int)$_POST['room_id'];
    $issue = trim($_POST['issue_description']);
    $priority = $_POST['priority'];

    try {
        $stmt = $pdo->prepare("INSERT INTO maintenance_requests (room_id, issue_description, priority, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->execute([$roomId, $issue, $priority]);
        
        // Update room status to Maintenance
        $pdo->prepare("UPDATE rooms SET status = 'Maintenance' WHERE id = ?")->execute([$roomId]);
        setFlash('success', 'Maintenance issue reported. Room status marked Under Maintenance.');
    } catch (PDOException $e) {
        setFlash('danger', 'Failed to submit maintenance request: ' . $e->getMessage());
    }
    redirect('staff/housekeeping/maintenance.php');
}

$requests = $pdo->query("
    SELECT mr.*, r.room_number 
    FROM maintenance_requests mr 
    JOIN rooms r ON mr.room_id = r.id 
    ORDER BY mr.id DESC
")->fetchAll();

$rooms = $pdo->query("SELECT id, room_number FROM rooms ORDER BY room_number ASC")->fetchAll();
?>

<div class="container-fluid px-lg-5 py-4">
    <?php displayFlash(); ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark text-warning p-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-tools me-2"></i>Maintenance Issue Reporting</h5>
                    <button class="btn btn-warning btn-sm fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addIssueModal">
                        <i class="fas fa-plus me-1"></i> Report Issue
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Room Number</th>
                                <th>Issue Description</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Reported At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                                <tr>
                                    <td class="fw-bold">Room #<?php echo sanitize($req['room_number']); ?></td>
                                    <td class="small text-secondary"><?php echo sanitize($req['issue_description']); ?></td>
                                    <td><span class="badge bg-danger"><?php echo sanitize($req['priority']); ?></span></td>
                                    <td><span class="badge bg-warning text-dark"><?php echo sanitize($req['status']); ?></span></td>
                                    <td class="small text-muted"><?php echo formatDate($req['created_at'], 'd M Y H:i'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Issue Modal -->
<div class="modal fade" id="addIssueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-warning">
                <h5 class="modal-title fw-bold"><i class="fas fa-tools me-2"></i>Report Room Maintenance Issue</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="maintenance.php" method="POST">
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
                        <label class="form-label fw-semibold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Issue Description *</label>
                        <textarea name="issue_description" class="form-control" rows="3" placeholder="e.g. AC unit leaking, shower tap broken" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
