<?php
// FILE PATH: /api/flights.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

function decodeFlight(array $f): array {
    $f['type_prices'] = json_decode($f['type_prices'], true) ?: [];
    return $f;
}

// GET — public (used by both builders)
if ($method === 'GET') {
    try {
        $db = getDB();
        $onlyActive = !isAdminLoggedIn();
        $sql = "SELECT * FROM flights" . ($onlyActive ? " WHERE is_active = 1" : "") . " ORDER BY sort_order, fare_type";
        $flights = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $flights = array_map('decodeFlight', $flights);
        jsonResponse(true, 'ok', ['flights' => $flights]);
    } catch (Exception $e) {
        jsonResponse(false, 'DB error.');
    }
}

// POST — admin only. Identified by sys_id (never the numeric id) —
// consistent with every other admin CRUD page in this project.
if ($method === 'POST') {
    requireAdmin();
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
    $db = getDB();
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'create' || $action === 'update') {
        $sysId    = trim($_POST['sys_id'] ?? '');
        $fareType = sanitize($_POST['fare_type'] ?? '');
        $note     = sanitize($_POST['note'] ?? 'Price may differ based on season and availability.');
        $sortOrd  = (int) ($_POST['sort_order'] ?? 0);
        $prices   = json_decode($_POST['type_prices'] ?? '[]', true);

        if ($fareType === '') jsonResponse(false, 'Fare type name is required.');
        if (!is_array($prices)) $prices = [];
        $cleanPrices = [];
        foreach ($prices as $p) {
            $type  = sanitize($p['type'] ?? '');
            $price = (float) ($p['price'] ?? 0);
            if ($type !== '') $cleanPrices[] = ['type' => $type, 'price' => $price];
        }
        if (!$cleanPrices) jsonResponse(false, 'At least one connection type (e.g. Direct/Connecting) with a price is required.');
        $pJson = json_encode($cleanPrices, JSON_UNESCAPED_UNICODE);

        if ($action === 'create') {
            $ids = generateSysIdAndUuid($db, 'flights');
            $now = date('Y-m-d H:i:s');
            $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

            try {
                $db->prepare("INSERT INTO flights (sys_id, uuid, fare_type, type_prices, note, sort_order, metadata) VALUES (?,?,?,?,?,?,?)")
                   ->execute([$ids['sys_id'], $ids['uuid'], $fareType, $pJson, $note, $sortOrd, $metadata]);
                jsonResponse(true, 'Flight fare added.', ['sys_id' => $ids['sys_id']]);
            } catch (PDOException $e) {
                jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That fare type already exists.' : 'Save failed.');
            }
        }

        // update
        if ($sysId === '') jsonResponse(false, 'Missing flight sys_id.');
        $existing = $db->prepare("SELECT metadata FROM flights WHERE sys_id = ?");
        $existing->execute([$sysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Flight fare not found.');

        $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
        $meta['updated_at'] = date('Y-m-d H:i:s');
        $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

        $db->prepare("UPDATE flights SET fare_type=?, type_prices=?, note=?, sort_order=?, metadata=? WHERE sys_id=?")
           ->execute([$fareType, $pJson, $note, $sortOrd, json_encode($meta), $sysId]);
        jsonResponse(true, 'Flight fare updated.');
    }

    // Fast, single-price update for one connection type within a fare's JSON — mirrors meals/visa's quick_price
    if ($action === 'quick_price') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $type  = sanitize($_POST['type'] ?? '');
        $price = (float) ($_POST['price'] ?? -1);
        if ($sysId === '' || $type === '' || $price < 0) jsonResponse(false, 'Invalid price.');

        $stmt = $db->prepare("SELECT type_prices FROM flights WHERE sys_id = ?");
        $stmt->execute([$sysId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Flight fare not found.');

        $prices = json_decode($row['type_prices'], true) ?: [];
        $found = false;
        foreach ($prices as &$p) {
            if ($p['type'] === $type) { $p['price'] = $price; $found = true; break; }
        }
        unset($p);
        if (!$found) jsonResponse(false, "Connection type \"$type\" not found on this fare.");

        $db->prepare("UPDATE flights SET type_prices = ? WHERE sys_id = ?")
           ->execute([json_encode($prices, JSON_UNESCAPED_UNICODE), $sysId]);
        jsonResponse(true, 'Price updated.');
    }

    if ($action === 'toggle') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE flights SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    if ($action === 'delete') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("DELETE FROM flights WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Flight fare deleted.');
    }

    jsonResponse(false, 'Unknown action.');
}