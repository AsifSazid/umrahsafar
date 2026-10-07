<?php
// FILE PATH: /api/booking-lookup.php
//
// Guest "Track Booking" lookup (pages/user-dashboard.php).
// Accepts either a booking sys_id (US9-26-BKS-xxxxx) or a custom build
// sys_id (US9-26-CBS-xxxxx). If a custom build has already been synced
// into `bookings`, the booking row wins — its status/final price is the
// one the customer should see.
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

header('Content-Type: application/json');

$ref = strtoupper(trim(sanitize($_GET['ref'] ?? '')));
if (!$ref) jsonResponse(false, 'Booking reference required.');

// {"sar":[amount,rate],"bdt":[amount,rate],...} → amount for one currency
function lookupPrice($json, string $cur): ?float {
    $p = is_array($json) ? $json : json_decode($json ?? '', true);
    return isset($p[$cur][0]) ? (float)$p[$cur][0] : null;
}

function lookupRow(array $r, string $kind): array {
    $persons  = json_decode($r['persons']       ?? '', true) ?: [];
    $service  = json_decode($r['service_infos'] ?? '', true) ?: [];
    $meta     = json_decode($r['metadata']      ?? '', true) ?: [];
    // bookings → price after discount; custom_builds → submitted total
    $prices   = $kind === 'booking' ? ($r['final_prices'] ?? $r['total_prices']) : $r['total_prices'];

    // Full-details page: custom-build-preview.php is the one that works on the
    // new schema. A synced booking of type=custom points back at its source build.
    $previewRef = $kind === 'booking'
        ? (($r['type'] ?? '') === 'custom' ? $r['ref_sys_id'] : null)
        : $r['sys_id'];

    return [
        'booking_ref'     => $r['sys_id'],
        'kind'            => $kind,
        'package_name'    => !empty($service['name']) ? $service['name'] . ' — Custom Package' : 'Custom Package',
        'adults'          => (int)($persons['adults']   ?? 0),
        'children'        => (int)($persons['children'] ?? 0),
        'infants'         => (int)($persons['infants']  ?? 0),
        'status'          => $r['status'],
        'total_price_bdt' => lookupPrice($prices, 'bdt'),
        'total_price_sar' => lookupPrice($prices, 'sar'),
        'created_at'      => $meta['created_at'] ?? null,
        'preview_url'     => $previewRef
            ? BASE_URL . '/pages/custom-build-preview.php?ref=' . rawurlencode($previewRef)
            : null,
    ];
}

try {
    $db = getDB();

    // 1) bookings — by its own sys_id, or by the source record's sys_id
    $stmt = $db->prepare("SELECT sys_id, type, ref_sys_id, persons, service_infos, status,
                                 total_prices, final_prices, metadata
                          FROM bookings
                          WHERE sys_id = :ref1 OR ref_sys_id = :ref2
                          LIMIT 1");
    $stmt->execute([':ref1' => $ref, ':ref2' => $ref]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        jsonResponse(true, 'Found.', ['booking' => lookupRow($row, 'booking')]);
    }

    // 2) custom_builds — not yet synced into a booking
    $stmt = $db->prepare("SELECT sys_id, persons, service_infos, status, total_prices, metadata
                          FROM custom_builds WHERE sys_id = :ref LIMIT 1");
    $stmt->execute([':ref' => $ref]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        jsonResponse(true, 'Found.', ['booking' => lookupRow($row, 'custom')]);
    }

    jsonResponse(false, 'Booking not found. Please check your reference number.');
} catch (Exception $e) {
    error_log('[booking-lookup] ' . $e->getMessage());
    jsonResponse(false, 'Could not look up booking.');
}