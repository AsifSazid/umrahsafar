<?php
// FILE PATH: /api/booking-lookup.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

header('Content-Type: application/json');

$ref = sanitize($_GET['ref'] ?? '');
if (!$ref) jsonResponse(false, 'Booking reference required.');

try {
    $db = getDB();
    // Search both bookings and custom_builds
    $stmt = $db->prepare("SELECT booking_ref, package_name, adults, children, status, total_price_bdt, total_price_sar, created_at FROM bookings WHERE booking_ref = :ref LIMIT 1");
    $stmt->execute([':ref' => $ref]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        // Try custom builds
        $stmt2 = $db->prepare("SELECT build_ref AS booking_ref, CONCAT(package_level, ' | ', flight_type) AS package_name, adults, children, status, total_price_bdt, total_price_sar, created_at FROM custom_builds WHERE build_ref = :ref LIMIT 1");
        $stmt2->execute([':ref' => $ref]);
        $booking = $stmt2->fetch(PDO::FETCH_ASSOC);
    }

    if (!$booking) jsonResponse(false, 'Booking not found. Please check your reference number.');

    jsonResponse(true, 'Found.', ['booking' => $booking]);
} catch (Exception $e) {
    jsonResponse(false, 'Could not look up booking.');
}
