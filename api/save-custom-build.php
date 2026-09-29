<?php
// FILE PATH: /api/save-custom-build.php
// Receives the mini-builder's (and pages/package-builder.php's) entire
// `state.formData` as one JSON blob (`build_data_json`) and derives every
// structured custom_builds column from it. This is the single source of
// truth — no other individual POST fields are read for the structured
// columns, so the JSON and the DB row can never drift out of sync with
// each other.
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';
require_once dirname(__DIR__) . '/includes/pdf-builder.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Method not allowed.');
if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid security token.');

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (!rateLimit("custom_build_{$ip}", 10, 3600))
    jsonResponse(false, 'Too many submissions. Please try again later.');

$raw = $_POST['build_data_json'] ?? '';
$f = json_decode($raw, true);
if (!is_array($f)) jsonResponse(false, 'Invalid submission data.');

$db = getDB();

// ── Pull customer + traveler info straight out of the submitted state ──
$name     = sanitize($f['travelers']['name']  ?? '');
$email    = sanitize($f['travelers']['email'] ?? '');
$phone    = sanitize($f['travelers']['phone'] ?? '');
$adults   = (int) ($f['travelers']['no_of_pax']['adult'] ?? 1);
$children = (int) ($f['travelers']['no_of_pax']['child'] ?? 0);
$infants  = (int) ($f['travelers']['no_of_pax']['infant'] ?? 0);

// Phone/email: at least ONE of the two is required (not both) — Confirm &
// Preview only needs a way to reach the customer; either channel works.
if ($adults < 1 || !$name) jsonResponse(false, 'Name and at least 1 adult are required.');
if (!$phone && !$email) jsonResponse(false, 'Please provide at least a phone number or an email address.');

$rates = getExchangeRates(); // ['SAR'=>1, 'USD'=>..., 'BDT'=>..., 'last_updated'=>...]

// Converts one SAR amount into {"sar":[amount,rate],"bdt":[amount,rate],"usd":[amount,rate]},
// freezing today's rate alongside the converted amount for historical accuracy.
function moneyBreakdown(float $sar, array $rates): array {
    return [
        'sar' => [round($sar, 2), 1],
        'bdt' => [round($sar * ($rates['BDT'] ?? 32.5), 2), $rates['BDT'] ?? 32.5],
        'usd' => [round($sar * ($rates['USD'] ?? 0.2664), 2), $rates['USD'] ?? 0.2664],
    ];
}

$customerInfos = json_encode(['name' => $name, 'email' => $email, 'phone' => $phone], JSON_UNESCAPED_UNICODE);
$persons       = json_encode(['adults' => $adults, 'children' => $children, 'infants' => $infants]);

// ── visa_infos ── (per-traveler — one visa per person, never shared)
$visaInfos = null;
if (!empty($f['choices']['visa']) && !empty($f['visa'])) {
    $visaTotal = (float) ($f['visa']['price'] ?? 0) * max(1, $adults + $children + $infants);
    $visaInfos = json_encode([
        'required'          => 1,
        'visa_type_sys_ids' => array_values(array_filter([$f['visa']['visa_type_sys_id'] ?? null])),
        'total_prices'      => moneyBreakdown($visaTotal, $rates),
    ], JSON_UNESCAPED_UNICODE);
}

// ── service_infos ── (per-traveler — the package tier is paid by each traveler)
// serviceLevel currently holds a NAME (Economy/Premium/Luxury), not a
// sys_id — resolved here against service_levels for both the sys_id
// reference and its price.
$serviceInfos = null;
if (!empty($f['serviceLevel'])) {
    $lvl = $db->prepare("SELECT sys_id, name, price FROM service_levels WHERE name = ?");
    $lvl->execute([$f['serviceLevel']]);
    $lvlRow = $lvl->fetch(PDO::FETCH_ASSOC);
    if ($lvlRow) {
        $serviceTotal = (float) $lvlRow['price'] * $adults;
        $serviceInfos = json_encode([
            'sys_id'       => $lvlRow['sys_id'],
            'name'         => $lvlRow['name'],
            'price'        => (float) $lvlRow['price'],
            'total_prices' => moneyBreakdown($serviceTotal, $rates),
        ], JSON_UNESCAPED_UNICODE);
    }
}
$choices = is_array($f['choices'] ?? null)
    ? implode(',', array_keys(array_filter($f['choices'])))
    : '';

// ── flight_infos — minimal for now, per plan (confirmed with customer later) ──
$flightInfos = !empty($f['flight']['connection_type'])
    ? json_encode(['connection_type' => $f['flight']['connection_type']])
    : null;

// ── days ──
$days = json_encode([
    'makkah'  => (int) ($f['makkahStay'] ?? 0),
    'madinah' => (int) ($f['madinahStay'] ?? 0),
    'total'   => (int) ($f['duration'] ?? 0),
]);

// ── hotel_infos ── (per-night price × trip duration in nights)
$hotelInfos = null;
if (!empty($f['choices']['hotel']) && !empty($f['accommodation']) && is_array($f['accommodation'])) {
    $selections = [];
    $hotelPerNightTotal = 0.0;
    foreach ($f['accommodation'] as $a) {
        $price = (float) ($a['price'] ?? 0);
        $hotelPerNightTotal += $price;
        $selections[] = [
            'hotel_sys_id'      => $a['hotel_sys_id'] ?? null,
            'hotel_name'        => $a['hotel_name'] ?? '',
            'room_type_sys_id'  => $a['room_type_sys_id'] ?? null,
            'room_name'         => $a['room_name'] ?? '',
            'board_type_sys_id' => $a['board_type_sys_id'] ?? null,
            'board_name'        => $a['board_name'] ?? '',
            'price'             => $price,
        ];
    }
    $duration = (int) ($f['duration'] ?? 0);
    $hotelTotal = $hotelPerNightTotal * max(1, $duration);
    $hotelInfos = json_encode([
        'category'     => $f['hotelCategory'] ?? null,
        'selections'   => $selections,
        'nights'       => $duration,
        'total_prices' => moneyBreakdown($hotelTotal, $rates),
    ], JSON_UNESCAPED_UNICODE);
}

// ── meal_infos ──
$mealInfos = null;
if (!empty($f['choices']['meal']) && !empty($f['meal'])) {
    $mealPrice = (float) ($f['meal']['price'] ?? 0) * $adults;
    $mealInfos = json_encode([
        'meal_sys_id'  => $f['meal']['meal_sys_id'] ?? null,
        'meal_name'    => $f['meal']['meal_name'] ?? '',
        'tier'         => $f['meal']['tier'] ?? '',
        'price'        => (float) ($f['meal']['price'] ?? 0),
        'total_prices' => moneyBreakdown($mealPrice, $rates),
    ], JSON_UNESCAPED_UNICODE);
}

// ── transport_infos ──
$transportInfos = null;
if (!empty($f['choices']['transport']) && !empty($f['transport']) && is_array($f['transport'])) {
    $legs = [];
    $transportTotal = 0.0;
    foreach ($f['transport'] as $leg) {
        $price = (float) ($leg['price'] ?? 0);
        $transportTotal += $price;
        $legs[] = [
            'route_sys_id'        => $leg['route_sys_id'] ?? null,
            'route_name'          => $leg['route_name'] ?? '',
            'vehicle_type_sys_id' => $leg['vehicle_type_sys_id'] ?? null,
            'vehicle_name'        => $leg['vehicle_type'] ?? '',
            'price'               => $price,
        ];
    }
    $transportInfos = json_encode([
        'legs'         => $legs,
        'total_prices' => moneyBreakdown($transportTotal, $rates),
    ], JSON_UNESCAPED_UNICODE);
}

// ── moyallem_infos ── (general Moyallem step selections)
$moyallemInfos = null;
if (!empty($f['choices']['moyallem']) && !empty($f['moyallem']) && is_array($f['moyallem'])) {
    $services = [];
    $moyallemTotal = 0.0;
    foreach ($f['moyallem'] as $m) {
        $price = (float) ($m['price'] ?? 0);
        $moyallemTotal += $price;
        $services[] = [
            'sys_id'       => $m['sys_id'] ?? null,
            'service_name' => $m['name'] ?? '',
            'category'     => $m['category'] ?? '',
            'price'        => $price,
        ];
    }
    $moyallemInfos = json_encode([
        'services'     => $services,
        'total_prices' => moneyBreakdown($moyallemTotal, $rates),
    ], JSON_UNESCAPED_UNICODE);
}

// ── ziarah_infos ── (flat per-selection — not multiplied by adults, matches
// mini-builder's getZiarahPrice(): each selection's own price + its
// optional per-ziarah Moyallem add-on price)
$ziarahInfos = null;
if (!empty($f['choices']['ziarah']) && !empty($f['ziarah']) && is_array($f['ziarah'])) {
    $selections = [];
    $ziarahTotal = 0.0;
    foreach ($f['ziarah'] as $z) {
        $price = (float) ($z['price'] ?? 0);
        $moyallemPrice = (float) ($z['moyallemPriceSar'] ?? 0);
        $ziarahTotal += $price + $moyallemPrice;
        $selections[] = [
            'sys_id'                 => $z['id'] ?? null,
            'name'                   => $z['name'] ?? '',
            'price'                  => $price,
            'transport_route_sys_id' => $z['routeId'] ?? null,
            'moyallem_category'      => $z['moyallemCategory'] ?? null,
            'moyallem_price'         => $moyallemPrice,
        ];
    }
    $ziarahInfos = json_encode([
        'selections'   => $selections,
        'total_prices' => moneyBreakdown($ziarahTotal, $rates),
    ], JSON_UNESCAPED_UNICODE);
}

// ── grand total across every section ──
$grandTotalSar = 0.0;
foreach ([$visaInfos, $serviceInfos, $hotelInfos, $mealInfos, $transportInfos, $moyallemInfos, $ziarahInfos] as $section) {
    if ($section) {
        $decoded = json_decode($section, true);
        $grandTotalSar += (float) ($decoded['total_prices']['sar'][0] ?? 0);
    }
}
$totalPrices = json_encode(moneyBreakdown($grandTotalSar, $rates));

// ── data_json — organized snapshot (not a raw dump of the submitted
// state) — groups related fields together and drops UI-only scaffolding
// (currency selector, internal ids) that isn't part of the booking data. ──
$dataJson = json_encode([
    'traveler' => ['name' => $name, 'phone' => $phone, 'email' => $email],
    'persons'  => json_decode($persons, true),
    'trip'     => [
        'total_duration' => (int) ($f['duration'] ?? 0),
        'makkah'         => (int) ($f['makkahStay'] ?? 0),
        'madinah'        => (int) ($f['madinahStay'] ?? 0),
        'service_level'  => $f['serviceLevel'] ?? null,
        'hotel_category' => $f['hotelCategory'] ?? null,
    ],
    'choices'   => $f['choices'] ?? [],
    'visa'      => !empty($f['visa']) ? ['type' => $f['visa']['visa_type'] ?? null, 'sys_id' => $f['visa']['visa_type_sys_id'] ?? null, 'price' => (float) ($f['visa']['price'] ?? 0)] : null,
    'flight'    => !empty($f['flight']) ? ['connection_type' => $f['flight']['connection_type'] ?? null] : null,
    'hotel'     => $hotelInfos ? ['selections' => json_decode($hotelInfos, true)['selections']] : null,
    'meal'      => $mealInfos ? array_diff_key(json_decode($mealInfos, true), ['total_prices' => 0]) : null,
    'transport' => $transportInfos ? ['legs' => json_decode($transportInfos, true)['legs']] : null,
    'moyallem'  => $moyallemInfos ? ['services' => json_decode($moyallemInfos, true)['services']] : null,
    'ziarah'    => $ziarahInfos ? json_decode($ziarahInfos, true) : null,
    'total_prices' => moneyBreakdown($grandTotalSar, $rates),
], JSON_UNESCAPED_UNICODE);

try {
    $ids = generateSysIdAndUuid($db, 'custom_builds');

    // Client-side login uses $_SESSION['user_id'] (the numeric PK) — resolve
    // it to the user's sys_id, since metadata stores sys_id, not the PK.
    // Anonymous submissions (no session) leave created_by/updated_by null.
    $clientSysId = null;
    if (!empty($_SESSION['user_id'])) {
        $u = $db->prepare("SELECT sys_id FROM users WHERE id = ?");
        $u->execute([$_SESSION['user_id']]);
        $clientSysId = $u->fetchColumn() ?: null;
    }
    $now = date('Y-m-d H:i:s');
    $metadata = json_encode(['created_at' => $now, 'created_by' => $clientSysId, 'updated_at' => $now, 'updated_by' => $clientSysId]);

    $stmt = $db->prepare("INSERT INTO custom_builds
        (sys_id, uuid, customer_infos, persons, visa_infos, service_infos, choices,
         flight_infos, days, hotel_infos, meal_infos, transport_infos, moyallem_infos, ziarah_infos,
         total_prices, markups, data_json, metadata)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $ids['sys_id'], $ids['uuid'], $customerInfos, $persons, $visaInfos, $serviceInfos, $choices,
        $flightInfos, $days, $hotelInfos, $mealInfos, $transportInfos, $moyallemInfos, $ziarahInfos,
        $totalPrices, null, $dataJson, $metadata,
    ]);

    // Generate the PDF now, while every value needed is already at hand —
    // avoids a second DB read, and the preview page can rely on pdf_path
    // being set the moment it loads.
    $pdfPath = buildBookingPdf([
        'sys_id'          => $ids['sys_id'],
        'customer_infos'  => $customerInfos,
        'persons'         => $persons,
        'visa_infos'      => $visaInfos,
        'service_infos'   => $serviceInfos,
        'flight_infos'    => $flightInfos,
        'days'            => $days,
        'hotel_infos'     => $hotelInfos,
        'transport_infos' => $transportInfos,
        'moyallem_infos'  => $moyallemInfos,
        'ziarah_infos'    => $ziarahInfos,
        'total_prices'    => $totalPrices,
        'choices'         => $choices,
        'data_json'       => $dataJson,
    ]);
    if ($pdfPath) {
        $db->prepare("UPDATE custom_builds SET pdf_path = ? WHERE sys_id = ?")->execute([$pdfPath, $ids['sys_id']]);
    }

    jsonResponse(true, 'Your custom package has been submitted!', [
        'sys_id'   => $ids['sys_id'],
        'redirect' => BASE_URL . '/pages/custom-build-preview.php?ref=' . $ids['sys_id'],
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Something went wrong while saving your package. Please try again.');
}