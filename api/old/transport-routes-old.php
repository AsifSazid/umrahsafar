<?php
// FILE PATH: /api/transport-routes.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$VEHICLES = ['Car','HiAce','Coaster','Bus','H1','GMC','Staria','Train'];

// GET — public (for package builder)
if ($method === 'GET') {
    try {
        $db = getDB();
        $routes = $db->query("SELECT * FROM transport_routes WHERE is_active = 1 ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($routes as &$r) {
            $prices = $db->prepare("SELECT vehicle_type, price_sar FROM transport_prices WHERE route_id = :rid AND is_active = 1");
            $prices->execute([':rid' => $r['id']]);
            $r['prices'] = [];
            foreach ($prices->fetchAll(PDO::FETCH_ASSOC) as $p) {
                $r['prices'][$p['vehicle_type']] = (float)$p['price_sar'];
            }
        }
        jsonResponse(true, 'ok', ['routes' => $routes]);
    } catch (Exception $e) {
        jsonResponse(false, 'DB error.');
    }
}

// POST — admin only
if ($method === 'POST') {
    requireAdmin();
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
    $db = getDB();
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'create_route') {
        $from = sanitize($_POST['from_loc'] ?? '');
        $to   = sanitize($_POST['to_loc'] ?? '');
        $name = $from . ' → ' . $to;
        $db->prepare("INSERT INTO transport_routes (route_name, from_loc, to_loc, sort_order) VALUES (?,?,?,?)")
           ->execute([$name, $from, $to, (int)($_POST['sort_order'] ?? 0)]);
        $rid = $db->lastInsertId();
        // Insert prices
        foreach ($VEHICLES as $v) {
            $price = (float)($_POST['price_' . strtolower(str_replace('/', '', $v))] ?? 0);
            $db->prepare("INSERT INTO transport_prices (route_id, vehicle_type, price_sar) VALUES (?,?,?)")
               ->execute([$rid, $v, $price]);
        }
        jsonResponse(true, 'Route created.', ['id' => $rid]);
    }

    if ($action === 'update_route') {
        $id   = (int)($_POST['id'] ?? 0);
        $from = sanitize($_POST['from_loc'] ?? '');
        $to   = sanitize($_POST['to_loc'] ?? '');
        $name = $from . ' → ' . $to;
        $db->prepare("UPDATE transport_routes SET route_name=?, from_loc=?, to_loc=?, sort_order=? WHERE id=?")
           ->execute([$name, $from, $to, (int)($_POST['sort_order'] ?? 0), $id]);
        foreach ($VEHICLES as $v) {
            $price = (float)($_POST['price_' . strtolower(str_replace('/', '', $v))] ?? 0);
            $db->prepare("INSERT INTO transport_prices (route_id, vehicle_type, price_sar) VALUES (?,?,?) ON DUPLICATE KEY UPDATE price_sar=VALUES(price_sar)")
               ->execute([$id, $v, $price]);
        }
        jsonResponse(true, 'Route updated.');
    }

    if ($action === 'delete_route') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM transport_routes WHERE id = :id")->execute([':id' => $id]);
        jsonResponse(true, 'Route deleted.');
    }

    if ($action === 'toggle_route') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("UPDATE transport_routes SET is_active = NOT is_active WHERE id = :id")->execute([':id' => $id]);
        jsonResponse(true, 'Toggled.');
    }

    jsonResponse(false, 'Unknown action.');
}
