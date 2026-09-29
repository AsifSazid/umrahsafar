<?php
// FILE PATH: /api/packages-handler.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// GET — public (for package builder & listing)
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    try {
        $db = getDB();
        if ($id > 0) {
            $stmt = $db->prepare("SELECT * FROM packages WHERE id = :id AND is_active = 1");
            $stmt->execute([':id' => $id]);
            $pkg = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$pkg) jsonResponse(false, 'Package not found.');
            $pkg['includes'] = json_decode($pkg['includes_json'] ?? '[]', true);
            $pkg['excludes'] = json_decode($pkg['excludes_json'] ?? '[]', true);
            $pkg['itinerary'] = json_decode($pkg['itinerary_json'] ?? '[]', true);
            jsonResponse(true, 'ok', ['package' => $pkg]);
        } else {
            $rows = $db->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r['includes'] = json_decode($r['includes_json'] ?? '[]', true);
                $r['excludes'] = json_decode($r['excludes_json'] ?? '[]', true);
                $r['itinerary'] = json_decode($r['itinerary_json'] ?? '[]', true);
            }
            jsonResponse(true, 'ok', ['packages' => $rows]);
        }
    } catch (Exception $e) {
        jsonResponse(false, 'DB error.');
    }
}

// POST — admin only
if ($method === 'POST') {
    requireAdmin();
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');

    $action = sanitize($_POST['action'] ?? '');
    $db = getDB();

    if ($action === 'create' || $action === 'update') {
        $name      = sanitize($_POST['name'] ?? '');
        $slug      = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
        $duration  = (int)($_POST['duration'] ?? 7);
        $budget    = in_array($_POST['budget'] ?? '', ['economy','premium','luxury']) ? $_POST['budget'] : 'economy';
        $stars     = max(1, min(5, (int)($_POST['stars'] ?? 3)));
        $price_sar = (float)($_POST['price_sar'] ?? 0);
        $badge     = sanitize($_POST['badge'] ?? '');
        $rating    = min(5.0, max(0, (float)($_POST['rating'] ?? 4.5)));
        $reviews   = (int)($_POST['reviews'] ?? 0);
        $city      = sanitize($_POST['city'] ?? 'Makkah & Madinah');
        $flight    = sanitize($_POST['flight'] ?? '');
        $hotel     = sanitize($_POST['hotel'] ?? '');
        $transport = sanitize($_POST['transport'] ?? '');
        $includes  = $_POST['includes'] ?? [];
        $excludes  = $_POST['excludes'] ?? [];
        $itinerary = $_POST['itinerary'] ?? [];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $sort_order= (int)($_POST['sort_order'] ?? 0);

        // Build itinerary from parallel arrays
        $itin = [];
        if (isset($_POST['itin_day'])) {
            foreach ($_POST['itin_day'] as $i => $day) {
                $itin[] = [
                    'day'   => (int)$day,
                    'title' => sanitize($_POST['itin_title'][$i] ?? ''),
                    'desc'  => sanitize($_POST['itin_desc'][$i]  ?? ''),
                ];
            }
        }

        $includes_j  = json_encode(array_filter(array_map('trim', (array)$includes)));
        $excludes_j  = json_encode(array_filter(array_map('trim', (array)$excludes)));
        $itinerary_j = json_encode($itin);

        if ($action === 'create') {
            // Make slug unique
            $baseSlug = $slug;
            $i = 1;
            while ($db->prepare("SELECT id FROM packages WHERE slug = :s")->execute([':s' => $slug]) &&
                   $db->prepare("SELECT id FROM packages WHERE slug = :s")->execute([':s' => $slug]) &&
                   $db->query("SELECT COUNT(*) FROM packages WHERE slug = '$slug'")->fetchColumn() > 0) {
                $slug = $baseSlug . '-' . $i++;
            }
            $db->prepare("INSERT INTO packages (name,slug,duration,budget,stars,price_sar,badge,rating,reviews,city,flight,hotel,transport,includes_json,excludes_json,itinerary_json,is_active,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$name,$slug,$duration,$budget,$stars,$price_sar,$badge,$rating,$reviews,$city,$flight,$hotel,$transport,$includes_j,$excludes_j,$itinerary_j,$is_active,$sort_order]);
            jsonResponse(true, 'Package created.', ['id' => $db->lastInsertId()]);
        } else {
            $id = (int)($_POST['id'] ?? 0);
            $db->prepare("UPDATE packages SET name=?,duration=?,budget=?,stars=?,price_sar=?,badge=?,rating=?,reviews=?,city=?,flight=?,hotel=?,transport=?,includes_json=?,excludes_json=?,itinerary_json=?,is_active=?,sort_order=?,updated_at=NOW() WHERE id=?")
               ->execute([$name,$duration,$budget,$stars,$price_sar,$badge,$rating,$reviews,$city,$flight,$hotel,$transport,$includes_j,$excludes_j,$itinerary_j,$is_active,$sort_order,$id]);
            jsonResponse(true, 'Package updated.');
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM packages WHERE id = :id")->execute([':id' => $id]);
        jsonResponse(true, 'Package deleted.');
    }

    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("UPDATE packages SET is_active = NOT is_active WHERE id = :id")->execute([':id' => $id]);
        jsonResponse(true, 'Status toggled.');
    }

    jsonResponse(false, 'Unknown action.');
}
