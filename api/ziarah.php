<?php
// FILE PATH: /api/ziarah.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

function decodeZiarah(array $z): array {
    $z['itinerary']        = json_decode($z['itinerary'], true) ?: [];
    $z['route_sys_ids']    = json_decode($z['route_sys_ids'], true) ?: [];
    $z['moyallem_enabled'] = (bool) $z['moyallem_enabled'];
    return $z;
}

/**
 * Lightweight allowlist sanitizer for admin-authored rich-text HTML
 * (Ziarah itinerary item descriptions). Strips everything except a small
 * set of safe formatting tags — no <script>, <style>, event handlers, or
 * arbitrary attributes can survive this, even though the write path is
 * admin-only.
 */
function sanitizeRichText(string $html): string {
    $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><a><h3><h4><blockquote>';
    $clean = strip_tags($html, $allowedTags);
    // Strip any attributes except href on <a> tags — removes onclick, style,
    // javascript: hrefs, etc. from whatever survived strip_tags().
    $clean = preg_replace_callback('/<(\w+)([^>]*)>/', function ($m) {
        $tag = strtolower($m[1]);
        if ($tag === 'a') {
            if (preg_match('/href\s*=\s*"([^"]*)"/i', $m[2], $hrefMatch)
                && preg_match('#^https?://#i', $hrefMatch[1])) {
                return '<a href="' . htmlspecialchars($hrefMatch[1], ENT_QUOTES, 'UTF-8') . '">';
            }
            return '<a>';
        }
        return '<' . $tag . '>';
    }, $clean);
    return trim($clean);
}

// GET — public (used by both builders). Also resolves route_sys_ids into
// lightweight route summaries (sys_id, slug, origin, destinations) so the
// front-end can show "Makkah → Madinah → Makkah" without a second round-trip.
if ($method === 'GET') {
    try {
        $db = getDB();
        $onlyActive = !isAdminLoggedIn();
        $sql = "SELECT * FROM ziarah" . ($onlyActive ? " WHERE is_active = 1" : "") . " ORDER BY sort_order, name";
        $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rows = array_map('decodeZiarah', $rows);

        // Collect all referenced route sys_ids across every Ziarah, fetch once.
        $allRouteSysIds = [];
        foreach ($rows as $z) $allRouteSysIds = array_merge($allRouteSysIds, $z['route_sys_ids']);
        $allRouteSysIds = array_values(array_unique(array_filter($allRouteSysIds)));

        $routesBySysId = [];
        if ($allRouteSysIds) {
            $placeholders = implode(',', array_fill(0, count($allRouteSysIds), '?'));
            $rstmt = $db->prepare("SELECT sys_id, slug, origin, destinations FROM transport_routes WHERE sys_id IN ($placeholders)");
            $rstmt->execute($allRouteSysIds);
            foreach ($rstmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $stops = json_decode($r['destinations'], true) ?: [];
                $routesBySysId[$r['sys_id']] = [
                    'sys_id' => $r['sys_id'],
                    'slug' => $r['slug'],
                    'label' => implode(' → ', array_merge([$r['origin']], $stops)),
                ];
            }
        }

        foreach ($rows as &$z) {
            $z['routes'] = array_values(array_filter(array_map(
                fn($rsid) => $routesBySysId[$rsid] ?? null,
                $z['route_sys_ids']
            )));
        }
        unset($z);

        jsonResponse(true, 'ok', ['ziarah' => $rows]);
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
        $sysId       = trim($_POST['sys_id'] ?? '');
        $name        = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $duration    = sanitize($_POST['possible_duration'] ?? '');
        $moyallemOn  = isset($_POST['moyallem_enabled']) && $_POST['moyallem_enabled'] === '1' ? 1 : 0;
        $price       = (float) ($_POST['price'] ?? 0);
        $sortOrd     = (int) ($_POST['sort_order'] ?? 0);

        $itineraryRaw   = json_decode($_POST['itinerary'] ?? '[]', true);
        $routeSysIdsRaw = json_decode($_POST['route_sys_ids'] ?? '[]', true);

        if ($name === '') jsonResponse(false, 'Name is required.');

        // Itinerary items: title required, description allowed to contain
        // basic rich-text markup (already produced by a client-side editor) —
        // sanitize() would strip tags entirely, so a dedicated allowlist
        // sanitizer is used instead to keep formatting while blocking
        // scripts, event handlers, and any non-http(s) links.
        $cleanItinerary = [];
        if (is_array($itineraryRaw)) {
            foreach ($itineraryRaw as $item) {
                $title = trim(sanitize($item['title'] ?? ''));
                $html  = sanitizeRichText((string) ($item['description'] ?? ''));
                if ($title !== '') $cleanItinerary[] = ['title' => $title, 'description' => $html];
            }
        }

        $cleanRouteSysIds = [];
        if (is_array($routeSysIdsRaw)) {
            foreach ($routeSysIdsRaw as $rsid) {
                $rsid = sanitize((string) $rsid);
                if ($rsid !== '') $cleanRouteSysIds[] = $rsid;
            }
        }

        $itJson = json_encode($cleanItinerary, JSON_UNESCAPED_UNICODE);
        $rtJson = json_encode(array_values(array_unique($cleanRouteSysIds)), JSON_UNESCAPED_UNICODE);

        if ($action === 'create') {
            $ids = generateSysIdAndUuid($db, 'ziarah');
            $now = date('Y-m-d H:i:s');
            $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

            try {
                $db->prepare("INSERT INTO ziarah
                    (sys_id, uuid, name, description, possible_duration, itinerary, route_sys_ids, moyallem_enabled, price, sort_order, metadata)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?)")
                   ->execute([$ids['sys_id'], $ids['uuid'], $name, $description, $duration, $itJson, $rtJson, $moyallemOn, $price, $sortOrd, $metadata]);
                jsonResponse(true, 'Ziarah tour added.', ['sys_id' => $ids['sys_id']]);
            } catch (PDOException $e) {
                jsonResponse(false, 'Save failed.');
            }
        }

        // update
        if ($sysId === '') jsonResponse(false, 'Missing Ziarah sys_id.');
        $existing = $db->prepare("SELECT metadata FROM ziarah WHERE sys_id = ?");
        $existing->execute([$sysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Ziarah tour not found.');

        $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
        $meta['updated_at'] = date('Y-m-d H:i:s');
        $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

        $db->prepare("UPDATE ziarah SET name=?, description=?, possible_duration=?, itinerary=?, route_sys_ids=?, moyallem_enabled=?, price=?, sort_order=?, metadata=? WHERE sys_id=?")
           ->execute([$name, $description, $duration, $itJson, $rtJson, $moyallemOn, $price, $sortOrd, json_encode($meta), $sysId]);
        jsonResponse(true, 'Ziarah tour updated.');
    }

    if ($action === 'toggle') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE ziarah SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    if ($action === 'delete') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("DELETE FROM ziarah WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Ziarah tour deleted.');
    }

    jsonResponse(false, 'Unknown action.');
}