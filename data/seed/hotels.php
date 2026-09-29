<?php
// FILE PATH: /data/seed/hotels.php
// Run this ONCE, right after data/table-sql/hotels.sql — FIRST in the
// hotels → room_types → room_board_types → room_prices chain.
// Uses generateSysIdAndUuid() so sys_id/uuid come from the real
// generator, same pattern as every other table in this project.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$hotels = [
    [
        'name' => 'Hotel Al Safwah Royale Orchid',
        'city' => 'Makkah',
        'star_rating' => 5,
        'checkin_schedule' => ['in' => '14:00', 'out' => '12:00'],
        'distance_info' => ['landmark' => 'Masjid al-Haram', 'gate_no' => '1', 'unit' => 'm', 'value' => 450, 'walking' => ['enabled' => true, 'minutes' => 6]],
        'age_ranges' => ['child' => ['min' => 2, 'max' => 11], 'infant' => ['min' => 0, 'max' => 1]],
        'description' => 'Five-star hotel with a direct view of the Haram, steps from the Clock Tower.',
        'address' => 'Ibrahim Al Khalil Street, Makkah',
        'phone' => '+966920000001',
        'email' => 'reservations@example.com',
        'sort_order' => 1,
    ],
    [
        'name' => 'Dar Al Taqwa Hotel',
        'city' => 'Madinah',
        'star_rating' => 4,
        'checkin_schedule' => ['in' => '14:00', 'out' => '12:00'],
        'distance_info' => ['landmark' => 'Masjid an-Nabawi', 'gate_no' => '5', 'unit' => 'm', 'value' => 200, 'walking' => ['enabled' => true, 'minutes' => 3]],
        'age_ranges' => ['child' => ['min' => 2, 'max' => 12], 'infant' => ['min' => 0, 'max' => 1]],
        'description' => 'Comfortable four-star hotel a short walk from the Prophet\'s Mosque.',
        'address' => 'Central Area, Madinah',
        'phone' => '+966920000002',
        'email' => 'info@example.com',
        'sort_order' => 2,
    ],
];

try {
    $db = getDB();

    foreach ($hotels as $hotel) {
        $exists = $db->prepare("SELECT id FROM hotels WHERE name = ?");
        $exists->execute([$hotel['name']]);
        if ($exists->fetch()) {
            echo "Hotel '{$hotel['name']}' already exists — skipped.\n";
            continue;
        }

        $ids = generateSysIdAndUuid($db, 'hotels');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO hotels
            (sys_id, uuid, city, star_rating, name, checkin_schedule, distance_info, age_ranges, description, address, phone, email, sort_order, metadata)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ids['sys_id'],
            $ids['uuid'],
            $hotel['city'],
            $hotel['star_rating'],
            $hotel['name'],
            json_encode($hotel['checkin_schedule']),
            json_encode($hotel['distance_info']),
            json_encode($hotel['age_ranges']),
            $hotel['description'],
            $hotel['address'],
            $hotel['phone'],
            $hotel['email'],
            $hotel['sort_order'],
            $metadata,
        ]);

        echo "Hotel '{$hotel['name']}' created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}