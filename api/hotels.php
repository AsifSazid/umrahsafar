<?php
// FILE PATH: /api/hotels.php
// Handles BOTH hotels and room_types — they're always created/edited
// together on admin/hotel-create.php, so one API keeps that flow simple.
// room_board_types and room_prices have their own APIs (added later,
// from admin/hotels.php's accordion view).
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

const HOTEL_IMAGE_DIR    = 'storage/hotels/images';
const HOTEL_VIDEO_DIR    = 'storage/hotels/videos';
const ROOM_IMAGE_DIR     = 'storage/room-types/images';
const ROOM_VIDEO_DIR     = 'storage/room-types/videos';
const ALLOWED_IMAGE_MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
const ALLOWED_VIDEO_MIMES = ['video/mp4' => 'mp4', 'video/webm' => 'webm'];

function decodeHotel(array $h): array {
    foreach (['checkin_schedule', 'distance_info', 'age_ranges', 'image_urls', 'images', 'videos', 'youtube_urls'] as $f) {
        $h[$f] = json_decode($h[$f] ?? 'null', true);
    }
    return $h;
}
function decodeRoomType(array $r): array {
    foreach (['person_capacity', 'bed_config', 'amenities', 'add_on', 'view_info', 'size', 'image_urls', 'images', 'videos', 'youtube_urls'] as $f) {
        $r[$f] = json_decode($r[$f] ?? 'null', true);
    }
    return $r;
}

// Shared upload handler for both hotels and room_types, images and videos.
// $sysId is the parent's sys_id, used for file naming ({sysId}-{n}.ext).
function handleMediaUpload(PDO $db, string $table, string $sysId, string $mediaColumn, string $dirRel, array $allowedMimes): void {
    $stmt = $db->prepare("SELECT $mediaColumn FROM $table WHERE sys_id = ?");
    $stmt->execute([$sysId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row === false) jsonResponse(false, 'Record not found.');
    $existing = json_decode($row[$mediaColumn] ?? '[]', true) ?: [];

    $dirAbs = dirname(__DIR__) . '/' . $dirRel;
    if (!is_dir($dirAbs)) mkdir($dirAbs, 0755, true);

    $files = $_FILES['files'] ?? null;
    if (!$files) jsonResponse(false, 'No files uploaded.');
    $count = is_array($files['name']) ? count($files['name']) : 1;
    $isMulti = is_array($files['name']);

    $nextIndex = count($existing) + 1;
    $saved = [];
    $errors = [];

    for ($i = 0; $i < $count; $i++) {
        $tmpPath  = $isMulti ? $files['tmp_name'][$i] : $files['tmp_name'];
        $error    = $isMulti ? $files['error'][$i]    : $files['error'];
        $origName = $isMulti ? $files['name'][$i]      : $files['name'];

        if ($error !== UPLOAD_ERR_OK) { $errors[] = "$origName: upload error."; continue; }

        $mime = mime_content_type($tmpPath);
        if (!isset($allowedMimes[$mime])) {
            $errors[] = "$origName: unsupported format.";
            continue;
        }
        $ext = $allowedMimes[$mime];
        $filename = "{$sysId}-{$nextIndex}.{$ext}";
        $destAbs  = $dirAbs . '/' . $filename;
        $destRel  = $dirRel . '/' . $filename;

        if (move_uploaded_file($tmpPath, $destAbs)) {
            $existing[] = $destRel;
            $saved[]    = $destRel;
            $nextIndex++;
        } else {
            $errors[] = "$origName: could not save file.";
        }
    }

    if ($saved) {
        $db->prepare("UPDATE $table SET $mediaColumn = ? WHERE sys_id = ?")->execute([json_encode($existing), $sysId]);
    }

    if ($saved && !$errors) {
        jsonResponse(true, count($saved) . ' file(s) uploaded.', [$mediaColumn => $existing]);
    } elseif ($saved && $errors) {
        jsonResponse(true, count($saved) . ' uploaded, ' . count($errors) . ' failed: ' . implode(' ', $errors), [$mediaColumn => $existing]);
    } else {
        jsonResponse(false, implode(' ', $errors) ?: 'Upload failed.');
    }
}

function handleMediaDelete(PDO $db, string $table, string $sysId, string $mediaColumn, string $path): void {
    $stmt = $db->prepare("SELECT $mediaColumn FROM $table WHERE sys_id = ?");
    $stmt->execute([$sysId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row === false) jsonResponse(false, 'Record not found.');
    $existing = json_decode($row[$mediaColumn] ?? '[]', true) ?: [];
    $existing = array_values(array_filter($existing, fn($p) => $p !== $path));

    $db->prepare("UPDATE $table SET $mediaColumn = ? WHERE sys_id = ?")->execute([json_encode($existing), $sysId]);

    $full = dirname(__DIR__) . '/' . ltrim($path, '/');
    if (is_file($full)) @unlink($full);

    jsonResponse(true, 'Removed.', [$mediaColumn => $existing]);
}

// Deletes every file in a stored path array from disk — used when an
// entire hotel/room_type row is being removed, so its media doesn't
// become orphaned (the DB row disappears via cascade, but the files
// on disk never get cleaned up on their own).
function unlinkStoredFiles(array $paths): void {
    foreach ($paths as $path) {
        $full = dirname(__DIR__) . '/' . ltrim($path, '/');
        if (is_file($full)) @unlink($full);
    }
}

// ── GET — public (used by the package builders) ──
if ($method === 'GET') {
    try {
        $db = getDB();
        $onlyActive = !isAdminLoggedIn();

        if (($_GET['scope'] ?? '') === 'room_types') {
            $hotelSysId = trim($_GET['hotel_sys_id'] ?? '');
            if (!$hotelSysId) jsonResponse(false, 'Missing hotel_sys_id.');
            $sql = "SELECT * FROM room_types WHERE hotel_sys_id = ?" . ($onlyActive ? " AND is_active = 1" : "") . " ORDER BY sort_order, name";
            $stmt = $db->prepare($sql);
            $stmt->execute([$hotelSysId]);
            $roomTypes = array_map('decodeRoomType', $stmt->fetchAll(PDO::FETCH_ASSOC));
            jsonResponse(true, 'ok', ['room_types' => $roomTypes]);
        }

        $city = trim($_GET['city'] ?? '');
        $star = trim($_GET['star'] ?? '');
        $sql = "SELECT * FROM hotels WHERE 1=1" . ($onlyActive ? " AND is_active = 1" : "") . ($city ? " AND city = :city" : "") . ($star ? " AND star_rating = :star" : "") . " ORDER BY sort_order, name";
        $stmt = $db->prepare($sql);
        if ($city) $stmt->bindValue(':city', $city);
        if ($star) $stmt->bindValue(':star', (int) $star, PDO::PARAM_INT);
        $stmt->execute();
        $hotels = array_map('decodeHotel', $stmt->fetchAll(PDO::FETCH_ASSOC));
        jsonResponse(true, 'ok', ['hotels' => $hotels]);
    } catch (Exception $e) {
        jsonResponse(false, 'DB error.');
    }
}

// ── POST — admin only ──
if ($method === 'POST') {
    requireAdmin();
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
    $db = getDB();
    $action = sanitize($_POST['action'] ?? '');

    // ═══ HOTEL actions ═══
    if ($action === 'create_hotel' || $action === 'update_hotel') {
        $sysId  = trim($_POST['sys_id'] ?? '');
        $name   = sanitize($_POST['name'] ?? '');
        $city   = sanitize($_POST['city'] ?? 'Makkah');
        $star   = (int) ($_POST['star_rating'] ?? 0) ?: null;

        if ($name === '') jsonResponse(false, 'Hotel name is required.');

        $checkinSchedule = json_encode([
            'in'  => sanitize($_POST['checkin_time'] ?? ''),
            'out' => sanitize($_POST['checkout_time'] ?? ''),
        ]);
        $distanceInfo = json_encode([
            'landmark' => sanitize($_POST['distance_landmark'] ?? ''),
            'gate_no'  => sanitize($_POST['distance_gate_no'] ?? ''),
            'unit'     => (($_POST['distance_unit'] ?? 'm') === 'km') ? 'km' : 'm',
            'value'    => (float) ($_POST['distance_value'] ?? 0),
            'walking'  => [
                'enabled' => !empty($_POST['walking_enabled']),
                'minutes' => (int) ($_POST['walking_minutes'] ?? 0),
            ],
        ]);
        $ageRanges = json_encode([
            'child'   => ['min' => (int) ($_POST['child_age_min'] ?? 0), 'max' => (int) ($_POST['child_age_max'] ?? 0)],
            'infant'  => ['min' => (int) ($_POST['infant_age_min'] ?? 0), 'max' => (int) ($_POST['infant_age_max'] ?? 0)],
        ]);
        $imageUrls   = json_encode(array_values(array_filter(json_decode($_POST['image_urls'] ?? '[]', true) ?: [])));
        $youtubeUrls = json_encode(array_values(array_filter(json_decode($_POST['youtube_urls'] ?? '[]', true) ?: [])));

        $description = sanitize($_POST['description'] ?? '');
        $address     = sanitize($_POST['address'] ?? '');
        $phone       = sanitize($_POST['phone'] ?? '');
        $email       = sanitize($_POST['email'] ?? '');
        $sortOrd     = (int) ($_POST['sort_order'] ?? 0);

        if ($action === 'create_hotel') {
            $ids = generateSysIdAndUuid($db, 'hotels');
            $now = date('Y-m-d H:i:s');
            $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

            $db->prepare("INSERT INTO hotels
                (sys_id, uuid, city, star_rating, name, checkin_schedule, distance_info, age_ranges, description, address, phone, email, image_urls, youtube_urls, sort_order, metadata)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$ids['sys_id'], $ids['uuid'], $city, $star, $name, $checkinSchedule, $distanceInfo, $ageRanges, $description, $address, $phone, $email, $imageUrls, $youtubeUrls, $sortOrd, $metadata]);
            jsonResponse(true, 'Hotel created.', ['sys_id' => $ids['sys_id']]);
        }

        if ($sysId === '') jsonResponse(false, 'Missing hotel sys_id.');
        $existing = $db->prepare("SELECT metadata FROM hotels WHERE sys_id = ?");
        $existing->execute([$sysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Hotel not found.');
        $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
        $meta['updated_at'] = date('Y-m-d H:i:s');
        $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

        $db->prepare("UPDATE hotels SET city=?, star_rating=?, name=?, checkin_schedule=?, distance_info=?, age_ranges=?, description=?, address=?, phone=?, email=?, image_urls=?, youtube_urls=?, sort_order=?, metadata=? WHERE sys_id=?")
           ->execute([$city, $star, $name, $checkinSchedule, $distanceInfo, $ageRanges, $description, $address, $phone, $email, $imageUrls, $youtubeUrls, $sortOrd, json_encode($meta), $sysId]);
        jsonResponse(true, 'Hotel updated.');
    }

    if ($action === 'toggle_hotel') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE hotels SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    if ($action === 'delete_hotel') {
        $sysId = trim($_POST['sys_id'] ?? '');

        // Clean up files on disk before the row (and its cascading children)
        // disappear from the DB — once deleted we'd have no paths left to find.
        $hotelMedia = $db->prepare("SELECT images, videos FROM hotels WHERE sys_id = ?");
        $hotelMedia->execute([$sysId]);
        if ($row = $hotelMedia->fetch(PDO::FETCH_ASSOC)) {
            unlinkStoredFiles(json_decode($row['images'] ?? '[]', true) ?: []);
            unlinkStoredFiles(json_decode($row['videos'] ?? '[]', true) ?: []);
        }
        $roomMedia = $db->prepare("SELECT images, videos FROM room_types WHERE hotel_sys_id = ?");
        $roomMedia->execute([$sysId]);
        foreach ($roomMedia->fetchAll(PDO::FETCH_ASSOC) as $rt) {
            unlinkStoredFiles(json_decode($rt['images'] ?? '[]', true) ?: []);
            unlinkStoredFiles(json_decode($rt['videos'] ?? '[]', true) ?: []);
        }

        // room_types (and, transitively, room_board_types → room_prices)
        // cascade-delete automatically via real FK constraints.
        $db->prepare("DELETE FROM hotels WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Hotel deleted.');
    }

    // ═══ ROOM TYPE actions ═══
    if ($action === 'create_room_type' || $action === 'update_room_type') {
        $sysId       = trim($_POST['sys_id'] ?? '');
        $hotelSysId  = trim($_POST['hotel_sys_id'] ?? '');
        $name        = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $sizeUnit    = in_array($_POST['size_unit'] ?? '', ['sqr-m','sqr-cm','sqr-ft','sqr-in'], true) ? $_POST['size_unit'] : 'sqr-m';
        $size        = json_encode(['unit' => $sizeUnit, 'value' => (float) ($_POST['size_value'] ?? 0)]);
        $sortOrd     = (int) ($_POST['sort_order'] ?? 0);

        if ($name === '') jsonResponse(false, 'Room type name is required.');

        $personCapacity = json_encode(['adults' => (int) ($_POST['capacity_adults'] ?? 0), 'children' => (int) ($_POST['capacity_children'] ?? 0)]);
        $bedConfigRaw = json_decode($_POST['bed_config'] ?? '[]', true);
        $cleanBedConfig = [];
        if (is_array($bedConfigRaw)) {
            foreach ($bedConfigRaw as $b) {
                $type = sanitize($b['bed_type'] ?? '');
                if ($type === '') continue;
                $cleanBedConfig[] = ['bed_type' => $type, 'quantity' => (int) ($b['quantity'] ?? 0), 'capacity_per_bed' => (int) ($b['capacity_per_bed'] ?? 0)];
            }
        }
        $amenitiesRaw = json_decode($_POST['amenities'] ?? '[]', true);
        $amenities = is_array($amenitiesRaw) ? array_values(array_filter(array_map('sanitize', $amenitiesRaw))) : [];
        $addOn = json_encode(['bed' => !empty($_POST['add_on_bed']) ? 1 : 0, 'charge' => (float) ($_POST['add_on_charge'] ?? 0)]);
        $viewInfo = json_encode(['enabled' => !empty($_POST['view_enabled']) ? 1 : 0, 'description' => sanitize($_POST['view_description'] ?? '')]);
        $imageUrls   = json_encode(array_values(array_filter(json_decode($_POST['image_urls'] ?? '[]', true) ?: [])));
        $youtubeUrls = json_encode(array_values(array_filter(json_decode($_POST['youtube_urls'] ?? '[]', true) ?: [])));

        if ($action === 'create_room_type') {
            if ($hotelSysId === '') jsonResponse(false, 'Missing hotel_sys_id.');
            $parentCheck = $db->prepare("SELECT id FROM hotels WHERE sys_id = ?");
            $parentCheck->execute([$hotelSysId]);
            if (!$parentCheck->fetch()) jsonResponse(false, 'Hotel not found.');

            $ids = generateSysIdAndUuid($db, 'room_types');
            $now = date('Y-m-d H:i:s');
            $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

            $db->prepare("INSERT INTO room_types
                (sys_id, uuid, hotel_sys_id, name, description, person_capacity, size, bed_config, amenities, add_on, view_info, image_urls, youtube_urls, sort_order, metadata)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$ids['sys_id'], $ids['uuid'], $hotelSysId, $name, $description, $personCapacity, $size, json_encode($cleanBedConfig), json_encode($amenities), $addOn, $viewInfo, $imageUrls, $youtubeUrls, $sortOrd, $metadata]);
            jsonResponse(true, 'Room type added.', ['sys_id' => $ids['sys_id']]);
        }

        if ($sysId === '') jsonResponse(false, 'Missing room type sys_id.');
        $existing = $db->prepare("SELECT metadata FROM room_types WHERE sys_id = ?");
        $existing->execute([$sysId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);
        if (!$row) jsonResponse(false, 'Room type not found.');
        $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
        $meta['updated_at'] = date('Y-m-d H:i:s');
        $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

        $db->prepare("UPDATE room_types SET name=?, description=?, person_capacity=?, size=?, bed_config=?, amenities=?, add_on=?, view_info=?, image_urls=?, youtube_urls=?, sort_order=?, metadata=? WHERE sys_id=?")
           ->execute([$name, $description, $personCapacity, $size, json_encode($cleanBedConfig), json_encode($amenities), $addOn, $viewInfo, $imageUrls, $youtubeUrls, $sortOrd, json_encode($meta), $sysId]);
        jsonResponse(true, 'Room type updated.');
    }

    if ($action === 'toggle_room_type') {
        $sysId = trim($_POST['sys_id'] ?? '');
        $db->prepare("UPDATE room_types SET is_active = NOT is_active WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Toggled.');
    }

    if ($action === 'delete_room_type') {
        $sysId = trim($_POST['sys_id'] ?? '');

        // Clean up files on disk before the row disappears.
        $mediaRow = $db->prepare("SELECT images, videos FROM room_types WHERE sys_id = ?");
        $mediaRow->execute([$sysId]);
        if ($row = $mediaRow->fetch(PDO::FETCH_ASSOC)) {
            unlinkStoredFiles(json_decode($row['images'] ?? '[]', true) ?: []);
            unlinkStoredFiles(json_decode($row['videos'] ?? '[]', true) ?: []);
        }

        // room_board_types (and, transitively, room_prices) cascade-delete
        // automatically via real FK constraints.
        $db->prepare("DELETE FROM room_types WHERE sys_id = ?")->execute([$sysId]);
        jsonResponse(true, 'Room type deleted.');
    }

    // ═══ MEDIA actions (shared handlers above, for both hotels & room_types) ═══
    if ($action === 'upload_hotel_image') { handleMediaUpload($db, 'hotels', trim($_POST['sys_id'] ?? ''), 'images', HOTEL_IMAGE_DIR, ALLOWED_IMAGE_MIMES); }
    if ($action === 'upload_hotel_video') { handleMediaUpload($db, 'hotels', trim($_POST['sys_id'] ?? ''), 'videos', HOTEL_VIDEO_DIR, ALLOWED_VIDEO_MIMES); }
    if ($action === 'delete_hotel_image') { handleMediaDelete($db, 'hotels', trim($_POST['sys_id'] ?? ''), 'images', trim($_POST['path'] ?? '')); }
    if ($action === 'delete_hotel_video') { handleMediaDelete($db, 'hotels', trim($_POST['sys_id'] ?? ''), 'videos', trim($_POST['path'] ?? '')); }

    if ($action === 'upload_room_type_image') { handleMediaUpload($db, 'room_types', trim($_POST['sys_id'] ?? ''), 'images', ROOM_IMAGE_DIR, ALLOWED_IMAGE_MIMES); }
    if ($action === 'upload_room_type_video') { handleMediaUpload($db, 'room_types', trim($_POST['sys_id'] ?? ''), 'videos', ROOM_VIDEO_DIR, ALLOWED_VIDEO_MIMES); }
    if ($action === 'delete_room_type_image') { handleMediaDelete($db, 'room_types', trim($_POST['sys_id'] ?? ''), 'images', trim($_POST['path'] ?? '')); }
    if ($action === 'delete_room_type_video') { handleMediaDelete($db, 'room_types', trim($_POST['sys_id'] ?? ''), 'videos', trim($_POST['path'] ?? '')); }

    jsonResponse(false, 'Unknown action.');
}