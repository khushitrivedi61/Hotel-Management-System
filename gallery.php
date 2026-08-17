<?php
$pageTitle = "Photo Gallery - Grand Royale Hotel & Resort";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Actual downloaded images list from D:\hotel images
$galleryImages = [
    [
        'id' => 1,
        'title' => 'Grand Resort Exterior & Facade',
        'category' => 'Hotel Exterior',
        'category_slug' => 'exterior',
        'file' => 'hotel.jpeg',
        'description' => 'Majestic architectural exterior of Grand Royale Hotel & Resort at twilight.'
    ],
    [
        'id' => 2,
        'title' => 'Executive Luxury Suite',
        'category' => 'Luxury Room',
        'category_slug' => 'luxury',
        'file' => 'luxury_suit.jpeg',
        'description' => 'Opulent suite featuring ocean views, king bed, and ambient lounge lighting.'
    ],
    [
        'id' => 3,
        'title' => 'Presidential Villa Suite',
        'category' => 'Luxury Room',
        'category_slug' => 'luxury',
        'file' => 'presidential_villa.jpeg',
        'description' => 'Ultra-luxurious multi-room villa suite with private Jacuzzi and sun terrace.'
    ],
    [
        'id' => 4,
        'title' => 'Deluxe Double Bedroom (Capacity: 4 Guests)',
        'category' => 'Deluxe Room',
        'category_slug' => 'deluxe',
        'file' => 'deluxe_double.jpeg',
        'description' => 'Spacious Deluxe Double bedroom accommodating up to 4 guests with modern luxury furnishings.'
    ],
    [
        'id' => 5,
        'title' => 'Standard Classic Room',
        'category' => 'Standard Room',
        'category_slug' => 'standard',
        'file' => 'normal_room.jpeg',
        'description' => 'Cozy double bedroom equipped with essential luxury amenities and garden view.'
    ],
    [
        'id' => 6,
        'title' => 'Marble Luxury Bathroom & Jacuzzi',
        'category' => 'Bathroom',
        'category_slug' => 'bathroom',
        'file' => 'bathroom.jpeg',
        'description' => 'Italian marble bathroom featuring private Jacuzzi tub and rainfall shower.'
    ],
    [
        'id' => 7,
        'title' => 'Standard Guest Bathroom',
        'category' => 'Bathroom',
        'category_slug' => 'bathroom',
        'file' => 'normal_bathroom.jpeg',
        'description' => 'Immaculately clean guest bathroom with vanity mirror and organic spa toiletries.'
    ],
    [
        'id' => 8,
        'title' => 'Front Desk Reception Lobby',
        'category' => 'Reception',
        'category_slug' => 'reception',
        'file' => 'reception.jpeg',
        'description' => 'Warm and welcoming 24/7 reception lobby for seamless guest check-ins.'
    ]
];
?>

<div class="bg-dark text-light py-5 border-bottom border-warning border-2">
    <div class="container text-center">
        <h2 class="fw-bold brand-font text-amber">Resort Photo Gallery</h2>
        <p class="text-secondary mb-0">Immerse yourself in our serene architecture, luxury suites, and grand lobby</p>
    </div>
</div>

<div class="container py-5">
    <!-- Category Filter Tabs -->
    <div class="d-flex justify-content-center flex-wrap gap-2 mb-5">
        <button class="btn btn-warning fw-bold text-dark filter-btn active" data-filter="all">All Photos (8)</button>
        <button class="btn btn-outline-dark fw-bold filter-btn" data-filter="exterior">Hotel Exterior</button>
        <button class="btn btn-outline-dark fw-bold filter-btn" data-filter="luxury">Luxury Suite</button>
        <button class="btn btn-outline-dark fw-bold filter-btn" data-filter="deluxe">Deluxe Double</button>
        <button class="btn btn-outline-dark fw-bold filter-btn" data-filter="standard">Standard Room</button>
        <button class="btn btn-outline-dark fw-bold filter-btn" data-filter="bathroom">Bathroom</button>
        <button class="btn btn-outline-dark fw-bold filter-btn" data-filter="reception">Reception</button>
    </div>

    <!-- Responsive Gallery Grid -->
    <div class="row g-4">
        <?php foreach ($galleryImages as $img): ?>
            <div class="col-md-6 col-lg-4 gallery-item <?php echo $img['category_slug']; ?>">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden room-card h-100 position-relative">
                    <div class="room-img-container" style="height: 260px;">
                        <img src="<?php echo BASE_URL; ?>/assets/images/<?php echo $img['file']; ?>" alt="<?php echo sanitize($img['title']); ?>" class="img-fluid w-100 h-100 object-fit-cover">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-dark text-warning border border-warning px-3 py-2"><?php echo sanitize($img['category']); ?></span>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-white d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><?php echo sanitize($img['title']); ?></h5>
                            <p class="text-secondary small mb-3"><?php echo sanitize($img['description']); ?></p>
                        </div>
                        <button type="button" class="btn btn-outline-warning text-dark fw-bold btn-sm w-100 mt-2" data-bs-toggle="modal" data-bs-target="#lightboxModal_<?php echo $img['id']; ?>">
                            <i class="fas fa-search-plus me-1"></i> Fullscreen Preview
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lightbox Modal for Image -->
            <div class="modal fade" id="lightboxModal_<?php echo $img['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 bg-dark text-light rounded-4 overflow-hidden">
                        <div class="modal-header border-bottom border-warning">
                            <h5 class="modal-title text-amber fw-bold"><i class="fas fa-image me-2"></i><?php echo sanitize($img['title']); ?></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0 text-center bg-black">
                            <img src="<?php echo BASE_URL; ?>/assets/images/<?php echo $img['file']; ?>" class="img-fluid max-vh-75" alt="<?php echo sanitize($img['title']); ?>">
                        </div>
                        <div class="modal-footer border-top border-secondary bg-dark text-secondary">
                            <div class="me-auto text-start">
                                <span class="badge bg-warning text-dark me-2"><?php echo sanitize($img['category']); ?></span>
                                <small><?php echo sanitize($img['description']); ?></small>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const items = document.querySelectorAll('.gallery-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => {
                b.classList.remove('btn-warning', 'text-dark', 'active');
                b.classList.add('btn-outline-dark');
            });
            this.classList.remove('btn-outline-dark');
            this.classList.add('btn-warning', 'text-dark', 'active');

            const filter = this.getAttribute('data-filter');
            items.forEach(item => {
                if (filter === 'all' || item.classList.contains(filter)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
