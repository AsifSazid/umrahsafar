<?php
// FILE PATH: /data/seed/super-admin.php
// Run this ONCE (php seed-super-admin.php, or visit it once in the browser
// then delete it) after users_table.sql and system_roles.sql have been
// imported. It calls PHP's password_hash() live — the actual hash is never
// hand-typed into a .sql file.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$name     = 'Super Admin';
$username = 'superadmin';
$email    = 'superadmin@travhub.com.bd';
$password = '11223344'; // CHANGE THIS immediately after first login

$hash = password_hash($password, PASSWORD_BCRYPT); // ← the actual function call

try {
    $db = getDB();

    $roleSysId = $db->query("SELECT sys_id FROM system_roles WHERE role_alias = 'super-admin'")->fetchColumn();
    if (!$roleSysId) {
        die("system_roles has no 'super-admin' row yet — import system_roles.sql first.\n");
    }

    $exists = $db->prepare("SELECT id FROM users WHERE email = ?");
    $exists->execute([$email]);
    if ($exists->fetch()) {
        die("A user with email {$email} already exists — nothing to do.\n");
    }

    $ids = generateSysIdAndUuid($db, 'users');
    $stmt = $db->prepare("
        INSERT INTO users (sys_id, uuid, role_sys_ids, active_role_sys_id, name, username, email, password_hash, is_verified, metadata)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
    ");
    $stmt->execute([
        $ids['sys_id'],
        $ids['uuid'],
        json_encode([$roleSysId]),
        $roleSysId,
        $name,
        $username,
        $email,
        $hash,
        json_encode(['created_at' => date('Y-m-d H:i:s'), 'created_by' => 'system', 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => 'system']),
    ]);

    echo "Super admin created.\n";
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}