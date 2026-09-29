<?php
// FILE PATH: /api/meals.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

function decodeMeal(array $m): array {
    $m['price_tiers'] = json_decode($m['price_tiers'], true) ?: [];
    return $m;
}

// GET — public (used by both builders)
if ($method === 'GET') {
    try {
        $db = getDB();
        $onlyActive = !isAdminLoggedIn();
        $sql = "SELECT * FROM meals" . ($onlyActive ? " WHERE is_active = 1" : "") . " ORDER BY sort_order, name";
        $meals = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $meals = array_map('decodeMeal', $meals);
        jsonResponse(true, 'ok', ['meals' => $meals]);
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
        $sysId      = trim($_POST['sys_id'] ?? '');
        $name       = sanitize($_POST['name'] ?? 'Meal Package');
        $minAdults  = max(1, (int) ($_POST['min_adults_required'] ?? 10));
        $sortOrd    = (int) ($_POST['sort_order'] ?? 0);
        $tiersRaw   = json_decode($_POST['price_tiers'] ?? '[]', true);

        if (!is_array($tiersRaw)) $tiersRaw = [];
        $cleanTiers = [];
        foreach ($tiersRaw as $t) {
            $tier  = sanitize($t['tier'] ?? '');
            $price = (float) ($t['price'] ?? 0);
            if ($tier !== '') $cleanTiers[] = ['tier' => $tier, 'price' => $price];
        }
        if (!$cleanTiers) jsonResponse(false, 'At least one tier (e.g. Economy/Premium/Luxury) with a price is required.');
        $tJson = json_encode($cleanTiers, JSON_UNESCAPED_UNICODE);

        if ($action === 'create') {
            $ids = generateSysIdAndUuid($db, 'meals');
            $now = date('Y-m-d H:i:s');
            $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

            try {
                $db->prepare("INSERT INTO meals (sys_id, uuid, name, min_adults_required, price_tiers, sort_order, metadata) VALUES (?,?,?,?,?,?,?)")
                   ->execute([$ids['sys_id'], $ids['uuid'], $name, $minAdults, $tJson, $sortOrd, $metadata]);
                jsonResponse(true, 'Meal plan added.', ['sys_id' => $ids['sys_id']]);
            } catch (PDOException $e) {
                jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That meal plan already exists.' : 'Save failed.');
            }
        }

        // update
        if ($sysId === '') jsonResponse(false, 'Missing meal sys_id.');
        $existing = $db->prepare("SELECT metadata FROM meals WHERE sys_id = ?");
        $existing->execute([$sysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Meal plan not found.');

        $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
        $meta['updated_at'] = date('Y-m-d H:i:s');
        $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

        $db->prepare("UPDATE meals SET name=?, min_adults_required=?, price_tiers=?, sort_order=?, metadata=? WHERE sys_id=?")
           ->execute([$name, $minAdults, $tJson, $sortOrd, json_encode($meta), $sysId]);
        jsonResponse(true, 'Meal plan updated.');
    }

    // Fast, single-price update for one tier within a meal's JSON — mirrors service_levels/visa_types' quick_price
    if ($action === 'quick_price') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $tier  = sanitize($_POST['tier'] ?? '');
        $price = (float) ($_POST['price'] ?? -1);
        if ($sysId === '' || $tier === '' || $price < 0) jsonResponse(false, 'Invalid price.');

        $stmt = $db->prepare("SELECT price_tiers FROM meals WHERE sys_id = ?");
        $stmt->execute([$sysId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Meal plan not found.');

        $tiers = json_decode($row['price_tiers'], true) ?: [];
        $found = false;
        foreach ($tiers as &$t) {
            if ($t['tier'] === $tier) { $t['price'] = $price; $found = true; break; }
        }
        unset($t);
        if (!$found) jsonResponse(false, "Tier \"$tier\" not found on this meal plan.");

        $db->prepare("UPDATE meals SET price_tiers = ? WHERE sys_id = ?")
           ->execute([json_encode($tiers, JSON_UNESCAPED_UNICODE), $sysId]);
        jsonResponse(true, 'Price updated.');
    }

    if ($action === 'toggle') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE meals SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    if ($action === 'delete') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("DELETE FROM meals WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Meal plan deleted.');
    }

    jsonResponse(false, 'Unknown action.');
}