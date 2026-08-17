<?php
$hotelName = getSetting('hotel_name', 'Grand Royale Hotel & Resort');
$hotelAddress = getSetting('hotel_address', 'Beach Road, Luxury Enclave, Goa 403001');
$hotelPhone = getSetting('hotel_phone', '+91 98765 43210');
$hotelEmail = getSetting('hotel_email', 'info@grandroyalehotel.com');
?>
<footer class="bg-dark text-light mt-auto pt-5 pb-3 border-top border-warning border-3">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Brand & Tagline -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo" height="40" class="rounded">
                    <h5 class="fw-bold text-amber mb-0"><?php echo sanitize($hotelName); ?></h5>
                </div>
                <p class="text-secondary small">
                    Experience world-class luxury, opulent suite accommodations, and unmatched hospitality. Designed as a BCA Major Project.
                </p>
                <div class="d-flex gap-3 text-amber fs-5 mt-3">
                    <a href="#" class="text-amber hover-gold"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-amber hover-gold"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-amber hover-gold"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-amber hover-gold"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h6 class="text-uppercase text-warning fw-bold mb-3 tracking-wider">Navigation</h6>
                <ul class="list-unstyled text-secondary small d-grid gap-2">
                    <li><a href="<?php echo BASE_URL; ?>/index.php" class="text-decoration-none text-secondary hover-gold"><i class="fas fa-chevron-right text-warning me-2 fs-6"></i>Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/rooms.php" class="text-decoration-none text-secondary hover-gold"><i class="fas fa-chevron-right text-warning me-2 fs-6"></i>Our Rooms</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/amenities.php" class="text-decoration-none text-secondary hover-gold"><i class="fas fa-chevron-right text-warning me-2 fs-6"></i>Amenities</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/gallery.php" class="text-decoration-none text-secondary hover-gold"><i class="fas fa-chevron-right text-warning me-2 fs-6"></i>Gallery</a></li>
                </ul>
            </div>

            <!-- Portals -->
            <div class="col-lg-3 col-md-6">
                <h6 class="text-uppercase text-warning fw-bold mb-3 tracking-wider">Access Portals</h6>
                <ul class="list-unstyled text-secondary small d-grid gap-2">
                    <li><a href="<?php echo BASE_URL; ?>/login.php" class="text-decoration-none text-secondary hover-gold"><i class="fas fa-lock text-warning me-2 fs-6"></i>Unified Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/register.php" class="text-decoration-none text-secondary hover-gold"><i class="fas fa-user-plus text-warning me-2 fs-6"></i>Customer Register</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/index.php" class="text-decoration-none text-secondary hover-gold"><i class="fas fa-user-shield text-warning me-2 fs-6"></i>Admin Console</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/staff/reception/index.php" class="text-decoration-none text-secondary hover-gold"><i class="fas fa-concierge-bell text-warning me-2 fs-6"></i>Reception Desk</a></li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-3 col-md-6">
                <h6 class="text-uppercase text-warning fw-bold mb-3 tracking-wider">Contact Us</h6>
                <ul class="list-unstyled text-secondary small d-grid gap-2">
                    <li class="d-flex align-items-start gap-2">
                        <i class="fas fa-map-marker-alt text-warning mt-1"></i>
                        <span><?php echo sanitize($hotelAddress); ?></span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="fas fa-phone-alt text-warning"></i>
                        <span><?php echo sanitize($hotelPhone); ?></span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="fas fa-envelope text-warning"></i>
                        <span><?php echo sanitize($hotelEmail); ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="border-secondary my-3">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary small gap-2">
            <div>
                &copy; <?php echo date('Y'); ?> <strong><?php echo sanitize($hotelName); ?></strong>. All Rights Reserved. (BCA Major Project)
            </div>
            <div>
                Built with <i class="fas fa-heart text-danger"></i> using Core PHP & MySQL
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Main JS -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
