<?php
// FILE PATH: /api/admin-change-password.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Method not allowed.');
if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
$cur  = $_POST['current_password'] ?? '';
$new  = $_POST['new_password'] ?? '';
$conf = $_POST['confirm_password'] ?? '';
if ($new !== $conf)   jsonResponse(false, 'Passwords do not match.');
if (strlen($new) < 6) jsonResponse(false, 'Password must be at least 6 characters.');
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['admin_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || !password_verify($cur, $row['password_hash']))
        jsonResponse(false, 'Current password is incorrect.');
    $hash = password_hash($new, PASSWORD_BCRYPT);
    $db->prepare("UPDATE admin_users SET password_hash = :h WHERE id = :id")
       ->execute([':h' => $hash, ':id' => $_SESSION['admin_id']]);
    jsonResponse(true, 'Password updated successfully.');
} catch (Exception $e) {
    jsonResponse(false, 'Failed to update password.');
}
