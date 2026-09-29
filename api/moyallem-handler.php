<?php
// FILE PATH: /api/moyallem-handler.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

function decodeMoyallem(array $m): array {
    $m['category_prices'] = json_decode($m['category_prices'], true) ?: [];
    return $m;
}

// GET — public (used by both builders)
if ($method === 'GET') {
    try {
        $db = getDB();
        $onlyActive = !isAdminLoggedIn();
        $sql = "SELECT * FROM moyallem_services" . ($onlyActive ? " WHERE is_active = 1" : "") . " ORDER BY sort_order, name";
        $services = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $services = array_map('decodeMoyallem', $services);
        jsonResponse(true, 'ok', ['services' => $services]);
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
        $sysId   = trim($_POST['sys_id'] ?? '');
        $name    = sanitize($_POST['name'] ?? '');
        $desc    = sanitize($_POST['description'] ?? '');
        $icon    = sanitize($_POST['icon'] ?? 'star');
        $sortOrd = (int) ($_POST['sort_order'] ?? 0);
        $prices  = json_decode($_POST['category_prices'] ?? '[]', true);

        if ($name === '') jsonResponse(false, 'Service name is required.');
        if (!is_array($prices)) $prices = [];
        $cleanPrices = [];
        foreach ($prices as $p) {
            $cat   = sanitize($p['category'] ?? '');
            $price = (float) ($p['price'] ?? 0);
            if ($cat !== '') $cleanPrices[] = ['category' => $cat, 'price' => $price];
        }
        if (!$cleanPrices) jsonResponse(false, 'At least one category (e.g. General/Expert/VIP) with a price is required.');
        $pJson = json_encode($cleanPrices, JSON_UNESCAPED_UNICODE);

        if ($action === 'create') {
            $ids = generateSysIdAndUuid($db, 'moyallem_services');
            $now = date('Y-m-d H:i:s');
            $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

            try {
                $db->prepare("INSERT INTO moyallem_services (sys_id, uuid, name, description, icon, category_prices, sort_order, metadata) VALUES (?,?,?,?,?,?,?,?)")
                   ->execute([$ids['sys_id'], $ids['uuid'], $name, $desc, $icon ?: 'star', $pJson, $sortOrd, $metadata]);
                jsonResponse(true, 'Moyallem service added.', ['sys_id' => $ids['sys_id']]);
            } catch (PDOException $e) {
                jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That service already exists.' : 'Save failed.');
            }
        }

        // update
        if ($sysId === '') jsonResponse(false, 'Missing service sys_id.');
        $existing = $db->prepare("SELECT metadata FROM moyallem_services WHERE sys_id = ?");
        $existing->execute([$sysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Moyallem service not found.');

        $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
        $meta['updated_at'] = date('Y-m-d H:i:s');
        $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

        $db->prepare("UPDATE moyallem_services SET name=?, description=?, icon=?, category_prices=?, sort_order=?, metadata=? WHERE sys_id=?")
           ->execute([$name, $desc, $icon ?: 'star', $pJson, $sortOrd, json_encode($meta), $sysId]);
        jsonResponse(true, 'Moyallem service updated.');
    }

    // Fast, single-price update for one category within a service's JSON — mirrors flights/meals' quick_price
    if ($action === 'quick_price') {
        $sysId    = trim($_POST['sys_id'] ?? '');
        $category = sanitize($_POST['category'] ?? '');
        $price    = (float) ($_POST['price'] ?? -1);
        if ($sysId === '' || $category === '' || $price < 0) jsonResponse(false, 'Invalid price.');

        $stmt = $db->prepare("SELECT category_prices FROM moyallem_services WHERE sys_id = ?");
        $stmt->execute([$sysId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Moyallem service not found.');

        $prices = json_decode($row['category_prices'], true) ?: [];
        $found = false;
        foreach ($prices as &$p) {
            if ($p['category'] === $category) { $p['price'] = $price; $found = true; break; }
        }
        unset($p);
        if (!$found) jsonResponse(false, "Category \"$category\" not found on this service.");

        $db->prepare("UPDATE moyallem_services SET category_prices = ? WHERE sys_id = ?")
           ->execute([json_encode($prices, JSON_UNESCAPED_UNICODE), $sysId]);
        jsonResponse(true, 'Price updated.');
    }

    if ($action === 'toggle') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE moyallem_services SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    if ($action === 'delete') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("DELETE FROM moyallem_services WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Moyallem service deleted.');
    }

    jsonResponse(false, 'Unknown action.');
}