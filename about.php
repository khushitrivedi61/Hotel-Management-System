<?php
$pageTitle = "About Us - Grand Royale Hotel & Resort";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="bg-dark text-light py-5 border-bottom border-warning border-2">
    <div class="container text-center">
        <h2 class="fw-bold brand-font text-amber">About Grand Royale Hotel</h2>
        <p class="text-secondary mb-0">Discover our rich heritage of five-star hospitality and luxury</p>
    </div>
</div>

<div class="container py-5">
    <div class="row align-items-center g-5">
        <div class="col-lg-6">
            <img src="<?php echo BASE_URL; ?>/assets/images/hero-bg.jpg" class="img-fluid rounded-4 shadow-lg" alt="About Resort">
        </div>
        <div class="col-lg-6">
            <h6 class="text-warning text-uppercase fw-bold">Our Heritage</h6>
            <h3 class="fw-bold brand-font mb-3">Redefining World-Class Luxury</h3>
            <p class="text-secondary">
                Nestled on pristine beachfront property, Grand Royale Hotel & Resort offers an incomparable blend of sophisticated luxury, modern architectural splendor, and personalized guest services.
            </p>
            <p class="text-secondary">
                Designed as a complete BCA Major Project demonstration, our platform simulates enterprise-level hotel operations, dynamic room availability engines, multi-role staff access, and automated guest billing.
            </p>
            <div class="row g-3 text-center mt-3">
                <div class="col-4">
                    <div class="p-3 bg-light rounded-3">
                        <h3 class="fw-bold text-warning mb-0">50+</h3>
                        <small class="text-secondary">Luxury Suites</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-3 bg-light rounded-3">
                        <h3 class="fw-bold text-warning mb-0">15k+</h3>
                        <small class="text-secondary">Happy Guests</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-3 bg-light rounded-3">
                        <h3 class="fw-bold text-warning mb-0">4.9★</h3>
                        <small class="text-secondary">Overall Rating</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
