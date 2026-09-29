<?php
// FILE PATH: /api/room-board-types.php
// GLOBAL catalog CRUD (Room Only / Bed & Breakfast / Half Board / Full
// Board, or any admin-defined text) — not tied to any one room type or
// hotel. Every room type's Prices modal offers this same catalog.
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// GET — list the whole global catalog (public: used by the package
// builders; admin screen also uses this same list via its own page load).
if ($method === 'GET') {
    try {
        $db = getDB();
        $onlyActive = !isAdminLoggedIn();
        $sql = "SELECT * FROM room_board_types" . ($onlyActive ? " WHERE is_active = 1" : "") . " ORDER BY sort_order, name";
        jsonResponse(true, 'ok', ['board_types' => $db->query($sql)->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        jsonResponse(false, 'DB error.');
    }
}

// POST — admin only. Identified by sys_id.
if ($method === 'POST') {
    requireAdmin();
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
    $db = getDB();
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'create') {
        $name    = sanitize($_POST['name'] ?? '');
        $sortOrd = (int) ($_POST['sort_order'] ?? 0);
        if ($name === '') jsonResponse(false, 'Board type name is required.');

        $ids = generateSysIdAndUuid($db, 'room_board_types');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

        try {
            $db->prepare("INSERT INTO room_board_types (sys_id, uuid, name, sort_order, metadata) VALUES (?,?,?,?,?)")
               ->execute([$ids['sys_id'], $ids['uuid'], $name, $sortOrd, $metadata]);
            jsonResponse(true, 'Board type added.', ['sys_id' => $ids['sys_id']]);
        } catch (PDOException $e) {
            jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That board type already exists.' : 'Save failed.');
        }
    }

    if ($action === 'update') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $name  = sanitize($_POST['name'] ?? '');
        if ($sysId === '' || $name === '') jsonResponse(false, 'Name is required.');

        $existing = $db->prepare("SELECT metadata FROM room_board_types WHERE sys_id = ?");
        $existing->execute([$sysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Board type not found.');

        $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
        $meta['updated_at'] = date('Y-m-d H:i:s');
        $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

        try {
            $db->prepare("UPDATE room_board_types SET name=?, metadata=? WHERE sys_id=?")
               ->execute([$name, json_encode($meta), $sysId]);
            jsonResponse(true, 'Board type updated.');
        } catch (PDOException $e) {
            jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That board type already exists.' : 'Save failed.');
        }
    }

    if ($action === 'toggle') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE room_board_types SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    if ($action === 'delete') {
        $sysId = trim($_POST['sys_id'] ?? '');
        // room_prices no longer has a real FK to this table (its FK is on
        // room_type_sys_id, not board types — board_prices just embeds
        // board_type_sys_id inside a JSON array), so a delete here does
        // NOT cascade automatically. Guard against deleting a board type
        // that's still priced on at least one room.
        $inUse = $db->prepare("SELECT COUNT(*) FROM room_prices WHERE JSON_SEARCH(board_prices, 'one', ?) IS NOT NULL");
        $inUse->execute([$sysId]);
        if ((int) $inUse->fetchColumn() > 0) {
            jsonResponse(false, "Can't delete — this board type is priced on one or more rooms. Remove it from those rooms' prices first.");
        }
        $db->prepare("DELETE FROM room_board_types WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Board type deleted.');
    }

    jsonResponse(false, 'Unknown action.');
}