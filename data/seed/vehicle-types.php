<?php
// FILE PATH: /data/seed/vehicle-types.php
// Run this ONCE, right after data/table-sql/vehicle-types.sql.
// Uses generateSysIdAndUuid() so sys_id/uuid come from the real
// generator — keeps sys_id_counters in sync for whatever vehicle
// type gets added next from the admin panel later.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$vehicleTypes = [
    ['name' => 'Car',      'icon' => 'car',         'sort_order' => 1, 'capacities' => ['seat' => 4,  'luggage' => 3]],
    ['name' => 'HiAce',    'icon' => 'truck',       'sort_order' => 2, 'capacities' => ['seat' => 10, 'luggage' => 8]],
    ['name' => 'Bus',      'icon' => 'bus',         'sort_order' => 3, 'capacities' => ['seat' => 45, 'luggage' => 30]],
    ['name' => 'Minibus',  'icon' => 'bus',         'sort_order' => 4, 'capacities' => ['seat' => 20, 'luggage' => 15]],
    ['name' => 'Coaster',  'icon' => 'bus',         'sort_order' => 5, 'capacities' => ['seat' => 25, 'luggage' => 20]],
    ['name' => 'GMC/JMC',  'icon' => 'car',         'sort_order' => 6, 'capacities' => ['seat' => 6,  'luggage' => 5]],
    ['name' => 'H1',       'icon' => 'car',         'sort_order' => 7, 'capacities' => ['seat' => 8,  'luggage' => 6]],
    ['name' => 'Staria',   'icon' => 'car',         'sort_order' => 8, 'capacities' => ['seat' => 8,  'luggage' => 6]],
    ['name' => 'Train',    'icon' => 'train-front', 'sort_order' => 9, 'capacities' => null],
];

try {
    $db = getDB();

    foreach ($vehicleTypes as $vehicle) {
        $exists = $db->prepare("SELECT id FROM vehicle_types WHERE name = ?");
        $exists->execute([$vehicle['name']]);
        if ($exists->fetch()) {
            echo "Vehicle type '{$vehicle['name']}' already exists — skipped.\n";
            continue;
        }

        $ids = generateSysIdAndUuid($db, 'vehicle_types');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO vehicle_types (sys_id, uuid, name, icon, capacities, sort_order, metadata) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ids['sys_id'],
            $ids['uuid'],
            $vehicle['name'],
            $vehicle['icon'],
            $vehicle['capacities'] ? json_encode($vehicle['capacities']) : null,
            $vehicle['sort_order'],
            $metadata,
        ]);

        echo "Vehicle type '{$vehicle['name']}' created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}