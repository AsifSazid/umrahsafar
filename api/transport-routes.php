<?php
// FILE PATH: /api/transport-routes.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

function slugify(string $origin, array $stops): string {
    $parts = array_merge([$origin], $stops);
    $parts = array_filter(array_map(fn($p) => preg_replace('/[^A-Za-z0-9]+/', '', $p), $parts));
    return implode('-', $parts);
}

function decodeRoute(array $r): array {
    $r['destinations']    = json_decode($r['destinations'], true) ?: [];
    $r['vehicle_options'] = json_decode($r['vehicle_options'], true) ?: [];
    return $r;
}

// GET — public (package builder consumes this)
if ($method === 'GET') {
    try {
        $db = getDB();
        $routes = $db->query("SELECT * FROM transport_routes WHERE is_active = 1 ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
        $routes = array_map('decodeRoute', $routes);
        jsonResponse(true, 'ok', ['routes' => $routes]);
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

    if ($action === 'create_route' || $action === 'update_route') {
        $sysId   = trim($_POST['sys_id'] ?? '');
        $origin  = sanitize($_POST['origin'] ?? '');
        $stops   = json_decode($_POST['destinations'] ?? '[]', true);
        $voptRaw = json_decode($_POST['vehicle_options'] ?? '[]', true);
        $sortOrd = (int) ($_POST['sort_order'] ?? 0);

        if ($origin === '') jsonResponse(false, 'Origin is required.');
        if (!is_array($stops) || count($stops) === 0) jsonResponse(false, 'At least one destination is required.');
        $stops = array_map('sanitize', $stops);

        if (!is_array($voptRaw)) $voptRaw = [];
        $vehicleOptions = [];
        foreach ($voptRaw as $v) {
            $vehicleSysId = sanitize($v['vehicle_type_sys_id'] ?? '');
            $price        = (float) ($v['price'] ?? 0);
            if ($vehicleSysId !== '') $vehicleOptions[] = ['vehicle_type_sys_id' => $vehicleSysId, 'price' => $price];
        }

        $slug     = slugify($origin, $stops);
        $destJson = json_encode($stops, JSON_UNESCAPED_UNICODE);
        $voptJson = json_encode($vehicleOptions, JSON_UNESCAPED_UNICODE);

        if ($action === 'create_route') {
            $check = $db->prepare("SELECT id FROM transport_routes WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetch()) jsonResponse(false, "A route with slug \"$slug\" already exists.");

            $ids = generateSysIdAndUuid($db, 'transport_routes');
            $now = date('Y-m-d H:i:s');
            $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

            $db->prepare("INSERT INTO transport_routes
                (sys_id, uuid, slug, origin, destinations, vehicle_options, sort_order, metadata)
                VALUES (?,?,?,?,?,?,?,?)")
               ->execute([$ids['sys_id'], $ids['uuid'], $slug, $origin, $destJson, $voptJson, $sortOrd, $metadata]);
            jsonResponse(true, 'Route created.', ['sys_id' => $ids['sys_id']]);
        }

        // update_route
        if ($sysId === '') jsonResponse(false, 'Missing route sys_id.');
        $check = $db->prepare("SELECT id FROM transport_routes WHERE slug = ? AND sys_id != ?");
        $check->execute([$slug, $sysId]);
        if ($check->fetch()) jsonResponse(false, "Another route already uses slug \"$slug\".");

        $existing = $db->prepare("SELECT metadata FROM transport_routes WHERE sys_id = ?");
        $existing->execute([$sysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Route not found.');
        $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
        $meta['updated_at'] = date('Y-m-d H:i:s');
        $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

        $db->prepare("UPDATE transport_routes SET slug=?, origin=?, destinations=?, vehicle_options=?, sort_order=?, metadata=? WHERE sys_id=?")
           ->execute([$slug, $origin, $destJson, $voptJson, $sortOrd, json_encode($meta), $sysId]);
        jsonResponse(true, 'Route updated.');
    }

    if ($action === 'delete_route') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("DELETE FROM transport_routes WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Route deleted.');
    }

    if ($action === 'toggle_route') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE transport_routes SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    jsonResponse(false, 'Unknown action.');
}