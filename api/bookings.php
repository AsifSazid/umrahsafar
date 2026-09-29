<?php
// FILE PATH: /api/bookings.php
// Syncs a custom_builds row into the bookings table — a denormalized
// copy taken at sync time (see data/table-sql/bookings.sql for why).
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';
require_once dirname(__DIR__) . '/includes/booking-pdf-builder.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') jsonResponse(false, 'Method not allowed.');

requireAdmin();
// Super-admin + admin only — kept as an array so a future role can be
// added/removed here without touching the rest of this action.
$syncAllowedRoles = ['super-admin', 'admin'];
if (!in_array($_SESSION['role_alias'] ?? '', $syncAllowedRoles, true)) {
    jsonResponse(false, "You don't have permission to sync bookings.");
}
if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');

$db = getDB();
$action = sanitize($_POST['action'] ?? '');

if ($action === 'sync') {
    $customBuildSysId = trim($_POST['custom_build_sys_id'] ?? '');
    if ($customBuildSysId === '') jsonResponse(false, 'Missing custom_build_sys_id.');

    $stmt = $db->prepare("SELECT * FROM custom_builds WHERE sys_id = ?");
    $stmt->execute([$customBuildSysId]);
    $cb = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cb) jsonResponse(false, 'Custom build not found.');

    // Already synced? The unique index would catch this too, but check
    // first so the message is clear rather than a generic DB error.
    $existing = $db->prepare("SELECT sys_id FROM bookings WHERE type = 'custom' AND ref_sys_id = ?");
    $existing->execute([$customBuildSysId]);
    if ($existing->fetch()) jsonResponse(false, 'This custom build has already been synced to a booking.');

    // No discount at sync time — final_prices starts identical to
    // total_prices; discounting a booking is a separate, later action.
    $finalPrices = $cb['total_prices'];

    $ids = generateSysIdAndUuid($db, 'bookings');
    $now = date('Y-m-d H:i:s');
    $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

    try {
        $db->beginTransaction();

        $db->prepare("INSERT INTO bookings
            (sys_id, uuid, type, ref_sys_id, customer_infos, persons, visa_infos, service_infos, choices,
             flight_infos, days, hotel_infos, meal_infos, transport_infos, moyallem_infos, ziarah_infos,
             total_prices, discount, final_prices, markups, data_json, pdf_path, status, metadata)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
           ->execute([
                $ids['sys_id'], $ids['uuid'], 'custom', $cb['sys_id'],
                $cb['customer_infos'], $cb['persons'], $cb['visa_infos'], $cb['service_infos'], $cb['choices'],
                $cb['flight_infos'], $cb['days'], $cb['hotel_infos'], $cb['meal_infos'], $cb['transport_infos'],
                $cb['moyallem_infos'], $cb['ziarah_infos'], $cb['total_prices'], null, $finalPrices,
                $cb['markups'], $cb['data_json'], null, 'Contacted', $metadata,
           ]);

        // The source record's own status moves to 'Contacted' too, so admin
        // screens browsing custom_builds directly reflect the sync as well.
        $db->prepare("UPDATE custom_builds SET status = 'Contacted' WHERE sys_id = ?")->execute([$cb['sys_id']]);

        $db->commit();

        // The booking gets its OWN pdf, separate from the custom_build's —
        // discounts only ever apply to bookings, never to custom_builds, so
        // the two PDFs can genuinely differ from here on.
        $bookingRow = $db->prepare("SELECT * FROM bookings WHERE sys_id = ?");
        $bookingRow->execute([$ids['sys_id']]);
        $pdfPath = buildBookingRecordPdf($bookingRow->fetch(PDO::FETCH_ASSOC));
        if ($pdfPath) {
            $db->prepare("UPDATE bookings SET pdf_path = ? WHERE sys_id = ?")->execute([$pdfPath, $ids['sys_id']]);
        }

        jsonResponse(true, 'Synced to booking.', ['sys_id' => $ids['sys_id']]);
    } catch (PDOException $e) {
        $db->rollBack();
        if (str_contains($e->getMessage(), 'Duplicate')) {
            jsonResponse(false, 'This custom build has already been synced to a booking.');
        }
        jsonResponse(false, 'Sync failed. Please try again.');
    }
}

if ($action === 'apply_discount') {
    $sysId = trim($_POST['sys_id'] ?? '');
    $discountType = trim($_POST['discount_type'] ?? '');
    $discountCurrency = strtolower(trim($_POST['discount_currency'] ?? ''));
    $discountValue = (float) ($_POST['discount_value'] ?? -1);
    if ($sysId === '' || !in_array($discountType, ['percentage', 'amount'], true)
        || !in_array($discountCurrency, ['sar', 'bdt', 'usd'], true) || $discountValue < 0) {
        jsonResponse(false, 'Invalid discount.');
    }

    $stmt = $db->prepare("SELECT total_prices FROM bookings WHERE sys_id = ?");
    $stmt->execute([$sysId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) jsonResponse(false, 'Booking not found.');

    $total = json_decode($row['total_prices'], true) ?: [];
    $amounts = [
        'sar' => (float) ($total['sar'][0] ?? 0),
        'bdt' => (float) ($total['bdt'][0] ?? 0),
        'usd' => (float) ($total['usd'][0] ?? 0),
    ];
    $rates = [
        'sar' => (float) ($total['sar'][1] ?? 1),
        'bdt' => (float) ($total['bdt'][1] ?? 1),
        'usd' => (float) ($total['usd'][1] ?? 1),
    ];

    $final = [];
    if ($discountType === 'percentage') {
        $factor = max(0, 1 - ($discountValue / 100));
        foreach ($amounts as $cur => $amt) $final[$cur] = $amt * $factor;
    } else {
        // Amount discount is entered in whichever currency the admin picked
        // — converted to SAR (this booking's frozen rates), then out to
        // every other currency, so the same real-world discount is
        // subtracted consistently no matter which currency it was typed in.
        $anchorRate = $rates[$discountCurrency] ?: 1;
        $discountInSar = $discountValue / $anchorRate;
        foreach ($amounts as $cur => $amt) {
            $discountInCur = $discountInSar * $rates[$cur];
            $final[$cur] = max(0, $amt - $discountInCur);
        }
    }

    $discountJson = json_encode(['type' => $discountType, 'value' => $discountValue, 'currency' => $discountCurrency]);
    $finalPrices = json_encode([
        'sar' => [round($final['sar'], 2), $rates['sar']],
        'bdt' => [round($final['bdt'], 2), $rates['bdt']],
        'usd' => [round($final['usd'], 2), $rates['usd']],
    ]);

    $db->prepare("UPDATE bookings SET discount = ?, final_prices = ? WHERE sys_id = ?")->execute([$discountJson, $finalPrices, $sysId]);

    // Regenerate this booking's PDF so the discount shows up in it.
    $fresh = $db->prepare("SELECT * FROM bookings WHERE sys_id = ?");
    $fresh->execute([$sysId]);
    $pdfPath = buildBookingRecordPdf($fresh->fetch(PDO::FETCH_ASSOC));
    if ($pdfPath) {
        $db->prepare("UPDATE bookings SET pdf_path = ? WHERE sys_id = ?")->execute([$pdfPath, $sysId]);
    }

    jsonResponse(true, 'Discount applied.');
}

if ($action === 'update_status') {
    $sysId = trim($_POST['sys_id'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $validStatuses = ['Contacted','Visa Processing','Visa Approved','Visa Rejected',
        'Hotel Processing','Hotel Confirmed','Flight Processing','Flight Confirmed',
        'Flight Date Changed','Payment Pending','Payment Received','Confirmed',
        'On Hold','Cancelled','Completed'];
    if ($sysId === '' || !in_array($status, $validStatuses, true)) jsonResponse(false, 'Invalid status.');

    $stmt = $db->prepare("SELECT type, ref_sys_id FROM bookings WHERE sys_id = ?");
    $stmt->execute([$sysId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$booking) jsonResponse(false, 'Booking not found.');

    try {
        $db->beginTransaction();
        $db->prepare("UPDATE bookings SET status = ? WHERE sys_id = ?")->execute([$status, $sysId]);

        // Keep the source record's status in lockstep with its booking —
        // same reasoning as the sync step: one lifecycle, viewed from
        // either screen.
        if ($booking['type'] === 'custom') {
            $db->prepare("UPDATE custom_builds SET status = ? WHERE sys_id = ?")->execute([$status, $booking['ref_sys_id']]);
        }

        $db->commit();
        jsonResponse(true, 'Status updated.');
    } catch (PDOException $e) {
        $db->rollBack();
        jsonResponse(false, 'Update failed. Please try again.');
    }
}

jsonResponse(false, 'Unknown action.');