<?php
// FILE PATH: /api/visa-types.php
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
        $sql = "SELECT * FROM visa_types" . ($onlyActive ? " WHERE is_active = 1" : "") . " ORDER BY sort_order, name";
        $types = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(true, 'ok', ['visa_types' => $types]);
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

    if ($action === 'create') {
        $name        = sanitize($_POST['name'] ?? '');
        $price       = (float) ($_POST['price'] ?? 0);
        $forUmrah    = !empty($_POST['for_umrah']) ? 1 : 0;
        $description = sanitize($_POST['description'] ?? '');
        $icon        = sanitize($_POST['icon'] ?? 'file-text');
        if ($name === '') jsonResponse(false, 'Name is required.');

        $ids = generateSysIdAndUuid($db, 'visa_types');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

        try {
            $db->prepare("INSERT INTO visa_types (sys_id, uuid, name, price, for_umrah, description, icon, sort_order, metadata) VALUES (?,?,?,?,?,?,?,?,?)")
               ->execute([$ids['sys_id'], $ids['uuid'], $name, $price, $forUmrah, $description, $icon, (int) ($_POST['sort_order'] ?? 0), $metadata]);
            jsonResponse(true, 'Visa type added.', ['sys_id' => $ids['sys_id']]);
        } catch (PDOException $e) {
            jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That visa type already exists.' : 'Save failed.');
        }
    }

    if ($action === 'update') {
        $sysId       = trim($_POST['sys_id'] ?? '');
        $name        = sanitize($_POST['name'] ?? '');
        $price       = (float) ($_POST['price'] ?? 0);
        $forUmrah    = !empty($_POST['for_umrah']) ? 1 : 0;
        $description = sanitize($_POST['description'] ?? '');
        $icon        = sanitize($_POST['icon'] ?? 'file-text');
        if ($name === '' || $sysId === '') jsonResponse(false, 'Name is required.');

        $existing = $db->prepare("SELECT metadata FROM visa_types WHERE sys_id = ?");
        $existing->execute([$sysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Visa type not found.');

        $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
        $meta['updated_at'] = date('Y-m-d H:i:s');
        $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

        try {
            $db->prepare("UPDATE visa_types SET name=?, price=?, for_umrah=?, description=?, icon=?, sort_order=?, metadata=? WHERE sys_id=?")
               ->execute([$name, $price, $forUmrah, $description, $icon, (int) ($_POST['sort_order'] ?? 0), json_encode($meta), $sysId]);
            jsonResponse(true, 'Visa type updated.');
        } catch (PDOException $e) {
            jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That visa type already exists.' : 'Save failed.');
        }
    }

    // Fast, single-field update — for the "just change the price" admin workflow
    if ($action === 'quick_price') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $price = (float) ($_POST['price'] ?? -1);
        if ($sysId === '' || $price < 0) jsonResponse(false, 'Invalid price.');
        $db->prepare("UPDATE visa_types SET price = ? WHERE sys_id = ?")->execute([$price, $sysId]);
        jsonResponse(true, 'Price updated.');
    }

    if ($action === 'toggle') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE visa_types SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    if ($action === 'toggle_for_umrah') {
        $sysId = trim($_POST['sys_id'] ?? '');
        if ($sysId === '') jsonResponse(false, 'Missing sys_id.');
        $db->prepare("UPDATE visa_types SET for_umrah = NOT for_umrah WHERE sys_id = ?")->execute([$sysId]);
        $newState = $db->prepare("SELECT for_umrah FROM visa_types WHERE sys_id = ?");
        $newState->execute([$sysId]);
        jsonResponse(true, 'Toggled.', ['for_umrah' => (int) $newState->fetchColumn()]);
    }

    if ($action === 'delete') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("DELETE FROM visa_types WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Visa type deleted.');
    }

    jsonResponse(false, 'Unknown action.');
}