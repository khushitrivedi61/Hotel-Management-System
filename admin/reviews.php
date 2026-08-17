<?php
$pageTitle = "Guest Reviews Moderation - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $pdo->prepare("UPDATE reviews SET status = ? WHERE id = ?")->execute([$status, $id]);
    setFlash('success', "Review status updated to {$status}.");
    redirect('admin/reviews.php');
}

$reviews = $pdo->query("
    SELECT r.*, u.name as customer_name 
    FROM reviews r 
    JOIN customers c ON r.customer_id = c.id 
    JOIN users u ON c.user_id = u.id 
    ORDER BY r.id DESC
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
                    <h5 class="fw-bold mb-0"><i class="fas fa-star me-2"></i>Guest Reviews Moderation Panel</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Guest</th>
                                <th>Rating</th>
                                <th>Title & Content</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $rev): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo sanitize($rev['customer_name']); ?></td>
                                    <td class="text-warning">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="<?php echo $i <= $rev['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo sanitize($rev['review_title']); ?></div>
                                        <small class="text-secondary"><?php echo sanitize($rev['review_text']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo ($rev['status']=='Approved'?'success':($rev['status']=='Hidden'?'danger':'warning')); ?>">
                                            <?php echo sanitize($rev['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($rev['status'] !== 'Approved'): ?>
                                            <a href="reviews.php?id=<?php echo $rev['id']; ?>&status=Approved" class="btn btn-sm btn-outline-success">Approve</a>
                                        <?php endif; ?>
                                        <?php if ($rev['status'] !== 'Hidden'): ?>
                                            <a href="reviews.php?id=<?php echo $rev['id']; ?>&status=Hidden" class="btn btn-sm btn-outline-danger">Hide</a>
                                        <?php endif; ?>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
