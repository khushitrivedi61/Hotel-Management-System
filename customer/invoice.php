<?php
$pageTitle = "Tax Invoice - Grand Royale Hotel";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../functions/invoice_functions.php';

$invoiceId = $_GET['id'] ?? null;
if (!$invoiceId) {
    die("Invalid Invoice Request");
}

$inv = getInvoiceDetails($invoiceId);
if (!$inv) {
    die("Invoice not found.");
}

$hotelName = getSetting('hotel_name', 'Grand Royale Hotel & Resort');
$hotelAddress = getSetting('hotel_address', 'Beach Road, Luxury Enclave, Goa 403001');
$hotelPhone = getSetting('hotel_phone', '+91 98765 43210');
$hotelEmail = getSetting('hotel_email', 'info@grandroyalehotel.com');
$gstNumber = getSetting('gst_number', '22AAAAA0000A1Z5');

$nights = calculateNights($inv['check_in_date'], $inv['check_out_date']);
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 btn-print-hide">
        <a href="my-bookings.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left me-1"></i> Back to Bookings</a>
        <button onclick="window.print()" class="btn btn-warning fw-bold text-dark"><i class="fas fa-print me-1"></i> Print / Save as PDF</button>
    </div>

    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
        <!-- Invoice Header -->
        <div class="d-flex justify-content-between align-items-start invoice-header pb-4 mb-4">
            <div>
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo" height="55" class="rounded mb-2">
                <h4 class="fw-bold brand-font text-dark mb-1"><?php echo sanitize($hotelName); ?></h4>
                <div class="text-secondary small"><?php echo sanitize($hotelAddress); ?></div>
                <div class="text-secondary small">Phone: <?php echo sanitize($hotelPhone); ?> | Email: <?php echo sanitize($hotelEmail); ?></div>
                <div class="fw-bold text-dark small mt-1">GSTIN: <?php echo sanitize($gstNumber); ?></div>
            </div>
            <div class="text-end">
                <h3 class="fw-bold text-warning mb-1">TAX INVOICE</h3>
                <div class="fw-bold text-dark">Invoice #: <?php echo sanitize($inv['invoice_number']); ?></div>
                <div class="text-secondary small">Date: <?php echo formatDate($inv['issue_date']); ?></div>
                <div class="badge bg-success text-uppercase mt-2 px-3 py-2">STATUS: PAID</div>
            </div>
        </div>

        <!-- Customer & Booking Info -->
        <div class="row g-4 mb-4">
            <div class="col-6">
                <h6 class="fw-bold text-uppercase text-secondary small">Billed To:</h6>
                <h5 class="fw-bold text-dark mb-1"><?php echo sanitize($inv['customer_name']); ?></h5>
                <div class="text-secondary small"><?php echo sanitize($inv['address'] ?: 'Customer Address N/A'); ?></div>
                <div class="text-secondary small">Phone: <?php echo sanitize($inv['customer_phone']); ?></div>
                <div class="text-secondary small">ID: <?php echo sanitize($inv['id_type']); ?> - <?php echo sanitize($inv['id_number']); ?></div>
            </div>
            <div class="col-6 text-end">
                <h6 class="fw-bold text-uppercase text-secondary small">Reservation Details:</h6>
                <div class="text-secondary small">Booking Reference: <strong class="text-dark"><?php echo sanitize($inv['booking_code']); ?></strong></div>
                <div class="text-secondary small">Room Reserved: <strong class="text-dark">Room #<?php echo sanitize($inv['room_number']); ?> (<?php echo sanitize($inv['room_type']); ?>)</strong></div>
                <div class="text-secondary small">Check-In: <strong class="text-dark"><?php echo formatDate($inv['check_in_date']); ?></strong></div>
                <div class="text-secondary small">Check-Out: <strong class="text-dark"><?php echo formatDate($inv['check_out_date']); ?></strong></div>
                <div class="text-secondary small">Duration: <strong class="text-dark"><?php echo $nights; ?> Night(s)</strong></div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Nights / Qty</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="fw-bold"><?php echo sanitize($inv['room_type']); ?> Accommodation</div>
                            <small class="text-muted">Room #<?php echo sanitize($inv['room_number']); ?></small>
                        </td>
                        <td class="text-center"><?php echo $nights; ?></td>
                        <td class="text-end"><?php echo formatCurrency($inv['room_charges'] / $nights); ?></td>
                        <td class="text-end fw-bold"><?php echo formatCurrency($inv['room_charges']); ?></td>
                    </tr>
                    <?php if ($inv['discount_amount'] > 0): ?>
                        <tr>
                            <td colspan="3" class="text-end text-success fw-bold">Coupon Discount Applied</td>
                            <td class="text-end text-success fw-bold">- <?php echo formatCurrency($inv['discount_amount']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="3" class="text-end text-secondary">GST (18%)</td>
                        <td class="text-end text-secondary"><?php echo formatCurrency($inv['gst_amount']); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-dark text-warning fs-5">
                        <td colspan="3" class="text-end fw-bold">Grand Total Paid</td>
                        <td class="text-end fw-bold"><?php echo formatCurrency($inv['grand_total']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Payment Info Footer -->
        <div class="row align-items-center pt-3 border-top">
            <div class="col-md-6">
                <div class="small text-secondary">Payment Method: <strong class="text-dark"><?php echo sanitize($inv['payment_method'] ?: 'UPI'); ?></strong></div>
                <div class="small text-secondary">Transaction Ref: <code><?php echo sanitize($inv['transaction_ref']); ?></code></div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="fw-bold text-dark">Grand Royale Hotel Management</div>
                <small class="text-muted">Computer-generated Tax Invoice</small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
