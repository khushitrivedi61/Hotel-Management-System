<?php
$pageTitle = "Search Rooms - Grand Royale Hotel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../functions/room_functions.php';

requireRole('customer');

$checkIn = $_GET['check_in'] ?? date('Y-m-d');
$checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
$roomTypeId = $_GET['room_type'] ?? null;
$acStatus = $_GET['ac_status'] ?? null;

$rooms = getAvailableRooms($checkIn, $checkOut, $roomTypeId, null, $acStatus);
$roomTypes = getAllRoomTypes();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>
        
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-search text-warning me-2"></i>Search Room Availability</h5>
                <form action="search.php" method="GET">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Check-In</label>
                            <input type="date" name="check_in" class="form-control" value="<?php echo sanitize($checkIn); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Check-Out</label>
                            <input type="date" name="check_out" class="form-control" value="<?php echo sanitize($checkOut); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="room_type" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach ($roomTypes as $rt): ?>
                                    <option value="<?php echo $rt['id']; ?>" <?php echo $roomTypeId == $rt['id'] ? 'selected' : ''; ?>><?php echo sanitize($rt['type_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-grid align-items-end">
                            <button type="submit" class="btn btn-warning fw-bold text-dark"><i class="fas fa-search me-1"></i> Search</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row g-4">
                <?php if (empty($rooms)): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center py-4">No rooms available for your requested dates. Please choose alternate dates.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($rooms as $room): ?>
                        <div class="col-md-6">
                            <div class="card h-100 room-card border-0 shadow-sm">
                                <div class="room-img-container">
                                    <img src="<?php echo BASE_URL; ?>/assets/images/hero-bg.jpg" alt="Room">
                                    <span class="badge bg-dark text-warning position-absolute top-0 end-0 m-3 px-3 py-2 border border-warning">
                                        <?php echo formatCurrency($room['price_per_night']); ?> / Night
                                    </span>
                                </div>
                                <div class="card-body p-4 d-flex flex-column">
                                    <h5 class="fw-bold"><?php echo sanitize($room['type_name']); ?> (Room #<?php echo sanitize($room['room_number']); ?>)</h5>
                                    <p class="text-secondary small flex-grow-1"><?php echo sanitize($room['description'] ?: $room['type_desc']); ?></p>

                                    <div class="d-grid mt-3">
                                        <a href="book.php?room_id=<?php echo $room['id']; ?>&check_in=<?php echo urlencode($checkIn); ?>&check_out=<?php echo urlencode($checkOut); ?>" class="btn btn-warning fw-bold text-dark">
                                            Proceed to Book <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
