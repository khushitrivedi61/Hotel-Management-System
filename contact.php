<?php
$pageTitle = "Contact Us - Grand Royale Hotel & Resort";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $success = "Thank you for contacting us! Our team will get back to you shortly.";
        } catch (PDOException $e) {
            $error = "Failed to send message: " . $e->getMessage();
        }
    }
}
?>

<div class="bg-dark text-light py-5 border-bottom border-warning border-2">
    <div class="container text-center">
        <h2 class="fw-bold brand-font text-amber">Contact Us</h2>
        <p class="text-secondary mb-0">We are here to assist you 24/7</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                <h4 class="fw-bold mb-4">Send Us a Message</h4>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo sanitize($success); ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo sanitize($error); ?></div>
                <?php endif; ?>

                <form action="contact.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Your Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject *</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message *</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning fw-bold text-dark w-100 py-2">
                        <i class="fas fa-paper-plane me-2"></i> Send Inquiry
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white h-100">
                <h4 class="fw-bold mb-4">Resort Information</h4>
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="bg-light text-warning p-3 rounded-circle fs-4"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Address</h6>
                        <p class="text-secondary small mb-0"><?php echo sanitize(getSetting('hotel_address', 'Beach Road, Luxury Enclave, Goa 403001')); ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="bg-light text-warning p-3 rounded-circle fs-4"><i class="fas fa-phone-alt"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Direct Helpline</h6>
                        <p class="text-secondary small mb-0"><?php echo sanitize(getSetting('hotel_phone', '+91 98765 43210')); ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-light text-warning p-3 rounded-circle fs-4"><i class="fas fa-envelope"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Email Inquiries</h6>
                        <p class="text-secondary small mb-0"><?php echo sanitize(getSetting('hotel_email', 'info@grandroyalehotel.com')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
