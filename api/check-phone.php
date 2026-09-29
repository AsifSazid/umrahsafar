<?php
// FILE PATH: /api/check-phone.php
// Used by the register form to check (as the user types/blurs the
// field) whether a phone number is already registered in `users`.
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

header('Content-Type: application/json');

$phone = sanitize($_GET['phone'] ?? $_POST['phone'] ?? '');
if (strlen($phone) < 6) jsonResponse(false, 'Invalid phone number.');
$excludeUuid = trim($_GET['exclude_user_uuid'] ?? $_POST['exclude_user_uuid'] ?? '');

try {
    $db = getDB();
    $sql = "SELECT id FROM users WHERE phone = :p" . ($excludeUuid ? " AND uuid != :ex" : "") . " LIMIT 1";
    $stmt = $db->prepare($sql);
    $params = [':p' => $phone];
    if ($excludeUuid) $params[':ex'] = $excludeUuid;
    $stmt->execute($params);
    $exists = (bool) $stmt->fetch();
    jsonResponse(true, $exists ? 'Phone number already registered.' : 'Phone number available.', ['exists' => $exists]);
} catch (Exception $e) {
    jsonResponse(false, 'Could not check phone number right now.');
}