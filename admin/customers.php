<?php
$pageTitle = "Customer Profiles - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

if (isset($_GET['delete_id'])) {
    $delUserId = (int)$_GET['delete_id'];
    try {
        $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'")->execute([$delUserId]);
        setFlash('success', "Customer profile deleted successfully.");
    } catch (PDOException $e) {
        setFlash('danger', "Cannot delete customer: " . $e->getMessage());
    }
    redirect('admin/customers.php');
}

$customers = $pdo->query("
    SELECT c.*, u.id as user_id, u.name, u.email, u.phone, u.account_status, u.created_at as registered_date 
    FROM customers c 
    JOIN users u ON c.user_id = u.id 
    ORDER BY c.id DESC
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
                    <h5 class="fw-bold mb-0"><i class="fas fa-user-friends me-2"></i>Registered Customers Directory</h5>
                </div>

                <div class="p-3 bg-light border-bottom">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm table-search-input" data-target="customersTable" placeholder="Live search customers...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="customersTable">
                        <thead class="table-light small">
                            <tr>
                                <th>Customer Name</th>
                                <th>Email / Phone</th>
                                <th>City / Address</th>
                                <th>Govt ID Proof</th>
                                <th>Registered Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $cust): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo sanitize($cust['name']); ?></td>
                                    <td class="small">
                                        <div><i class="fas fa-envelope text-warning me-1"></i> <?php echo sanitize($cust['email']); ?></div>
                                        <div><i class="fas fa-phone text-warning me-1"></i> <?php echo sanitize($cust['phone']); ?></div>
                                    </td>
                                    <td class="small text-secondary">
                                        <div><?php echo sanitize($cust['city'] ?: 'N/A'); ?></div>
                                        <small class="text-muted"><?php echo sanitize($cust['address']); ?></small>
                                    </td>
                                    <td class="small">
                                        <span class="badge bg-light text-dark border"><?php echo sanitize($cust['id_type']); ?></span>
                                        <div><code><?php echo sanitize($cust['id_number']); ?></code></div>
                                    </td>
                                    <td class="small text-muted"><?php echo formatDate($cust['registered_date']); ?></td>
                                    <td>
                                        <a href="customers.php?delete_id=<?php echo $cust['user_id']; ?>" class="btn btn-sm btn-outline-danger" data-confirm="Are you sure you want to delete customer <?php echo sanitize($cust['name']); ?>?">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
