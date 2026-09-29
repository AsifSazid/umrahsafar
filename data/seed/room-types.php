<?php
// FILE PATH: /data/seed/room-types.php
// Run this ONCE, right after data/seed/hotels.php (needs hotels to
// already exist, to look up hotel_sys_id by name).

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$roomTypes = [
    [
        'hotel_name' => 'Hotel Al Safwah Royale Orchid',
        'name' => 'Deluxe Haram View',
        'description' => 'Spacious room with a direct view of the Haram courtyard.',
        'person_capacity' => ['adults' => 3, 'children' => 1],
        'size' => ['unit' => 'sqr-m', 'value' => 32.5],
        'bed_config' => [
            ['bed_type' => 'King', 'quantity' => 1, 'capacity_per_bed' => 2],
            ['bed_type' => 'Single', 'quantity' => 1, 'capacity_per_bed' => 1],
        ],
        'amenities' => ['Haram View', 'Free WiFi', 'Air Conditioning', 'Mini Fridge'],
        'add_on' => ['bed' => 1, 'charge' => 60],
        'view_info' => ['enabled' => 1, 'description' => 'Full frontal view of Masjid al-Haram and the Clock Tower from a private balcony.'],
        'sort_order' => 1,
    ],
    [
        'hotel_name' => 'Hotel Al Safwah Royale Orchid',
        'name' => 'Executive Suite',
        'description' => 'Separate living area with premium furnishings.',
        'person_capacity' => ['adults' => 4, 'children' => 2],
        'size' => ['unit' => 'sqr-m', 'value' => 55],
        'bed_config' => [
            ['bed_type' => 'King', 'quantity' => 1, 'capacity_per_bed' => 2],
            ['bed_type' => 'Queen', 'quantity' => 1, 'capacity_per_bed' => 2],
        ],
        'amenities' => ['Haram View', 'Living Area', 'Free WiFi', 'Air Conditioning', 'Mini Bar'],
        'add_on' => ['bed' => 1, 'charge' => 80],
        'view_info' => ['enabled' => 1, 'description' => 'Partial Haram view from the living area windows.'],
        'sort_order' => 2,
    ],
    [
        'hotel_name' => 'Dar Al Taqwa Hotel',
        'name' => 'Standard Twin Room',
        'description' => 'Comfortable twin room for pilgrims traveling together.',
        'person_capacity' => ['adults' => 2, 'children' => 1],
        'size' => ['unit' => 'sqr-m', 'value' => 24],
        'bed_config' => [
            ['bed_type' => 'Twin', 'quantity' => 2, 'capacity_per_bed' => 1],
        ],
        'amenities' => ['Free WiFi', 'Air Conditioning'],
        'add_on' => ['bed' => 0, 'charge' => 0],
        'view_info' => ['enabled' => 0, 'description' => ''],
        'sort_order' => 1,
    ],
    [
        'hotel_name' => 'Dar Al Taqwa Hotel',
        'name' => 'Family Suite',
        'description' => 'Larger room suited for families with children.',
        'person_capacity' => ['adults' => 4, 'children' => 3],
        'size' => ['unit' => 'sqr-m', 'value' => 40],
        'bed_config' => [
            ['bed_type' => 'Queen', 'quantity' => 1, 'capacity_per_bed' => 2],
            ['bed_type' => 'Twin', 'quantity' => 2, 'capacity_per_bed' => 1],
        ],
        'amenities' => ['Free WiFi', 'Air Conditioning', 'Extra Storage'],
        'add_on' => ['bed' => 1, 'charge' => 50],
        'view_info' => ['enabled' => 1, 'description' => 'Side view of Masjid an-Nabawi\'s green dome from the upper floor.'],
        'sort_order' => 2,
    ],
];

try {
    $db = getDB();

    foreach ($roomTypes as $rt) {
        $hotelSysId = $db->prepare("SELECT sys_id FROM hotels WHERE name = ?");
        $hotelSysId->execute([$rt['hotel_name']]);
        $hotelSysId = $hotelSysId->fetchColumn();
        if (!$hotelSysId) {
            echo "  Warning: hotel '{$rt['hotel_name']}' not found — skipping room type '{$rt['name']}'. Run hotels.php first.\n";
            continue;
        }

        $exists = $db->prepare("SELECT id FROM room_types WHERE hotel_sys_id = ? AND name = ?");
        $exists->execute([$hotelSysId, $rt['name']]);
        if ($exists->fetch()) {
            echo "Room type '{$rt['name']}' ({$rt['hotel_name']}) already exists — skipped.\n";
            continue;
        }

        $ids = generateSysIdAndUuid($db, 'room_types');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO room_types
            (sys_id, uuid, hotel_sys_id, name, description, person_capacity, size, bed_config, amenities, add_on, view_info, sort_order, metadata)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ids['sys_id'],
            $ids['uuid'],
            $hotelSysId,
            $rt['name'],
            $rt['description'],
            json_encode($rt['person_capacity']),
            json_encode($rt['size']),
            json_encode($rt['bed_config']),
            json_encode($rt['amenities']),
            json_encode($rt['add_on']),
            json_encode($rt['view_info']),
            $rt['sort_order'],
            $metadata,
        ]);

        echo "Room type '{$rt['name']}' ({$rt['hotel_name']}) created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}