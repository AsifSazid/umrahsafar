<?php
// FILE PATH: /api/service-levels.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// GET — public (used by the package builders)
if ($method === 'GET') {
    try {
        $db = getDB();
        $onlyActive = !isAdminLoggedIn();
        $sql = "SELECT * FROM service_levels" . ($onlyActive ? " WHERE is_active = 1" : "") . " ORDER BY sort_order, name";
        $levels = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($levels as &$l) {
            $l['features'] = json_decode($l['features'] ?? '[]', true) ?: [];
        }
        unset($l);
        jsonResponse(true, 'ok', ['service_levels' => $levels]);
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

    // Features arrive as newline-separated text from the admin form —
    // convert to a clean JSON array of trimmed, non-empty lines.
    $parseFeatures = function (string $raw): string {
        $lines = array_filter(array_map('trim', explode("\n", $raw)), fn($l) => $l !== '');
        return json_encode(array_values($lines));
    };

    if ($action === 'create') {
        $name     = sanitize($_POST['name'] ?? '');
        $price    = (float) ($_POST['price'] ?? 0);
        $features = $parseFeatures($_POST['features'] ?? '');
        if ($name === '') jsonResponse(false, 'Name is required.');

        $ids = generateSysIdAndUuid($db, 'service_levels');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

        try {
            $db->prepare("INSERT INTO service_levels (sys_id, uuid, name, price, features, sort_order, metadata) VALUES (?,?,?,?,?,?,?)")
               ->execute([$ids['sys_id'], $ids['uuid'], $name, $price, $features, (int) ($_POST['sort_order'] ?? 0), $metadata]);
            jsonResponse(true, 'Service level added.', ['sys_id' => $ids['sys_id']]);
        } catch (PDOException $e) {
            jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That service level already exists.' : 'Save failed.');
        }
    }

    if ($action === 'update') {
        $sysId    = trim($_POST['sys_id'] ?? '');
        $name     = sanitize($_POST['name'] ?? '');
        $price    = (float) ($_POST['price'] ?? 0);
        $features = $parseFeatures($_POST['features'] ?? '');
        if ($name === '' || $sysId === '') jsonResponse(false, 'Name is required.');

        $existing = $db->prepare("SELECT metadata FROM service_levels WHERE sys_id = ?");
        $existing->execute([$sysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Service level not found.');

        $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
        $meta['updated_at'] = date('Y-m-d H:i:s');
        $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

        try {
            $db->prepare("UPDATE service_levels SET name=?, price=?, features=?, sort_order=?, metadata=? WHERE sys_id=?")
               ->execute([$name, $price, $features, (int) ($_POST['sort_order'] ?? 0), json_encode($meta), $sysId]);
            jsonResponse(true, 'Service level updated.');
        } catch (PDOException $e) {
            jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That service level already exists.' : 'Save failed.');
        }
    }

    // Fast, single-field update — for the "just change the price" admin workflow
    if ($action === 'quick_price') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $price = (float) ($_POST['price'] ?? -1);
        if ($sysId === '' || $price < 0) jsonResponse(false, 'Invalid price.');
        $db->prepare("UPDATE service_levels SET price = ? WHERE sys_id = ?")->execute([$price, $sysId]);
        jsonResponse(true, 'Price updated.');
    }

    if ($action === 'toggle') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE service_levels SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    if ($action === 'delete') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("DELETE FROM service_levels WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Service level deleted.');
    }

    if ($action === 'reorder') {
        $order = json_decode($_POST['order'] ?? '[]', true); // array of sys_ids, in new order
        if (is_array($order)) {
            $stmt = $db->prepare("UPDATE service_levels SET sort_order = ? WHERE sys_id = ?");
            foreach ($order as $i => $sysId) $stmt->execute([$i, $sysId]);
        }
        jsonResponse(true, 'Reordered.');
    }

    jsonResponse(false, 'Unknown action.');
}