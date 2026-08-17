<?php
$pageTitle = "Hotel Settings - Admin Panel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

requireRole('admin');

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $val) {
        if ($key !== 'submit') {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, trim($val), trim($val)]);
        }
    }
    logActivity('System Settings Updated', 'Admin modified global hotel configuration parameters.');
    setFlash('success', 'Hotel settings updated successfully!');
    redirect('admin/settings.php');
}
?>

<div class="container-fluid px-lg-5 py-4">
    <?php displayFlash(); ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark text-warning p-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-cog me-2"></i>Global Hotel Configuration & Policies</h5>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="settings.php" method="POST">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Hotel Name</label>
                                <input type="text" name="hotel_name" class="form-control" value="<?php echo sanitize(getSetting('hotel_name')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tagline</label>
                                <input type="text" name="hotel_tagline" class="form-control" value="<?php echo sanitize(getSetting('hotel_tagline')); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contact Email</label>
                                <input type="email" name="hotel_email" class="form-control" value="<?php echo sanitize(getSetting('hotel_email')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Helpline Phone</label>
                                <input type="text" name="hotel_phone" class="form-control" value="<?php echo sanitize(getSetting('hotel_phone')); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Physical Resort Address</label>
                                <textarea name="hotel_address" class="form-control" rows="2" required><?php echo sanitize(getSetting('hotel_address')); ?></textarea>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Taxation & Booking Policy</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">GSTIN Registration Number</label>
                                <input type="text" name="gst_number" class="form-control text-uppercase" value="<?php echo sanitize(getSetting('gst_number')); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">GST Rate (%)</label>
                                <input type="number" step="0.01" name="tax_percentage" class="form-control" value="<?php echo sanitize(getSetting('tax_percentage')); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Currency Symbol</label>
                                <input type="text" name="currency_symbol" class="form-control" value="<?php echo sanitize(getSetting('currency_symbol')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Standard Check-In Time</label>
                                <input type="time" name="checkin_time" class="form-control" value="<?php echo sanitize(getSetting('checkin_time')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Standard Check-Out Time</label>
                                <input type="time" name="checkout_time" class="form-control" value="<?php echo sanitize(getSetting('checkout_time')); ?>" required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="submit" class="btn btn-warning btn-lg fw-bold text-dark py-3 shadow">
                                <i class="fas fa-save me-2"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
