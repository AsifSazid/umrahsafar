<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

// CSRF
$token = $_POST['csrf_token'] ?? '';
if (!verifyCsrf($token)) {
    jsonResponse(false, 'Invalid security token.');
}

// Rate limit
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (!rateLimit("booking_{$ip}", 3, 3600)) {
    jsonResponse(false, 'Too many booking attempts. Please try again later.');
}

// Collect & sanitize
$customerName     = sanitize($_POST['customer_name']   ?? '');
$customerEmail    = sanitize($_POST['customer_email']  ?? '');
$customerPhone    = sanitize($_POST['customer_phone']  ?? '');
$packageName      = sanitize($_POST['package_name']    ?? '');
$departureDate    = sanitize($_POST['departure_date']  ?? '');
$returnDate       = sanitize($_POST['return_date']     ?? '');
$adults           = (int)($_POST['adults']             ?? 1);
$children         = (int)($_POST['children']           ?? 0);
$infants          = (int)($_POST['infants']            ?? 0);
$makkahNights     = (int)($_POST['makkah_nights']      ?? 0);
$madinahNights    = (int)($_POST['madinah_nights']     ?? 0);
$hotelCategory    = sanitize($_POST['hotel_category']  ?? '');
$transportType    = sanitize($_POST['transport_type']  ?? '');
$visaType         = sanitize($_POST['visa_type']       ?? '');
$totalPriceBdt    = (float)($_POST['total_price_bdt']  ?? 0);
$totalPriceUsd    = (float)($_POST['total_price_usd']  ?? 0);
$totalPriceSar    = (float)($_POST['total_price_sar']  ?? 0);
$notes            = sanitize($_POST['notes']           ?? '');

// Validate
$errors = [];
if (strlen($customerName) < 2)                        $errors[] = 'Full name is required.';
if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if (strlen($customerPhone) < 7)                       $errors[] = 'Phone number is required.';
if ($adults < 1)                                      $errors[] = 'At least 1 adult required.';

if ($errors) jsonResponse(false, implode(' ', $errors));

// Generate booking ref
$bookingRef = generateSysId('bookings');

// Save to DB
try {
    $db  = getDB();
    $sql = "INSERT INTO bookings
                (booking_ref, customer_name, customer_email, customer_phone,
                 package_name, departure_date, return_date,
                 adults, children, infants,
                 makkah_nights, madinah_nights, hotel_category,
                 transport_type, visa_type,
                 total_price_bdt, total_price_usd, total_price_sar,
                 status, notes, created_at)
            VALUES
                (:ref, :name, :email, :phone,
                 :pkg, :dep, :ret,
                 :adults, :children, :infants,
                 :makkah, :madinah, :hotel,
                 :transport, :visa,
                 :bdt, :usd, :sar,
                 'Submitted', :notes, NOW())";
    $db->prepare($sql)->execute([
        ':ref'       => $bookingRef,
        ':name'      => $customerName,
        ':email'     => $customerEmail,
        ':phone'     => $customerPhone,
        ':pkg'       => $packageName,
        ':dep'       => $departureDate ?: null,
        ':ret'       => $returnDate    ?: null,
        ':adults'    => $adults,
        ':children'  => $children,
        ':infants'   => $infants,
        ':makkah'    => $makkahNights,
        ':madinah'   => $madinahNights,
        ':hotel'     => $hotelCategory,
        ':transport' => $transportType,
        ':visa'      => $visaType,
        ':bdt'       => $totalPriceBdt,
        ':usd'       => $totalPriceUsd,
        ':sar'       => $totalPriceSar,
        ':notes'     => $notes
    ]);
} catch (Exception $e) {
    error_log('Booking DB error: ' . $e->getMessage());
    jsonResponse(false, 'Booking could not be saved. Please contact us directly.');
}

// Send confirmation email to customer
$customerEmailBody = "
Assalamu Alaikum {$customerName},

Your Umrah package enquiry has been received. Our team will contact you within 24 hours.

BOOKING REFERENCE: {$bookingRef}

Package:    {$packageName}
Travelers:  {$adults} Adult(s), {$children} Children, {$infants} Infant(s)
Duration:   Makkah {$makkahNights} nights + Madinah {$madinahNights} nights
Transport:  {$transportType}
Visa:       {$visaType}

PAYMENT INSTRUCTIONS
Please do not make any payment until confirmed by our team.
bKash: 01XXXXXXXXX (Personal)
Bank:  Dutch-Bangla Bank | TravHub Ltd | A/C: XXXXXXXXXXXX

For questions: Call or WhatsApp +880 1X XX-XXXXXX
Or email: info@travhub.com.bd

JazakAllahu Khayran,
TravHub Team
travhub.com.bd
";

$adminBody = "New booking: {$bookingRef}\nCustomer: {$customerName} ({$customerPhone})\nPackage: {$packageName}";

$headers = "From: TravHub <noreply@travhub.com.bd>\r\n";
@mail($customerEmail, "[TravHub] Booking Received — {$bookingRef}", $customerEmailBody, $headers);
@mail(ADMIN_EMAIL,    "[TravHub] New Booking: {$bookingRef}",        $adminBody,          $headers);

// WhatsApp message for admin
$waMsg = urlencode("New booking {$bookingRef}: {$customerName} ({$customerPhone}) — {$packageName}");
$waUrl = "https://wa.me/" . ltrim(WHATSAPP_NUMBER, '+') . "?text={$waMsg}";

jsonResponse(true, 'Booking submitted successfully.', [
    'booking_ref'       => $bookingRef,
    'redirect'          => BASE_URL . "/pages/booking-confirmation.php?ref={$bookingRef}",
    'whatsapp_admin_url'=> $waUrl
]);