<?php
$pageTitle = "Rooms & Luxury Suites - Grand Royale Hotel & Resort";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/functions/room_functions.php';

$checkIn = $_GET['check_in'] ?? date('Y-m-d');
$checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
$roomTypeId = $_GET['room_type'] ?? null;
$capacity = $_GET['capacity'] ?? null;
$acStatus = $_GET['ac_status'] ?? null;

// Fetch Available Rooms based on filter parameters
try {
    $rooms = getAvailableRooms($checkIn, $checkOut, $roomTypeId, $capacity, $acStatus);
} catch (PDOException $e) {
    $rooms = [];
}

// Fallback: If no available room records match filter, query all rooms
if (empty($rooms) && empty($_GET)) {
    try {
        $stmt = $pdo->query("
            SELECT r.*, rt.type_name, rt.capacity, rt.ac_status, rt.cover_image, rt.description as type_desc 
            FROM rooms r 
            JOIN room_types rt ON r.room_type_id = rt.id 
            ORDER BY r.price_per_night ASC
        ");
        $rooms = $stmt->fetchAll();
    } catch (PDOException $e) {
        $rooms = [];
    }
}

$roomTypes = getAllRoomTypes();
?>

<div class="bg-dark text-light py-5 border-bottom border-warning border-2">
    <div class="container text-center">
        <h2 class="fw-bold brand-font text-amber">Our Luxury Rooms & Suites</h2>
        <p class="text-secondary mb-0">Explore our available inventory, suite amenities, and reserve your stay</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar Filter Engine -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-3"><i class="fas fa-filter text-warning me-2"></i>Filter Inventory</h5>
                <form action="rooms.php" method="GET">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Check-In Date</label>
                        <input type="date" name="check_in" class="form-control form-control-sm" value="<?php echo sanitize($checkIn); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Check-Out Date</label>
                        <input type="date" name="check_out" class="form-control form-control-sm" value="<?php echo sanitize($checkOut); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category</label>
                        <select name="room_type" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php foreach ($roomTypes as $rt): ?>
                                <option value="<?php echo $rt['id']; ?>" <?php echo $roomTypeId == $rt['id'] ? 'selected' : ''; ?>><?php echo sanitize($rt['type_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">AC Type</label>
                        <select name="ac_status" class="form-select form-select-sm">
                            <option value="">Any Type</option>
                            <option value="AC" <?php echo $acStatus === 'AC' ? 'selected' : ''; ?>>Air Conditioned (AC)</option>
                            <option value="Non AC" <?php echo $acStatus === 'Non AC' ? 'selected' : ''; ?>>Non AC</option>
                        </select>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-warning btn-sm fw-bold text-dark"><i class="fas fa-search me-1"></i> Apply Filters</button>
                        <a href="rooms.php" class="btn btn-outline-secondary btn-sm">Reset All</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Room Listing Grid -->
        <div class="col-lg-9">
            <?php if (empty($rooms)): ?>
                <div class="card border-0 shadow-sm p-5 text-center rounded-4 bg-white">
                    <i class="fas fa-bed text-muted fs-1 mb-3"></i>
                    <h5 class="fw-bold text-dark">No Rooms Found</h5>
                    <p class="text-secondary mb-3">We could not find any available rooms matching your criteria for the selected dates.</p>
                    <div><a href="rooms.php" class="btn btn-warning fw-bold text-dark">View All Rooms</a></div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($rooms as $idx => $room): 
                        $amenities = getAmenitiesByRoomType($room['room_type_id']);
                        $imgFile = getRoomImageFileName($room['type_name']);
                        // Override capacity for Deluxe Double if needed
                        $displayCapacity = (strtolower($room['type_name']) === 'deluxe double') ? 4 : $room['capacity'];
                    ?>
                        <div class="col-md-6">
                            <div class="card h-100 room-card border-0 shadow-sm bg-white">
                                <div class="room-img-container">
                                    <img src="<?php echo BASE_URL; ?>/assets/images/<?php echo $imgFile; ?>" alt="Room #<?php echo sanitize($room['room_number']); ?>">
                                    <span class="badge bg-dark text-warning position-absolute top-0 end-0 m-3 px-3 py-2 border border-warning shadow">
                                        <?php echo formatCurrency($room['price_per_night']); ?> / Night
                                    </span>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-3 px-2 py-1">
                                        Room #<?php echo sanitize($room['room_number']); ?>
                                    </span>
                                </div>
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="fw-bold text-dark mb-0"><?php echo sanitize($room['type_name']); ?></h5>
                                        <span class="badge badge-status-<?php echo strtolower($room['status']); ?>">
                                            <?php echo sanitize($room['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <p class="text-secondary small flex-grow-1 mb-3"><?php echo sanitize($room['description'] ?: $room['type_desc']); ?></p>

                                    <!-- Amenities Badges -->
                                    <?php if (!empty($amenities)): ?>
                                        <div class="mb-3">
                                            <small class="fw-semibold text-dark d-block mb-1">Included Amenities:</small>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach (array_slice($amenities, 0, 4) as $am): ?>
                                                    <span class="badge bg-light text-dark border"><i class="fas <?php echo sanitize($am['icon_class']); ?> text-warning me-1"></i> <?php echo sanitize($am['name']); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-between align-items-center text-secondary small border-top pt-3 mt-auto">
                                        <span><i class="fas fa-layer-group text-warning me-1"></i> Floor <?php echo $room['floor']; ?></span>
                                        <span><i class="fas fa-user-friends text-warning me-1"></i> Max <?php echo $displayCapacity; ?> Guests</span>
                                        <span><i class="fas fa-snowflake text-warning me-1"></i> <?php echo $room['ac_status']; ?></span>
                                    </div>

                                    <div class="d-grid mt-3">
                                        <?php if ($room['status'] === 'Available' || $room['status'] === 'Reserved'): ?>
                                            <a href="<?php echo isLoggedIn() ? 'customer/book.php' : 'login.php'; ?>?room_id=<?php echo $room['id']; ?>&check_in=<?php echo urlencode($checkIn); ?>&check_out=<?php echo urlencode($checkOut); ?>" class="btn btn-warning fw-bold text-dark">
                                                <i class="fas fa-calendar-plus me-1"></i> Book Now
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary fw-bold" disabled>
                                                Currently <?php echo sanitize($room['status']); ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
