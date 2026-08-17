<?php
$pageTitle = "Hotel Amenities - Grand Royale Hotel & Resort";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$stmt = $pdo->query("SELECT * FROM amenities ORDER BY id ASC");
$amenities = $stmt->fetchAll();
?>

<div class="bg-dark text-light py-5 border-bottom border-warning border-2">
    <div class="container text-center">
        <h2 class="fw-bold brand-font text-amber">World-Class Luxury Amenities</h2>
        <p class="text-secondary mb-0">Designed to make your stay unforgettable</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <?php foreach ($amenities as $am): ?>
            <div class="col-md-4 col-sm-6">
                <div class="card h-100 border-0 shadow-sm p-4 rounded-4 bg-white text-center">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 text-warning fs-2" style="width: 70px; height: 70px;">
                        <i class="fas <?php echo sanitize($am['icon_class']); ?>"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?php echo sanitize($am['name']); ?></h5>
                    <p class="text-secondary small mb-0"><?php echo sanitize($am['description']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
