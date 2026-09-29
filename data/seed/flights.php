<?php
// FILE PATH: /data/seed/flights.php
// Run this ONCE, right after data/table-sql/flights.sql.
// Uses generateSysIdAndUuid() so sys_id/uuid come from the real
// generator — keeps sys_id_counters in sync for whatever fare type
// gets added next from the admin panel later.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$fares = [
    [
        'fare_type' => 'Flexible',
        'type_prices' => [
            ['type' => 'Direct',     'price' => 650],
            ['type' => 'Connecting', 'price' => 480],
        ],
        'sort_order' => 1,
    ],
    [
        'fare_type' => 'Fixed',
        'type_prices' => [
            ['type' => 'Direct',     'price' => 500],
            ['type' => 'Connecting', 'price' => 350],
        ],
        'sort_order' => 2,
    ],
];

try {
    $db = getDB();

    foreach ($fares as $fare) {
        $exists = $db->prepare("SELECT id FROM flights WHERE fare_type = ?");
        $exists->execute([$fare['fare_type']]);
        if ($exists->fetch()) {
            echo "Fare type '{$fare['fare_type']}' already exists — skipped.\n";
            continue;
        }

        $ids = generateSysIdAndUuid($db, 'flights');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO flights (sys_id, uuid, fare_type, type_prices, sort_order, metadata) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ids['sys_id'],
            $ids['uuid'],
            $fare['fare_type'],
            json_encode($fare['type_prices']),
            $fare['sort_order'],
            $metadata,
        ]);

        echo "Fare type '{$fare['fare_type']}' created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}