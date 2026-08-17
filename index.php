<?php
$pageTitle = "Grand Royale Hotel & Resort - Five Star Luxury Hotel";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/functions/room_functions.php';

$hotelName = getSetting('hotel_name', 'Grand Royale Hotel & Resort');
$hotelTagline = getSetting('hotel_tagline', 'Where Luxury Meets Exceptional Elegance');

// Fetch 3 Featured Rooms dynamically from DB
try {
    $stmt = $pdo->query("
        SELECT r.*, rt.type_name, rt.capacity, rt.ac_status, rt.cover_image, rt.description as type_desc 
        FROM rooms r 
        JOIN room_types rt ON r.room_type_id = rt.id 
        WHERE r.status != 'Maintenance' 
        ORDER BY r.price_per_night DESC LIMIT 3
    ");
    $featuredRooms = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredRooms = [];
}

// Fetch Room Types for search dropdown
$roomTypes = getAllRoomTypes();

// Fetch Testimonials / Reviews dynamically from DB
try {
    $revStmt = $pdo->query("
        SELECT r.*, u.name as customer_name 
        FROM reviews r 
        JOIN customers c ON r.customer_id = c.id 
        JOIN users u ON c.user_id = u.id 
        WHERE r.status = 'Approved' 
        ORDER BY r.id DESC LIMIT 4
    ");
    $reviews = $revStmt->fetchAll();
} catch (PDOException $e) {
    $reviews = [];
}

if (empty($reviews)) {
    $reviews = [
        ['customer_name' => 'Alexander Wright', 'rating' => 5, 'review_title' => 'Unmatched Luxury & Hospitality', 'review_text' => 'Grand Royale exceeded every expectation! The presidential suite, private Jacuzzi, and beachfront views made our stay unforgettable.'],
        ['customer_name' => 'Dr. Meera Sharma', 'rating' => 5, 'review_title' => 'Exquisite Service & Dining', 'review_text' => 'From the seamless front desk check-in to gourmet breakfast dining, the staff made us feel like royalty throughout our 4-day stay.'],
        ['customer_name' => 'Robert Davies', 'rating' => 5, 'review_title' => 'Serene Spa & Resort Facilities', 'review_text' => 'The infinity swimming pool and aromatherapy spa sessions were pure relaxation. Highly recommended for luxury travelers.'],
        ['customer_name' => 'Priya Nair', 'rating' => 5, 'review_title' => 'Flawless Room Service & Cleanliness', 'review_text' => 'Immaculately clean rooms, plush bedding, high-speed WiFi, and attentive housekeeping staff. Will definitely return!']
    ];
}
?>

<!-- 1. Hero Section -->
<section class="hero-banner text-light text-center py-5 position-relative">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <span class="badge bg-warning text-dark text-uppercase px-3 py-2 fw-bold tracking-wider mb-3 shadow">
                    <i class="fas fa-crown me-1"></i> Welcome to Absolute Luxury
                </span>
                <h1 class="display-3 fw-bold mb-3 brand-font text-white text-shadow-sm"><?php echo sanitize($hotelName); ?></h1>
                <p class="lead text-light opacity-90 mb-4 fs-4"><?php echo sanitize($hotelTagline); ?></p>
                
                <div class="d-flex justify-content-center gap-3 mb-5">
                    <a href="#booking-search" class="btn btn-warning btn-lg fw-bold text-dark px-4 py-3 shadow">
                        <i class="fas fa-calendar-alt me-2"></i> Book Now
                    </a>
                    <a href="rooms.php" class="btn btn-outline-light btn-lg fw-bold px-4 py-3 shadow">
                        <i class="fas fa-bed me-2"></i> Explore Rooms
                    </a>
                </div>
                
                <!-- Quick Room Search Engine Box -->
                <div id="booking-search" class="search-box-card p-4 shadow-lg border-0 text-start text-dark">
                    <form action="rooms.php" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-semibold text-dark mb-1"><i class="fas fa-calendar-alt text-warning me-1"></i> Check-In Date</label>
                                <input type="date" name="check_in" class="form-control" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-semibold text-dark mb-1"><i class="fas fa-calendar-check text-warning me-1"></i> Check-Out Date</label>
                                <input type="date" name="check_out" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-semibold text-dark mb-1"><i class="fas fa-bed text-warning me-1"></i> Room Category</label>
                                <select name="room_type" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($roomTypes as $rt): ?>
                                        <option value="<?php echo $rt['id']; ?>"><?php echo sanitize($rt['type_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 d-grid">
                                <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm">
                                    <i class="fas fa-search me-2"></i> Search Available Rooms
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Welcome Section -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="position-relative">
                    <img src="<?php echo BASE_URL; ?>/assets/images/hotel.jpeg" class="img-fluid rounded-4 shadow-lg border border-4 border-white" alt="Grand Royale Exterior">
                    <div class="position-absolute bottom-0 start-0 bg-dark text-warning p-3 rounded-3 shadow border border-warning m-3 d-none d-sm-block">
                        <i class="fas fa-award fs-2 me-2 align-middle"></i>
                        <span class="fw-bold fs-5 align-middle">5-Star Luxury Certified</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <h6 class="text-warning text-uppercase fw-bold tracking-wider mb-2">Welcome to Sanctuary of Luxury</h6>
                <h2 class="fw-bold brand-font display-6 mb-3">About Grand Royale Hotel & Resort</h2>
                <div class="bg-amber mb-4" style="height: 3px; width: 70px;"></div>
                <p class="text-secondary fs-5 mb-3">
                    Situated on prime oceanfront property, Grand Royale Hotel & Resort sets the gold standard for five-star luxury accommodations, fine dining, and bespoke hospitality.
                </p>
                <p class="text-secondary mb-4">
                    Whether you are seeking a serene beachfront vacation in our Presidential Villa, a romantic suite getaway, or an executive conference venue, our dedicated staff provides round-the-clock personalized service to ensure your stay is flawless.
                </p>
                <div class="d-flex gap-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light text-warning p-3 rounded-circle fs-4"><i class="fas fa-concierge-bell"></i></div>
                        <div>
                            <h6 class="fw-bold mb-0">24/7 Butler Service</h6>
                            <small class="text-secondary">Dedicated Room Concierge</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light text-warning p-3 rounded-circle fs-4"><i class="fas fa-shield-alt"></i></div>
                        <div>
                            <h6 class="fw-bold mb-0">Private & Secure</h6>
                            <small class="text-secondary">Keycard Suite Access</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Why Choose Us Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-warning text-uppercase fw-bold tracking-wider mb-2">Exclusive Amenities</h6>
            <h2 class="fw-bold brand-font display-6">Why Choose Grand Royale</h2>
            <div class="bg-amber mx-auto mt-2" style="height: 3px; width: 60px;"></div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white stat-card">
                    <div class="bg-light text-warning rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 fs-2" style="width: 75px; height: 75px;">
                        <i class="fas fa-bed"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Luxury Rooms</h5>
                    <p class="text-secondary small mb-0">Plush king-size bedding, ergonomic lounge furniture, private balconies, and smart ambient lighting.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white stat-card">
                    <div class="bg-light text-warning rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 fs-2" style="width: 75px; height: 75px;">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Free Ultra High-Speed WiFi</h5>
                    <p class="text-secondary small mb-0">Complimentary 1Gbps fiber broadband connectivity throughout the resort and suites.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white stat-card">
                    <div class="bg-light text-warning rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 fs-2" style="width: 75px; height: 75px;">
                        <i class="fas fa-swimming-pool"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Infinity Swimming Pool</h5>
                    <p class="text-secondary small mb-0">Temperature-controlled oceanfront infinity pool with poolside sunbeds and cocktail lounge.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white stat-card">
                    <div class="bg-light text-warning rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 fs-2" style="width: 75px; height: 75px;">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Gourmet Restaurant</h5>
                    <p class="text-secondary small mb-0">Multi-cuisine dining prepared by world-renowned chefs, featuring fresh seafood and fine wines.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white stat-card">
                    <div class="bg-light text-warning rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 fs-2" style="width: 75px; height: 75px;">
                        <i class="fas fa-spa"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Aromatherapy Spa</h5>
                    <p class="text-secondary small mb-0">Rejuvenating massage therapy, hydrotherapy Jacuzzi baths, and natural wellness treatments.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white stat-card">
                    <div class="bg-light text-warning rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 fs-2" style="width: 75px; height: 75px;">
                        <i class="fas fa-plane-arrival"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Airport Chauffeur Pickup</h5>
                    <p class="text-secondary small mb-0">Luxury private sedan transfer directly to and from the airport for hassle-free travel.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4. Featured Rooms Section (Dynamic from Database) -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-warning text-uppercase fw-bold tracking-wider mb-2">Accommodations</h6>
            <h2 class="fw-bold brand-font display-6">Featured Luxury Rooms</h2>
            <div class="bg-amber mx-auto mt-2" style="height: 3px; width: 60px;"></div>
        </div>

        <div class="row g-4">
            <?php if (!empty($featuredRooms)): ?>
                <?php foreach ($featuredRooms as $fRoom): 
                    $imgFile = getRoomImageFileName($fRoom['type_name']);
                    $displayCap = (strtolower($fRoom['type_name']) === 'deluxe double') ? 4 : $fRoom['capacity'];
                ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 room-card border-0 shadow-sm">
                            <div class="room-img-container">
                                <img src="<?php echo BASE_URL; ?>/assets/images/<?php echo $imgFile; ?>" alt="<?php echo sanitize($fRoom['type_name']); ?>">
                                <span class="badge bg-dark text-warning position-absolute top-0 end-0 m-3 px-3 py-2 border border-warning shadow">
                                    <?php echo formatCurrency($fRoom['price_per_night']); ?> / Night
                                </span>
                                <span class="badge bg-primary position-absolute top-0 start-0 m-3 px-2 py-1">
                                    Room #<?php echo sanitize($fRoom['room_number']); ?>
                                </span>
                            </div>
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="fw-bold mb-2"><?php echo sanitize($fRoom['type_name']); ?></h5>
                                <p class="text-secondary small flex-grow-1"><?php echo sanitize($fRoom['type_desc'] ?: $fRoom['description']); ?></p>

                                <div class="d-flex justify-content-between align-items-center text-secondary small border-top pt-3 mt-2">
                                    <span><i class="fas fa-user-friends text-warning me-1"></i> Max <?php echo $displayCap; ?> Guests</span>
                                    <span><i class="fas fa-snowflake text-warning me-1"></i> <?php echo $fRoom['ac_status']; ?></span>
                                </div>

                                <div class="d-grid mt-3">
                                    <a href="rooms.php?room_type=<?php echo $fRoom['room_type_id']; ?>" class="btn btn-warning fw-bold text-dark">
                                        View Details & Book <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback Room Display -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 room-card border-0 shadow-sm">
                        <div class="room-img-container">
                            <img src="<?php echo BASE_URL; ?>/assets/images/luxury_suit.jpeg" alt="Executive Suite">
                            <span class="badge bg-dark text-warning position-absolute top-0 end-0 m-3 px-3 py-2 border border-warning">₹ 5,500.00 / Night</span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="fw-bold">Executive Suite</h5>
                            <p class="text-secondary small flex-grow-1">Spacious luxury suite featuring plush king bedding, private ocean balcony, and Jacuzzi access.</p>
                            <div class="d-grid mt-3"><a href="rooms.php" class="btn btn-warning fw-bold text-dark">View Details</a></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="rooms.php" class="btn btn-outline-dark btn-lg fw-bold px-5 py-3">View All Rooms & Availability <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>

<!-- 5. Hotel Statistics Section (Counters) -->
<section class="py-5 bg-dark text-light border-top border-bottom border-warning border-2">
    <div class="container py-3">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="fas fa-door-open fs-1 text-warning mb-2"></i>
                    <h2 class="fw-bold brand-font display-5 text-amber mb-0 counter-val">50+</h2>
                    <p class="text-secondary mb-0 fw-medium">Luxury Rooms & Suites</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="fas fa-users fs-1 text-warning mb-2"></i>
                    <h2 class="fw-bold brand-font display-5 text-amber mb-0 counter-val">15,000+</h2>
                    <p class="text-secondary mb-0 fw-medium">Satisfied Guests</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="fas fa-user-shield fs-1 text-warning mb-2"></i>
                    <h2 class="fw-bold brand-font display-5 text-amber mb-0 counter-val">45+</h2>
                    <p class="text-secondary mb-0 fw-medium">Dedicated Staff Members</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="fas fa-award fs-1 text-warning mb-2"></i>
                    <h2 class="fw-bold brand-font display-5 text-amber mb-0 counter-val">12+</h2>
                    <p class="text-secondary mb-0 fw-medium">Years of Excellence</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-warning text-uppercase fw-bold tracking-wider mb-2">Guest Feedback</h6>
            <h2 class="fw-bold brand-font display-6">What Our Guests Say</h2>
            <div class="bg-amber mx-auto mt-2" style="height: 3px; width: 60px;"></div>
        </div>

        <div class="row g-4">
            <?php foreach ($reviews as $rev): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white stat-card d-flex flex-column">
                        <div class="text-warning mb-3">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="<?php echo $i <= ($rev['rating'] ?? 5) ? 'fas' : 'far'; ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <h6 class="fw-bold mb-2"><?php echo sanitize($rev['review_title']); ?></h6>
                        <p class="text-secondary small flex-grow-1">"<?php echo sanitize($rev['review_text']); ?>"</p>
                        <div class="d-flex align-items-center gap-2 border-top pt-3 mt-3">
                            <div class="bg-dark text-warning rounded-circle p-2 fs-6 fw-bold" style="width:35px; height:35px; text-align:center;">
                                <?php echo strtoupper(substr($rev['customer_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="fw-bold small text-dark"><?php echo sanitize($rev['customer_name']); ?></div>
                                <small class="text-success fw-semibold" style="font-size:0.75rem;"><i class="fas fa-check-circle"></i> Verified Guest</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 7. Call to Action Section -->
<section class="py-5 bg-dark text-light text-center border-top border-warning border-3">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <i class="fas fa-crown text-warning fs-1 mb-3"></i>
                <h2 class="display-5 fw-bold brand-font text-white mb-3">Book Your Luxury Stay Today</h2>
                <p class="lead text-secondary mb-4">
                    Experience world-class hospitality, decadent suites, and serene beach views. Reserve your room now with instant confirmation.
                </p>
                <a href="rooms.php" class="btn btn-warning btn-lg fw-bold text-dark px-5 py-3 shadow">
                    <i class="fas fa-calendar-check me-2"></i> Book Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 8. Footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
