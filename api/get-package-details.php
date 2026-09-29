<?php
/**
 * TravHub — Get Package Details API
 * Returns full package data as JSON for dynamic use
 * GET /api/get-package-details.php?id=1
 * GET /api/get-package-details.php       (returns all packages)
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Static package data — in production this would come from DB
$packages = [
    [
        'id'        => 1,
        'name'      => 'Economy Essence',
        'slug'      => 'economy-essence',
        'duration'  => 7,
        'budget'    => 'economy',
        'stars'     => 3,
        'price_sar' => 3500,
        'badge'     => 'Best Value',
        'rating'    => 4.2,
        'reviews'   => 120,
        'city'      => 'Makkah & Madinah',
        'flight'    => 'Economy Class',
        'hotel'     => '3★ Hotel — 600m from Haram',
        'transport' => 'Shared Bus',
        'includes'  => ['Umrah Visa','Return flight (economy)','3★ accommodation','Shared airport transfers','Group Ziyarah tour','Basic travel insurance'],
        'excludes'  => ['Personal shopping','Additional excursions','Tipping'],
        'itinerary' => [
            ['day'=>1,'title'=>'Arrival Jeddah','desc'=>'Arrive Jeddah, group transfer to Makkah hotel'],
            ['day'=>2,'title'=>'Perform Umrah','desc'=>'Perform Tawaf, Sa\'i and complete Umrah rites with group guide'],
            ['day'=>3,'title'=>'Ibadah & Rest','desc'=>'Free time for voluntary prayers and rest near Haram'],
            ['day'=>4,'title'=>'Makkah Ziyarah','desc'=>'Jabal al-Noor, Cave of Hira, Jabal Thawr, Arafat, Mina'],
            ['day'=>5,'title'=>'Transfer to Madinah','desc'=>'Coach to Madinah, check-in hotel near Masjid Nabawi'],
            ['day'=>6,'title'=>'Masjid al-Nabawi','desc'=>'Visit Rawdah, send salawat, Ziyarah of Madinah sites'],
            ['day'=>7,'title'=>'Departure','desc'=>'Breakfast, transfer to Jeddah airport, fly home'],
        ],
    ],
    [
        'id'        => 2,
        'name'      => 'Premium Spiritual',
        'slug'      => 'premium-spiritual',
        'duration'  => 14,
        'budget'    => 'premium',
        'stars'     => 4,
        'price_sar' => 5800,
        'badge'     => 'Most Popular',
        'rating'    => 4.8,
        'reviews'   => 245,
        'city'      => 'Makkah & Madinah',
        'flight'    => 'Direct Flight',
        'hotel'     => '4★ Hotel — 200m from Haram',
        'transport' => 'Private Car',
        'includes'  => ['Umrah Visa','Direct return flight','4★ accommodation','Private airport transfers','Private Ziyarah tours','Full travel insurance','Dedicated group imam'],
        'excludes'  => ['International departure tax (if applicable)','Personal expenses'],
        'itinerary' => [
            ['day'=>1,'title'=>'VIP Arrival','desc'=>'Private meet & greet, direct transfer to 4★ Makkah hotel'],
            ['day'=>2,'title'=>'First Umrah','desc'=>'Perform Umrah rites with personal guide'],
            ['day'=>3,'title'=>'Ibadah','desc'=>'Voluntary Tawaf, Quran recitation, night prayers'],
            ['day'=>4,'title'=>'Makkah Ziyarah','desc'=>'Private tour: Jabal al-Noor, Cave of Hira, Arafat, Mina, Muzdalifah'],
            ['day'=>5,'title'=>'Second Umrah','desc'=>'Perform additional Umrah on behalf of family'],
            ['day'=>6,'title'=>'Free Ibadah','desc'=>'Personal worship time at Masjid al-Haram'],
            ['day'=>7,'title'=>'Rest & Shopping','desc'=>'Abraj Al-Bait mall, local market, dates and souvenirs'],
            ['day'=>8,'title'=>'Transfer Madinah','desc'=>'Private car to Madinah, 4★ hotel check-in'],
            ['day'=>9,'title'=>'Masjid al-Nabawi','desc'=>'First visit, Rawdah, 40 prayers project begins'],
            ['day'=>10,'title'=>'Madinah Ziyarah','desc'=>'Masjid Quba, Masjid al-Qiblatayn, Uhud mountain'],
            ['day'=>11,'title'=>'Ibadah Madinah','desc'=>'I\'tikaf, night prayers, Tahajjud at Masjid Nabawi'],
            ['day'=>12,'title'=>'Date Market','desc'=>'Al-Nakheel date market, local halal food, shopping'],
            ['day'=>13,'title'=>'Final Day','desc'=>'Farewell prayers, pack, rest'],
            ['day'=>14,'title'=>'Departure','desc'=>'Transfer Madinah airport, direct flight home'],
        ],
    ],
    [
        'id'        => 3,
        'name'      => 'Royal Sanctuary',
        'slug'      => 'royal-sanctuary',
        'duration'  => 21,
        'budget'    => 'luxury',
        'stars'     => 5,
        'price_sar' => 12500,
        'badge'     => 'Ultra Luxury',
        'rating'    => 5.0,
        'reviews'   => 85,
        'city'      => 'Haram Front',
        'flight'    => 'Business Class',
        'hotel'     => '5★ Fairmont — Clock Tower',
        'transport' => 'Chauffeur',
        'includes'  => ['Umrah Visa (VIP processing)','Business class flight','5★ hotel adjacent to Haram','Personal chauffeur all 21 days','Private certified scholar','Qurbani included','Premium travel insurance','Welcome gift hamper'],
        'excludes'  => ['Nothing — fully all-inclusive'],
        'itinerary' => [
            ['day'=>1,'title'=>'VIP Welcome','desc'=>'Private terminal Jeddah, chauffeur to Fairmont Makkah'],
            ['day'=>2,'title'=>'First Umrah','desc'=>'Private guide leads Umrah — no crowds, no rush'],
            ['day'=>3,'title'=>'Historical Makkah','desc'=>'Private tour of all Makkah historical sites'],
            ['day'=>4,'title'=>'Spa & Rest','desc'=>'Rejuvenate — 5★ spa, personal ibadah time'],
            ['day'=>5,'title'=>'Night Umrah','desc'=>'Late night Umrah — the Haram is most beautiful at 3AM'],
        ],
    ],
    [
        'id'        => 4,
        'name'      => 'Standard Serenity',
        'slug'      => 'standard-serenity',
        'duration'  => 14,
        'budget'    => 'economy',
        'stars'     => 3,
        'price_sar' => 4200,
        'badge'     => '',
        'rating'    => 4.5,
        'reviews'   => 156,
        'city'      => 'Makkah & Madinah',
        'flight'    => 'Economy Class',
        'hotel'     => '3★ Plus — 400m from Haram',
        'transport' => 'Shared Bus',
        'includes'  => ['Umrah Visa','Return flight','3★ Plus accommodation','Shared transfers','Group Ziyarah'],
        'excludes'  => ['Personal expenses','Extra meals'],
        'itinerary' => [],
    ],
    [
        'id'        => 5,
        'name'      => 'Elite Weekend',
        'slug'      => 'elite-weekend',
        'duration'  => 7,
        'budget'    => 'premium',
        'stars'     => 5,
        'price_sar' => 6200,
        'badge'     => 'Quick Trip',
        'rating'    => 4.7,
        'reviews'   => 92,
        'city'      => 'Makkah Only',
        'flight'    => 'Direct Flight',
        'hotel'     => '5★ Hotel — 50m from Haram',
        'transport' => 'Private Car',
        'includes'  => ['Umrah Visa','Direct flight','5★ accommodation','Private transfers','Private guide'],
        'excludes'  => ['Madinah visits (Makkah-only package)'],
        'itinerary' => [],
    ],
    [
        'id'        => 6,
        'name'      => 'Spiritual Immersion',
        'slug'      => 'spiritual-immersion',
        'duration'  => 21,
        'budget'    => 'premium',
        'stars'     => 4,
        'price_sar' => 8900,
        'badge'     => 'Extended Stay',
        'rating'    => 4.9,
        'reviews'   => 114,
        'city'      => 'Makkah & Madinah',
        'flight'    => 'Premium Economy',
        'hotel'     => '4★ Deluxe — 150m from Haram',
        'transport' => 'VIP Coach',
        'includes'  => ['Umrah Visa','Premium economy flight','4★ Deluxe accommodation','VIP coach transfers','Full Ziyarah programme','Travel insurance'],
        'excludes'  => ['Personal shopping'],
        'itinerary' => [],
    ],
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($id !== null) {
    $pkg = array_values(array_filter($packages, fn($p) => $p['id'] === $id));
    if (empty($pkg)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Package not found']);
    } else {
        // Add live exchange rates
        $rates = getExchangeRates();
        $pkg[0]['price_bdt'] = round($pkg[0]['price_sar'] * $rates['BDT']);
        $pkg[0]['price_usd'] = round($pkg[0]['price_sar'] * $rates['USD'], 2);
        echo json_encode(['success' => true, 'package' => $pkg[0], 'rates' => $rates]);
    }
} else {
    // Return all (without full itinerary for list view)
    $rates = getExchangeRates();
    $list  = array_map(function($p) use ($rates) {
        return [
            'id'        => $p['id'],
            'name'      => $p['name'],
            'slug'      => $p['slug'],
            'duration'  => $p['duration'],
            'budget'    => $p['budget'],
            'stars'     => $p['stars'],
            'price_sar' => $p['price_sar'],
            'price_bdt' => round($p['price_sar'] * $rates['BDT']),
            'price_usd' => round($p['price_sar'] * $rates['USD'], 2),
            'badge'     => $p['badge'],
            'rating'    => $p['rating'],
            'reviews'   => $p['reviews'],
            'city'      => $p['city'],
        ];
    }, $packages);
    echo json_encode(['success' => true, 'packages' => $list, 'count' => count($list), 'rates' => $rates]);
}
