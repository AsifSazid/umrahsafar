<?php
// FILE PATH: /data/seed/moyallem-services.php
// Run this ONCE, right after data/table-sql/moyallem-services.sql.
// Uses generateSysIdAndUuid() so sys_id/uuid come from the real
// generator — keeps sys_id_counters in sync for whatever service
// gets added next from the admin panel later.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$services = [
    [
        'name' => 'Moyallem in Makkah',
        'description' => "Certified spiritual guide for Umrah rites, Tawaf and Sa'i assistance in Makkah",
        'icon' => 'mosque',
        'category_prices' => [
            ['category' => 'General', 'price' => 350],
            ['category' => 'Expert',  'price' => 650],
            ['category' => 'VIP',     'price' => 1200],
        ],
        'sort_order' => 1,
    ],
    [
        'name' => 'Moyallem in Madinah',
        'description' => 'Dedicated guide for Rawdah visit, 40-prayers program and Madinah Ziyarah',
        'icon' => 'map-pin',
        'category_prices' => [
            ['category' => 'General', 'price' => 300],
            ['category' => 'Expert',  'price' => 550],
            ['category' => 'VIP',     'price' => 1000],
        ],
        'sort_order' => 2,
    ],
    [
        'name' => 'Moyallem for Ziyarah',
        'description' => 'Expert guide for historical sites: Arafat, Mina, Muzdalifah, Jabal al-Noor etc.',
        'icon' => 'map',
        'category_prices' => [
            ['category' => 'General', 'price' => 250],
            ['category' => 'Expert',  'price' => 450],
            ['category' => 'VIP',     'price' => 900],
        ],
        'sort_order' => 3,
    ],
    [
        'name' => 'Group Moyallem Package',
        'description' => 'One imam/guide covers full Umrah group — shared cost, maximum 15 pilgrims',
        'icon' => 'users',
        'category_prices' => [
            ['category' => 'General', 'price' => 200],
            ['category' => 'Expert',  'price' => 350],
            ['category' => 'VIP',     'price' => 700],
        ],
        'sort_order' => 4,
    ],
];

try {
    $db = getDB();

    foreach ($services as $service) {
        $exists = $db->prepare("SELECT id FROM moyallem_services WHERE name = ?");
        $exists->execute([$service['name']]);
        if ($exists->fetch()) {
            echo "Service '{$service['name']}' already exists — skipped.\n";
            continue;
        }

        $ids = generateSysIdAndUuid($db, 'moyallem_services');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO moyallem_services (sys_id, uuid, name, description, icon, category_prices, sort_order, metadata) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ids['sys_id'],
            $ids['uuid'],
            $service['name'],
            $service['description'],
            $service['icon'],
            json_encode($service['category_prices'], JSON_UNESCAPED_UNICODE),
            $service['sort_order'],
            $metadata,
        ]);

        echo "Service '{$service['name']}' created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}