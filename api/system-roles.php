<?php
// FILE PATH: /api/system-roles.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// GET — list all roles (used by admin/system-roles.php's own page-load
// query too, but kept here in case a future page wants it via fetch)
if ($method === 'GET') {
    requireAdmin();
    try {
        $db = getDB();
        $roles = $db->query("SELECT * FROM system_roles ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(true, 'ok', ['roles' => $roles]);
    } catch (Exception $e) {
        jsonResponse(false, 'DB error.');
    }
}

// POST — create / update / delete. Plain CRUD only — no active/inactive
// toggle for roles (confirmed not needed).
if ($method === 'POST') {
    requireAdmin();
    if (!canAccessTab('system-roles')) jsonResponse(false, 'Not authorized.');
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');

    $db = getDB();
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'create') {
        $name  = sanitize($_POST['name'] ?? '');
        $alias = strtolower(trim(sanitize($_POST['role_alias'] ?? '')));
        if ($name === '' || $alias === '') jsonResponse(false, 'Name and Role Alias are required.');
        if (!preg_match('/^[a-z0-9-]+$/', $alias)) jsonResponse(false, 'Role Alias may only contain lowercase letters, numbers, and hyphens.');

        $ids = generateSysIdAndUuid($db, 'system_roles');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => $_SESSION['admin_username'] ?? 'admin', 'updated_at' => $now, 'updated_by' => $_SESSION['admin_username'] ?? 'admin']);

        try {
            $stmt = $db->prepare("INSERT INTO system_roles (sys_id, uuid, name, role_alias, metadata) VALUES (?,?,?,?,?)");
            $stmt->execute([$ids['sys_id'], $ids['uuid'], $name, $alias, $metadata]);
            $newId = $db->lastInsertId();
            $db->prepare("
                UPDATE system_roles
                SET data_json = JSON_OBJECT('id', id, 'sys_id', sys_id, 'uuid', uuid, 'name', name, 'role_alias', role_alias, 'metadata', metadata)
                WHERE id = ?
            ")->execute([$newId]);
            jsonResponse(true, 'Role created.', ['id' => $newId]);
        } catch (PDOException $e) {
            jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That role alias already exists.' : 'Save failed.');
        }
    }

    if ($action === 'update') {
        $id    = (int) ($_POST['id'] ?? 0);
        $name  = sanitize($_POST['name'] ?? '');
        $alias = strtolower(trim(sanitize($_POST['role_alias'] ?? '')));
        if (!$id || $name === '' || $alias === '') jsonResponse(false, 'Name and Role Alias are required.');
        if (!preg_match('/^[a-z0-9-]+$/', $alias)) jsonResponse(false, 'Role Alias may only contain lowercase letters, numbers, and hyphens.');

        try {
            $existing = $db->prepare("SELECT metadata FROM system_roles WHERE id = ?");
            $existing->execute([$id]);
            $row = $existing->fetch(PDO::FETCH_ASSOC);
            if (!$row) jsonResponse(false, 'Role not found.');

            $meta = json_decode($row['metadata'] ?? '{}', true) ?: [];
            $meta['updated_at'] = date('Y-m-d H:i:s');
            $meta['updated_by'] = $_SESSION['admin_username'] ?? 'admin';
            $metadata = json_encode($meta);

            $db->prepare("UPDATE system_roles SET name=?, role_alias=?, metadata=? WHERE id=?")
               ->execute([$name, $alias, $metadata, $id]);
            $db->prepare("
                UPDATE system_roles
                SET data_json = JSON_OBJECT('id', id, 'sys_id', sys_id, 'uuid', uuid, 'name', name, 'role_alias', role_alias, 'metadata', metadata)
                WHERE id = ?
            ")->execute([$id]);
            jsonResponse(true, 'Role updated.');
        } catch (PDOException $e) {
            jsonResponse(false, str_contains($e->getMessage(), 'Duplicate') ? 'That role alias already exists.' : 'Save failed.');
        }
    }
    jsonResponse(false, 'Unknown action.');
}