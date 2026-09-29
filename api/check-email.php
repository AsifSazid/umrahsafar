<?php
// FILE PATH: /api/check-email.php
// Used by the register form to check (as the user types/blurs the
// field) whether an email is already registered in `users`.
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

header('Content-Type: application/json');

$email = filter_var(trim($_GET['email'] ?? $_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
if (!$email) jsonResponse(false, 'Invalid email format.');
$excludeUuid = trim($_GET['exclude_user_uuid'] ?? $_POST['exclude_user_uuid'] ?? '');

try {
    $db = getDB();
    $sql = "SELECT id FROM users WHERE email = :e" . ($excludeUuid ? " AND uuid != :ex" : "") . " LIMIT 1";
    $stmt = $db->prepare($sql);
    $params = [':e' => $email];
    if ($excludeUuid) $params[':ex'] = $excludeUuid;
    $stmt->execute($params);
    $exists = (bool) $stmt->fetch();
    jsonResponse(true, $exists ? 'Email already registered.' : 'Email available.', ['exists' => $exists]);
} catch (Exception $e) {
    jsonResponse(false, 'Could not check email right now.');
}