<?php
// FILE PATH: /api/room-prices.php
// One row per room type — board_prices holds ALL of that room's
// board-type prices together. The admin "Prices" modal always saves
// the whole array in one go (upsert), matching how the UI works: check
// rows, fill in validity + price, hit Save once.
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

function decodePrices(array $r): array {
    $r['board_prices'] = json_decode($r['board_prices'] ?? '[]', true) ?: [];
    return $r;
}

// GET — the price row for one room type (there's at most one).
if ($method === 'GET') {
    try {
        $db = getDB();
        $roomTypeSysId = trim($_GET['room_type_sys_id'] ?? '');
        if (!$roomTypeSysId) jsonResponse(false, 'Missing room_type_sys_id.');

        $stmt = $db->prepare("SELECT * FROM room_prices WHERE room_type_sys_id = ?");
        $stmt->execute([$roomTypeSysId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        jsonResponse(true, 'ok', ['prices' => $row ? decodePrices($row) : null]);
    } catch (Exception $e) {
        jsonResponse(false, 'DB error.');
    }
}

// POST — admin only.
if ($method === 'POST') {
    requireAdmin();
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
    $db = getDB();
    $action = sanitize($_POST['action'] ?? '');

    // Upserts the ENTIRE board_prices array for one room type in one go —
    // the modal always submits every checked row together, not one at a time.
    if ($action === 'save') {
        $roomTypeSysId = trim($_POST['room_type_sys_id'] ?? '');
        $rowsRaw = json_decode($_POST['board_prices'] ?? '[]', true);
        if ($roomTypeSysId === '') jsonResponse(false, 'Missing room_type_sys_id.');
        if (!is_array($rowsRaw)) $rowsRaw = [];

        $parentCheck = $db->prepare("SELECT id FROM room_types WHERE sys_id = ?");
        $parentCheck->execute([$roomTypeSysId]);
        if (!$parentCheck->fetch()) jsonResponse(false, 'Room type not found.');

        $clean = [];
        foreach ($rowsRaw as $r) {
            $title = sanitize($r['board_type_title'] ?? '');
            $from  = sanitize($r['valid_from'] ?? '');
            $to    = sanitize($r['valid_to'] ?? '');
            $price = (float) ($r['price'] ?? -1);
            if ($title === '' || $from === '' || $to === '' || $price < 0) continue; // skip incomplete rows
            if (strtotime($from) === false || strtotime($to) === false || strtotime($to) < strtotime($from)) continue;
            $clean[] = [
                'board_type_sys_id' => !empty($r['board_type_sys_id']) ? sanitize($r['board_type_sys_id']) : null,
                'board_type_title'  => $title,
                'valid_from'        => $from,
                'valid_to'          => $to,
                'price'             => $price,
            ];
        }

        $existing = $db->prepare("SELECT sys_id, metadata FROM room_prices WHERE room_type_sys_id = ?");
        $existing->execute([$roomTypeSysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        $boardPricesJson = json_encode($clean, JSON_UNESCAPED_UNICODE);

        if ($row) {
            $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
            $meta['updated_at'] = date('Y-m-d H:i:s');
            $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';
            $db->prepare("UPDATE room_prices SET board_prices=?, metadata=? WHERE room_type_sys_id=?")
               ->execute([$boardPricesJson, json_encode($meta), $roomTypeSysId]);
            jsonResponse(true, 'Prices saved.', ['sys_id' => $row['sys_id']]);
        } else {
            $ids = generateSysIdAndUuid($db, 'room_prices');
            $now = date('Y-m-d H:i:s');
            $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);
            $db->prepare("INSERT INTO room_prices (sys_id, uuid, room_type_sys_id, board_prices, metadata) VALUES (?,?,?,?,?)")
               ->execute([$ids['sys_id'], $ids['uuid'], $roomTypeSysId, $boardPricesJson, $metadata]);
            jsonResponse(true, 'Prices saved.', ['sys_id' => $ids['sys_id']]);
        }
    }

    if ($action === 'toggle') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE room_prices SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    if ($action === 'delete') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("DELETE FROM room_prices WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Prices deleted.');
    }

    jsonResponse(false, 'Unknown action.');
}