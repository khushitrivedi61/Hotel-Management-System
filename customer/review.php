<?php
$pageTitle = "Write Guest Review - Grand Royale Hotel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('customer');

$userId = $_SESSION['user_id'];
$bookingId = $_GET['booking_id'] ?? null;

// Get Customer ID
$custStmt = $pdo->prepare("SELECT id FROM customers WHERE user_id = ?");
$custStmt->execute([$userId]);
$customerId = $custStmt->fetchColumn();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = (int)$_POST['booking_id'];
    $rating = (int)$_POST['rating'];
    $title = trim($_POST['review_title']);
    $text = trim($_POST['review_text']);

    if (empty($title) || empty($text) || $rating < 1 || $rating > 5) {
        $error = "Please provide a valid rating, title, and review message.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (customer_id, booking_id, rating, review_title, review_text, status) VALUES (?, ?, ?, ?, ?, 'Approved')");
            $stmt->execute([$customerId, $bookingId, $rating, $title, $text]);
            setFlash('success', 'Thank you for your feedback! Your review has been submitted.');
            redirect('customer/my-bookings.php');
        } catch (PDOException $e) {
            $error = "Failed to submit review: " . $e->getMessage();
        }
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-warning p-4 text-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-star me-2"></i>Rate Your Stay</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo sanitize($error); ?></div>
                    <?php endif; ?>

                    <form action="review.php" method="POST">
                        <input type="hidden" name="booking_id" value="<?php echo sanitize($bookingId); ?>">
                        <div class="mb-3 text-center">
                            <label class="form-label fw-semibold d-block">Overall Rating (1 to 5 Stars)</label>
                            <select name="rating" class="form-select text-center fw-bold text-warning fs-5 mx-auto" style="max-width: 200px;">
                                <option value="5">★★★★★ (5 Stars)</option>
                                <option value="4">★★★★☆ (4 Stars)</option>
                                <option value="3">★★★☆☆ (3 Stars)</option>
                                <option value="2">★★☆☆☆ (2 Stars)</option>
                                <option value="1">★☆☆☆☆ (1 Star)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Review Title</label>
                            <input type="text" name="review_title" class="form-control" placeholder="e.g. Luxurious suite and stellar service!" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Detailed Review</label>
                            <textarea name="review_text" class="form-control" rows="4" placeholder="Tell us about your experience..." required></textarea>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-warning fw-bold text-dark">Submit Guest Review</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
