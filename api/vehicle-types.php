<?php
// FILE PATH: /api/vehicle-types.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

const VEHICLE_IMAGE_DIR    = 'storage/vehicle-types/images';
const VEHICLE_IMAGE_MIMES  = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

// GET — public (used by package builder + route editor)
if ($method === 'GET') {
    try {
        $db = getDB();
        $onlyActive = !isAdminLoggedIn(); // admin screen needs inactive ones too
        $sql = "SELECT * FROM vehicle_types" . ($onlyActive ? " WHERE is_active = 1" : "") . " ORDER BY sort_order, name";
        $types = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($types as &$t) {
            $t['capacities'] = json_decode($t['capacities'] ?? 'null', true);
            $t['images']     = json_decode($t['images'] ?? '[]', true) ?: [];
        }
        unset($t);
        jsonResponse(true, 'ok', ['vehicle_types' => $types]);
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

    // Reads seat/luggage fields and returns a clean capacities JSON
    // string, or null if neither was given.
    $buildCapacities = function (): ?string {
        $seat    = $_POST['capacity_seat'] ?? '';
        $luggage = $_POST['capacity_luggage'] ?? '';
        if ($seat === '' && $luggage === '') return null;
        return json_encode(['seat' => (int) $seat, 'luggage' => (int) $luggage]);
    };

    if ($action === 'create') {
        $name = sanitize($_POST['name'] ?? '');
        $icon = sanitize($_POST['icon'] ?? 'car');
        if ($name === '') jsonResponse(false, 'Name is required.');

        $ids = generateSysIdAndUuid($db, 'vehicle_types');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

        try {
            $db->prepare("INSERT INTO vehicle_types (sys_id, uuid, name, icon, capacities, sort_order, metadata) VALUES (?,?,?,?,?,?,?)")
               ->execute([$ids['sys_id'], $ids['uuid'], $name, $icon ?: 'car', $buildCapacities(), (int) ($_POST['sort_order'] ?? 0), $metadata]);
            jsonResponse(true, 'Vehicle type added.', ['sys_id' => $ids['sys_id']]);
        } catch (PDOException $e) {
            jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That vehicle type already exists.' : 'Save failed.');
        }
    }

    if ($action === 'update') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $name  = sanitize($_POST['name'] ?? '');
        $icon  = sanitize($_POST['icon'] ?? 'car');
        if ($name === '' || $sysId === '') jsonResponse(false, 'Name is required.');

        $existing = $db->prepare("SELECT metadata FROM vehicle_types WHERE sys_id = ?");
        $existing->execute([$sysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Vehicle type not found.');

        $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
        $meta['updated_at'] = date('Y-m-d H:i:s');
        $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

        try {
            $db->prepare("UPDATE vehicle_types SET name=?, icon=?, capacities=?, sort_order=?, metadata=? WHERE sys_id=?")
               ->execute([$name, $icon ?: 'car', $buildCapacities(), (int) ($_POST['sort_order'] ?? 0), json_encode($meta), $sysId]);
            jsonResponse(true, 'Vehicle type updated.');
        } catch (PDOException $e) {
            jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That vehicle type already exists.' : 'Save failed.');
        }
    }

    if ($action === 'toggle') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE vehicle_types SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    if ($action === 'delete') {
        $sysId = trim($_POST['sys_id'] ?? '');
        // Guard: don't delete a vehicle type still referenced inside any
        // route's vehicle_options JSON (matched by sys_id now, not name).
        $inUse = $db->prepare("SELECT COUNT(*) FROM transport_routes WHERE JSON_SEARCH(vehicle_options, 'one', ?) IS NOT NULL");
        $inUse->execute([$sysId]);
        if ((int) $inUse->fetchColumn() > 0) {
            jsonResponse(false, "Can't delete — this vehicle type is used in one or more routes. Remove it from those routes first.");
        }

        // Clean up any uploaded images on disk before removing the row.
        $imgRow = $db->prepare("SELECT images FROM vehicle_types WHERE sys_id = ?");
        $imgRow->execute([$sysId]);
        $images = json_decode($imgRow->fetchColumn() ?: '[]', true) ?: [];
        foreach ($images as $path) {
            $full = dirname(__DIR__) . '/' . ltrim($path, '/');
            if (is_file($full)) @unlink($full);
        }

        $db->prepare("DELETE FROM vehicle_types WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Vehicle type deleted.');
    }

    if ($action === 'reorder') {
        $order = json_decode($_POST['order'] ?? '[]', true); // array of sys_ids, in new order
        if (is_array($order)) {
            $stmt = $db->prepare("UPDATE vehicle_types SET sort_order = ? WHERE sys_id = ?");
            foreach ($order as $i => $sysId) $stmt->execute([$i, $sysId]);
        }
        jsonResponse(true, 'Reordered.');
    }

    // Upload one or more images for a vehicle type. Any number allowed
    // (no cap), any size allowed (no limit) — only mime type is checked.
    if ($action === 'upload_image') {
        $sysId = trim($_POST['sys_id'] ?? '');
        if ($sysId === '') jsonResponse(false, 'Missing sys_id.');
        if (empty($_FILES['images'])) jsonResponse(false, 'No files uploaded.');

        $row = $db->prepare("SELECT images FROM vehicle_types WHERE sys_id = ?");
        $row->execute([$sysId]);
        $current = $row->fetch(PDO::FETCH_ASSOC);
        if (!$current) jsonResponse(false, 'Vehicle type not found.');
        $images = json_decode($current['images'] ?? '[]', true) ?: [];

        $dirAbs = dirname(__DIR__) . '/' . VEHICLE_IMAGE_DIR;
        if (!is_dir($dirAbs)) mkdir($dirAbs, 0755, true); // path missing? build it

        // Normalize to a list of files whether one or multiple were sent
        $files = $_FILES['images'];
        $count = is_array($files['name']) ? count($files['name']) : 1;
        $isMulti = is_array($files['name']);

        $nextIndex = count($images) + 1;
        $saved = [];
        $errors = [];

        for ($i = 0; $i < $count; $i++) {
            $tmpPath = $isMulti ? $files['tmp_name'][$i] : $files['tmp_name'];
            $error   = $isMulti ? $files['error'][$i]    : $files['error'];
            $origName = $isMulti ? $files['name'][$i]    : $files['name'];

            if ($error !== UPLOAD_ERR_OK) { $errors[] = "$origName: upload error."; continue; }

            $mime = mime_content_type($tmpPath);
            if (!isset(VEHICLE_IMAGE_MIMES[$mime])) {
                $errors[] = "$origName: only JPG/JPEG/PNG images are allowed.";
                continue;
            }
            $ext = VEHICLE_IMAGE_MIMES[$mime];

            $filename = "{$sysId}-{$nextIndex}.{$ext}";
            $destAbs  = $dirAbs . '/' . $filename;
            $destRel  = VEHICLE_IMAGE_DIR . '/' . $filename;

            if (move_uploaded_file($tmpPath, $destAbs)) {
                $images[] = $destRel;
                $saved[]  = $destRel;
                $nextIndex++;
            } else {
                $errors[] = "$origName: could not save file.";
            }
        }

        if ($saved) {
            $db->prepare("UPDATE vehicle_types SET images = ? WHERE sys_id = ?")
               ->execute([json_encode($images), $sysId]);
        }

        if ($saved && !$errors) {
            jsonResponse(true, count($saved) . ' image(s) uploaded.', ['images' => $images]);
        } elseif ($saved && $errors) {
            jsonResponse(true, count($saved) . ' uploaded, ' . count($errors) . ' failed: ' . implode(' ', $errors), ['images' => $images]);
        } else {
            jsonResponse(false, implode(' ', $errors) ?: 'Upload failed.');
        }
    }

    // Remove one image — deletes both the DB reference AND the file on disk.
    if ($action === 'delete_image') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $path  = trim($_POST['path'] ?? '');
        if ($sysId === '' || $path === '') jsonResponse(false, 'Missing sys_id or path.');

        $row = $db->prepare("SELECT images FROM vehicle_types WHERE sys_id = ?");
        $row->execute([$sysId]);
        $current = $row->fetch(PDO::FETCH_ASSOC);
        if (!$current) jsonResponse(false, 'Vehicle type not found.');
        $images = json_decode($current['images'] ?? '[]', true) ?: [];

        $images = array_values(array_filter($images, fn($p) => $p !== $path));

        $db->prepare("UPDATE vehicle_types SET images = ? WHERE sys_id = ?")
           ->execute([json_encode($images), $sysId]);

        $full = dirname(__DIR__) . '/' . ltrim($path, '/');
        if (is_file($full)) @unlink($full);

        jsonResponse(true, 'Image removed.', ['images' => $images]);
    }

    jsonResponse(false, 'Unknown action.');
}