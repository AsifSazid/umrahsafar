<?php
// FILE PATH: /data/seed/visa-types.php
// Run this ONCE, right after data/table-sql/visa-types.sql.
// Uses generateSysIdAndUuid() so sys_id/uuid come from the real
// generator — keeps sys_id_counters in sync for whatever visa type
// gets added next from the admin panel later.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$visaTypes = [
    ['name' => 'Umrah Visa',        'price' => 450, 'for_umrah' => 0, 'description' => 'Standard Umrah visa for pilgrims.',                   'icon' => 'file-text',      'sort_order' => 1],
    ['name' => 'Tourist Visa',      'price' => 300, 'for_umrah' => 0, 'description' => 'General tourist visa, valid for Umrah as well.',      'icon' => 'plane',          'sort_order' => 2],
    ['name' => 'Visa Not Required', 'price' => 0,   'for_umrah' => 0, 'description' => 'For eligible nationalities — verify before booking.', 'icon' => 'check-circle-2', 'sort_order' => 3],
];

try {
    $db = getDB();

    foreach ($visaTypes as $visa) {
        $exists = $db->prepare("SELECT id FROM visa_types WHERE name = ?");
        $exists->execute([$visa['name']]);
        if ($exists->fetch()) {
            echo "Visa type '{$visa['name']}' already exists — skipped.\n";
            continue;
        }

        $ids = generateSysIdAndUuid($db, 'visa_types');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO visa_types (sys_id, uuid, name, price, for_umrah, description, icon, sort_order, metadata) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ids['sys_id'],
            $ids['uuid'],
            $visa['name'],
            $visa['price'],
            $visa['for_umrah'],
            $visa['description'],
            $visa['icon'],
            $visa['sort_order'],
            $metadata,
        ]);

        echo "Visa type '{$visa['name']}' created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}