<?php
// FILE PATH: /api/users.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') jsonResponse(false, 'Method not allowed.');

requireAdmin();
if (!canAccessTab('users')) jsonResponse(false, 'Not authorized.');
if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');

$db = getDB();
$action = sanitize($_POST['action'] ?? '');

if ($action === 'toggle_active') {
    $userUuid = trim($_POST['user'] ?? '');
    if (!$userUuid) jsonResponse(false, 'Missing user.');

    $stmt = $db->prepare("SELECT id, is_active FROM users WHERE uuid = ?");
    $stmt->execute([$userUuid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) jsonResponse(false, 'User not found.');

    // Safety: an admin can't block their own account (avoids an
    // accidental self-lockout with no one else able to undo it).
    if ((int) $row['id'] === (int) ($_SESSION['admin_id'] ?? 0)) {
        jsonResponse(false, "You can't block your own account.");
    }

    $newState = ((int) $row['is_active'] === 1) ? 0 : 1;
    $db->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$newState, $row['id']]);

    jsonResponse(true, $newState ? 'User unblocked.' : 'User blocked.', ['is_active' => $newState]);
}

if ($action === 'update') {
    $userUuid = trim($_POST['user'] ?? '');
    $name     = sanitize($_POST['name'] ?? '');
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone    = sanitize($_POST['phone'] ?? '');

    if (!$userUuid) jsonResponse(false, 'Missing user.');
    if (!$name || !$email) jsonResponse(false, 'Name and valid email are required.');

    $target = $db->prepare("SELECT id, metadata FROM users WHERE uuid = ?");
    $target->execute([$userUuid]);
    $existing = $target->fetch(PDO::FETCH_ASSOC);
    if (!$existing) jsonResponse(false, 'User not found.');
    $userId = $existing['id']; // internal PK, used only for the actual UPDATE/duplicate-exclude below

    $exists = $db->prepare("SELECT id FROM users WHERE id != :id AND (email = :e" . ($phone !== '' ? " OR phone = :p" : "") . ")");
    $params = [':id' => $userId, ':e' => $email];
    if ($phone !== '') $params[':p'] = $phone;
    $exists->execute($params);
    $dupe = $exists->fetch(PDO::FETCH_ASSOC);
    if ($dupe) {
        // Distinguish which field collided so the message is useful —
        // a second quick lookup, but only runs on the rare duplicate path.
        $emailDupe = $db->prepare("SELECT id FROM users WHERE id != :id AND email = :e");
        $emailDupe->execute([':id' => $userId, ':e' => $email]);
        jsonResponse(false, $emailDupe->fetch() ? 'This email is already registered to another user.' : 'This phone number is already registered to another user.');
    }

    $meta = json_decode($existing['metadata'] ?? '{}', true) ?: [];
    $meta['updated_at'] = date('Y-m-d H:i:s');
    $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';

    $db->prepare("UPDATE users SET name=?, email=?, phone=?, metadata=? WHERE id=?")
       ->execute([$name, $email, $phone ?: null, json_encode($meta), $userId]);

    jsonResponse(true, 'User updated.');
}

jsonResponse(false, 'Unknown action.');