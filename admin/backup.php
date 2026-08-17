<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/auth.php';

requireRole('admin');

if (isset($_GET['download']) && $_GET['download'] === 'sql') {
    $schemaFile = ROOT_PATH . '/database/schema.sql';
    if (file_exists($schemaFile)) {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="hotel_management_backup_' . date('Y-m-d_H-i') . '.sql"');
        readfile($schemaFile);
        exit();
    }
}

$pageTitle = "Database Backup - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-5 py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden p-5 bg-white text-center">
                <div class="p-4 bg-light rounded-circle mx-auto mb-3 text-warning fs-1" style="width: 100px; height: 100px;">
                    <i class="fas fa-database"></i>
                </div>
                <h3 class="fw-bold mb-2">MySQL Database Backup & Export</h3>
                <p class="text-secondary mb-4">Download complete relational SQL database backup file including schema, foreign keys, triggers, and seed data.</p>
                
                <div>
                    <a href="backup.php?download=sql" class="btn btn-warning btn-lg fw-bold text-dark px-5 py-3 shadow">
                        <i class="fas fa-download me-2"></i> Download Full SQL Backup (.sql)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
