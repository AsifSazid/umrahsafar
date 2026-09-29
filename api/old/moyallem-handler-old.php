<?php
// FILE PATH: /api/moyallem-handler.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $db = getDB();
        $rows = $db->query("SELECT * FROM moyallem_services WHERE is_active = 1 ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(true, 'ok', ['services' => $rows]);
    } catch (Exception $e) {
        jsonResponse(false, 'DB error.');
    }
}

if ($method === 'POST') {
    requireAdmin();
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
    $db = getDB();
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'create') {
        $name  = sanitize($_POST['name'] ?? '');
        $desc  = sanitize($_POST['description'] ?? '');
        $price = (float)($_POST['price_sar'] ?? 0);
        $icon  = sanitize($_POST['icon'] ?? 'star');
        $sort  = (int)($_POST['sort_order'] ?? 0);
        $db->prepare("INSERT INTO moyallem_services (name, description, price_sar, icon, sort_order) VALUES (?,?,?,?,?)")
           ->execute([$name, $desc, $price, $icon, $sort]);
        jsonResponse(true, 'Moyallem service created.', ['id' => $db->lastInsertId()]);
    }

    if ($action === 'update') {
        $id    = (int)($_POST['id'] ?? 0);
        $name  = sanitize($_POST['name'] ?? '');
        $desc  = sanitize($_POST['description'] ?? '');
        $price = (float)($_POST['price_sar'] ?? 0);
        $icon  = sanitize($_POST['icon'] ?? 'star');
        $sort  = (int)($_POST['sort_order'] ?? 0);
        $db->prepare("UPDATE moyallem_services SET name=?, description=?, price_sar=?, icon=?, sort_order=?, updated_at=NOW() WHERE id=?")
           ->execute([$name, $desc, $price, $icon, $sort, $id]);
        jsonResponse(true, 'Service updated.');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM moyallem_services WHERE id = :id")->execute([':id' => $id]);
        jsonResponse(true, 'Service deleted.');
    }

    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("UPDATE moyallem_services SET is_active = NOT is_active WHERE id = :id")->execute([':id' => $id]);
        jsonResponse(true, 'Toggled.');
    }

    jsonResponse(false, 'Unknown action.');
}
