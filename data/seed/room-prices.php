<?php
// FILE PATH: /data/seed/room-prices.php
// Run this ONCE, right after data/seed/room-board-types.php (needs the
// global board-type catalog to already exist) and after room-types.php.
// One row per room type — board_prices holds every board-type's price
// for that room, all referencing the global room_board_types catalog.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

// Flat base price per board type name — a real admin would set different
// prices per room type, this is just seed data to have something to test.
$basePriceByBoardName = [
    'Room Only'        => 300,
    'Bed & Breakfast'  => 380,
    'Half Board'       => 450,
    'Full Board'       => 520,
];
$validFrom = date('Y') . '-01-01';
$validTo   = date('Y') . '-12-31';

try {
    $db = getDB();

    $boardTypes = $db->query("SELECT sys_id, name FROM room_board_types")->fetchAll(PDO::FETCH_ASSOC);
    if (!$boardTypes) {
        die("No board types found — run room-board-types.php first.\n");
    }

    $roomTypes = $db->query("SELECT sys_id, name FROM room_types")->fetchAll(PDO::FETCH_ASSOC);
    if (!$roomTypes) {
        die("No room types found — run room-types.php first.\n");
    }

    foreach ($roomTypes as $rt) {
        $exists = $db->prepare("SELECT id FROM room_prices WHERE room_type_sys_id = ?");
        $exists->execute([$rt['sys_id']]);
        if ($exists->fetch()) {
            echo "Prices for '{$rt['name']}' already exist — skipped.\n";
            continue;
        }

        $boardPrices = [];
        foreach ($boardTypes as $bt) {
            $boardPrices[] = [
                'board_type_sys_id' => $bt['sys_id'],
                'board_type_title'  => $bt['name'],
                'valid_from'        => $validFrom,
                'valid_to'          => $validTo,
                'price'             => $basePriceByBoardName[$bt['name']] ?? 300,
            ];
        }

        $ids = generateSysIdAndUuid($db, 'room_prices');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO room_prices (sys_id, uuid, room_type_sys_id, board_prices, metadata) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$ids['sys_id'], $ids['uuid'], $rt['sys_id'], json_encode($boardPrices, JSON_UNESCAPED_UNICODE), $metadata]);

        echo "Prices for '{$rt['name']}' created — sys_id: {$ids['sys_id']} (" . count($boardPrices) . " board types)\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}