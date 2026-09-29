<?php
// FILE PATH: /data/seed/meals.php
// Run this ONCE, right after data/table-sql/meals.sql.
// Uses generateSysIdAndUuid() so sys_id/uuid come from the real
// generator — keeps sys_id_counters in sync for whatever meal plan
// gets added next from the admin panel later.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$meals = [
    [
        'name' => 'Meal Package',
        'min_adults_required' => 10,
        'price_tiers' => [
            ['tier' => 'Economy', 'price' => 40],
            ['tier' => 'Premium', 'price' => 75],
            ['tier' => 'Luxury',  'price' => 140],
        ],
        'sort_order' => 1,
    ],
];

try {
    $db = getDB();

    foreach ($meals as $meal) {
        $exists = $db->prepare("SELECT id FROM meals WHERE name = ?");
        $exists->execute([$meal['name']]);
        if ($exists->fetch()) {
            echo "Meal '{$meal['name']}' already exists — skipped.\n";
            continue;
        }

        $ids = generateSysIdAndUuid($db, 'meals');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO meals (sys_id, uuid, name, min_adults_required, price_tiers, sort_order, metadata) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ids['sys_id'],
            $ids['uuid'],
            $meal['name'],
            $meal['min_adults_required'],
            json_encode($meal['price_tiers']),
            $meal['sort_order'],
            $metadata,
        ]);

        echo "Meal '{$meal['name']}' created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}