<?php
// FILE PATH: /data/seed/ziarah.php
// Run this ONCE, right after data/table-sql/ziarah.sql.
// Uses generateSysIdAndUuid() so sys_id/uuid come from the real
// generator — keeps sys_id_counters in sync for whatever ziarah tour
// gets added next from the admin panel later.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$tours = [
    [
        'name' => 'Makkah Historical Ziyarah',
        'description' => 'Visit the key historical sites around Makkah connected to the life of the Prophet ﷺ.',
        'possible_duration' => 'Half Day (4-5 hours)',
        'itinerary' => [
            ['title' => 'Jabal al-Noor', 'description' => '<p>Visit the <strong>Cave of Hira</strong>, where the first revelation was received.</p>'],
            ['title' => 'Jabal Thawr', 'description' => '<p>See the cave where the Prophet ﷺ and Abu Bakr (RA) took refuge during the migration.</p>'],
            ['title' => 'Mina, Muzdalifah & Arafat', 'description' => '<p>Drive through the sacred plains central to the Hajj rites.</p>'],
        ],
        'route_sys_ids' => [],
        'moyallem_enabled' => 1,
        'price' => 250,
        'sort_order' => 1,
    ],
    [
        'name' => 'Madinah Historical Ziyarah',
        'description' => 'Explore the mosques and historical landmarks of Madinah Munawwarah.',
        'possible_duration' => 'Half Day (3-4 hours)',
        'itinerary' => [
            ['title' => 'Quba Mosque', 'description' => '<p>The first mosque built in Islam.</p>'],
            ['title' => 'Qiblatain Mosque', 'description' => '<p>Where the Qibla direction was changed during prayer.</p>'],
            ['title' => 'Uhud Mountain & Martyrs Cemetery', 'description' => '<p>Visit the site of the Battle of Uhud and pay respects at the martyrs\' graves.</p>'],
        ],
        'route_sys_ids' => [],
        'moyallem_enabled' => 1,
        'price' => 220,
        'sort_order' => 2,
    ],
];

try {
    $db = getDB();

    foreach ($tours as $tour) {
        $exists = $db->prepare("SELECT id FROM ziarah WHERE name = ?");
        $exists->execute([$tour['name']]);
        if ($exists->fetch()) {
            echo "Ziarah '{$tour['name']}' already exists — skipped.\n";
            continue;
        }

        $ids = generateSysIdAndUuid($db, 'ziarah');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO ziarah (sys_id, uuid, name, description, possible_duration, itinerary, route_sys_ids, moyallem_enabled, price, sort_order, metadata) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ids['sys_id'],
            $ids['uuid'],
            $tour['name'],
            $tour['description'],
            $tour['possible_duration'],
            json_encode($tour['itinerary'], JSON_UNESCAPED_UNICODE),
            json_encode($tour['route_sys_ids']),
            $tour['moyallem_enabled'],
            $tour['price'],
            $tour['sort_order'],
            $metadata,
        ]);

        echo "Ziarah '{$tour['name']}' created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}