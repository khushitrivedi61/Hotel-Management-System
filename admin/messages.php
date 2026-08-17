<?php
$pageTitle = "Contact Inquiries - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
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
                    <h5 class="fw-bold mb-0"><i class="fas fa-envelope-open-text me-2"></i>Guest Contact Inquiries</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Sender</th>
                                <th>Contact Info</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo sanitize($msg['name']); ?></td>
                                    <td class="small">
                                        <div><i class="fas fa-envelope text-warning me-1"></i> <?php echo sanitize($msg['email']); ?></div>
                                        <div><i class="fas fa-phone text-warning me-1"></i> <?php echo sanitize($msg['phone']); ?></div>
                                    </td>
                                    <td class="fw-semibold"><?php echo sanitize($msg['subject']); ?></td>
                                    <td class="small text-secondary"><?php echo sanitize($msg['message']); ?></td>
                                    <td class="small text-muted"><?php echo formatDate($msg['created_at'], 'd M Y H:i'); ?></td>
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
