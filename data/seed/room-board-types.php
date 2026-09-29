<?php
// FILE PATH: /data/seed/room-board-types.php
// Run this ONCE, right after data/table-sql/room-board-types.sql.
// This is now a GLOBAL catalog — not tied to any room type — so this
// seed just creates the 4 standard board options once.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$boardTypes = ['Room Only', 'Bed & Breakfast', 'Half Board', 'Full Board'];

try {
    $db = getDB();

    foreach ($boardTypes as $i => $name) {
        $exists = $db->prepare("SELECT id FROM room_board_types WHERE name = ?");
        $exists->execute([$name]);
        if ($exists->fetch()) {
            echo "Board type '{$name}' already exists — skipped.\n";
            continue;
        }

        $ids = generateSysIdAndUuid($db, 'room_board_types');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO room_board_types (sys_id, uuid, name, sort_order, metadata) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$ids['sys_id'], $ids['uuid'], $name, $i + 1, $metadata]);

        echo "Board type '{$name}' created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}