<?php
$hotelName = getSetting('hotel_name', 'Grand Royale Hotel & Resort');
$userRole = $_SESSION['user_role'] ?? null;
$userName = $_SESSION['user_name'] ?? 'Guest';
?>
<nav class="navbar navbar-expand-lg bg-dark navbar-dark sticky-top shadow-lg py-2 border-bottom border-warning border-2">
    <div class="container-fluid px-lg-5">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>/index.php">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Grand Royale Logo" height="42" class="rounded shadow-sm">
            <span class="fw-bold tracking-wide text-amber fs-5 d-none d-sm-inline"><?php echo sanitize($hotelName); ?></span>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-medium gap-lg-1">
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo BASE_URL; ?>/index.php"><i class="fas fa-home text-warning me-1"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo BASE_URL; ?>/rooms.php"><i class="fas fa-bed text-warning me-1"></i> Rooms</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo BASE_URL; ?>/amenities.php"><i class="fas fa-concierge-bell text-warning me-1"></i> Amenities</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo BASE_URL; ?>/gallery.php"><i class="fas fa-images text-warning me-1"></i> Gallery</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo BASE_URL; ?>/about.php"><i class="fas fa-info-circle text-warning me-1"></i> About</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="<?php echo BASE_URL; ?>/contact.php"><i class="fas fa-envelope text-warning me-1"></i> Contact</a></li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <button id="themeToggleBtn" class="btn btn-outline-warning btn-sm rounded-circle p-2" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                
                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-warning dropdown-toggle fw-semibold rounded-pill px-3 py-1 shadow-sm d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle fs-5"></i>
                            <span><?php echo sanitize($userName); ?></span>
                            <span class="badge bg-dark text-warning border border-warning text-uppercase px-2 ms-1" style="font-size: 0.65rem;"><?php echo sanitize($userRole); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                            <?php if ($userRole === 'admin'): ?>
                                <li><a class="dropdown-item fw-medium" href="<?php echo BASE_URL; ?>/admin/index.php"><i class="fas fa-tachometer-alt text-primary me-2"></i> Admin Dashboard</a></li>
                            <?php elseif ($userRole === 'receptionist'): ?>
                                <li><a class="dropdown-item fw-medium" href="<?php echo BASE_URL; ?>/staff/reception/index.php"><i class="fas fa-concierge-bell text-primary me-2"></i> Reception Desk</a></li>
                            <?php elseif ($userRole === 'housekeeping'): ?>
                                <li><a class="dropdown-item fw-medium" href="<?php echo BASE_URL; ?>/staff/housekeeping/index.php"><i class="fas fa-broom text-primary me-2"></i> Housekeeping Portal</a></li>
                            <?php elseif ($userRole === 'customer'): ?>
                                <li><a class="dropdown-item fw-medium" href="<?php echo BASE_URL; ?>/customer/index.php"><i class="fas fa-tachometer-alt text-primary me-2"></i> My Dashboard</a></li>
                                <li><a class="dropdown-item fw-medium" href="<?php echo BASE_URL; ?>/customer/my-bookings.php"><i class="fas fa-calendar-check text-primary me-2"></i> My Bookings</a></li>
                                <li><a class="dropdown-item fw-medium" href="<?php echo BASE_URL; ?>/customer/profile.php"><i class="fas fa-user-edit text-primary me-2"></i> My Profile</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger fw-semibold" href="<?php echo BASE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline-light rounded-pill px-3 py-1 fw-semibold"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-warning rounded-pill px-3 py-1 fw-semibold text-dark shadow-sm"><i class="fas fa-user-plus me-1"></i> Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
