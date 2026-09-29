<?php
// FILE PATH: /data/seed/system-roles.php
// Run this ONCE, right after system-roles.sql, and BEFORE
// seed-super-admin.php (which needs the 'super-admin' role to exist).
// Uses generateSysIdAndUuid() so sys_id/uuid come from the real
// generator — keeps sys_id_counters in sync for whatever role gets
// added next from an admin panel later.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

$roles = [
    ['name' => 'Super Admin', 'role_alias' => 'super-admin'],
    ['name' => 'Admin',       'role_alias' => 'admin'],
    ['name' => 'Guest',       'role_alias' => 'guest'],
    ['name' => 'Client',      'role_alias' => 'client'],
];

try {
    $db = getDB();

    foreach ($roles as $role) {
        $exists = $db->prepare("SELECT id FROM system_roles WHERE role_alias = ?");
        $exists->execute([$role['role_alias']]);
        if ($exists->fetch()) {
            echo "Role '{$role['role_alias']}' already exists — skipped.\n";
            continue;
        }

        $ids = generateSysIdAndUuid($db, 'system_roles');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO system_roles (sys_id, uuid, name, role_alias, metadata) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$ids['sys_id'], $ids['uuid'], $role['name'], $role['role_alias'], $metadata]);
        $newId = $db->lastInsertId();

        // data_json needs the row's own id/sys_id/uuid, so it's filled
        // in right after insert (can't self-reference within the INSERT).
        $db->prepare("
            UPDATE system_roles
            SET data_json = JSON_OBJECT('id', id, 'sys_id', sys_id, 'uuid', uuid, 'name', name, 'role_alias', role_alias, 'metadata', metadata)
            WHERE id = ?
        ")->execute([$newId]);

        echo "Role '{$role['role_alias']}' created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}