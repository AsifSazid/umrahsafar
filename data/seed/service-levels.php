<?php
// FILE PATH: /data/seed/service-levels.php
// Run this ONCE, right after data/table-sql/service-levels.sql.
// Uses generateSysIdAndUuid() so sys_id/uuid come from the real
// generator — keeps sys_id_counters in sync for whatever level gets
// added next from the admin panel later.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$levels = [
    ['name' => 'Economy', 'price' => 1200, 'features' => ['3-Star Hotels', 'Shared transfers', 'Ziyarah Tours'], 'sort_order' => 1],
    ['name' => 'Premium', 'price' => 2500, 'features' => ['4-Star Hotels', 'Private transfers', 'Ziyarah Tours'], 'sort_order' => 2],
    ['name' => 'Luxury',  'price' => 4500, 'features' => ['5-Star Hotels', 'Private car & guide', 'Ziyarah Tours'], 'sort_order' => 3],
];

try {
    $db = getDB();

    foreach ($levels as $level) {
        $exists = $db->prepare("SELECT id FROM service_levels WHERE name = ?");
        $exists->execute([$level['name']]);
        if ($exists->fetch()) {
            echo "Service level '{$level['name']}' already exists — skipped.\n";
            continue;
        }

        $ids = generateSysIdAndUuid($db, 'service_levels');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO service_levels (sys_id, uuid, name, price, features, sort_order, metadata) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ids['sys_id'],
            $ids['uuid'],
            $level['name'],
            $level['price'],
            json_encode($level['features']),
            $level['sort_order'],
            $metadata,
        ]);

        echo "Service level '{$level['name']}' created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}