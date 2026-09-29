<?php
// FILE PATH: /api/assign-role.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

header('Content-Type: application/json');
requireAdmin();

// Super-admin only — the button itself is also hidden from everyone
// else, but the API enforces it independently (never trust UI-hiding
// alone for a real permission boundary).
if (($_SESSION['role_alias'] ?? null) !== 'super-admin') {
    jsonResponse(false, 'Not authorized.');
}

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// GET — fetch every role + this user's current assignment, for the
// checkbox grid + active-role dropdown to render from.
if ($method === 'GET') {
    $userUuid = trim($_GET['user'] ?? '');
    if (!$userUuid) jsonResponse(false, 'Missing user.');

    $userStmt = $db->prepare("SELECT id, uuid, name, role_sys_ids, active_role_sys_id FROM users WHERE uuid = ?");
    $userStmt->execute([$userUuid]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) jsonResponse(false, 'User not found.');

    $roles = $db->query("SELECT sys_id, name, role_alias FROM system_roles ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(true, 'ok', [
        'roles' => $roles,
        'user' => [
            'uuid' => $user['uuid'],
            'name' => $user['name'],
            'role_sys_ids' => json_decode($user['role_sys_ids'] ?? '[]', true) ?: [],
            'active_role_sys_id' => $user['active_role_sys_id'],
        ],
    ]);
}

// POST — update assigned roles, or the active role.
if ($method === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid token.');
    $action = sanitize($_POST['action'] ?? '');
    $userUuid = trim($_POST['user'] ?? '');
    if (!$userUuid) jsonResponse(false, 'Missing user.');

    $userStmt = $db->prepare("SELECT id, role_sys_ids, active_role_sys_id FROM users WHERE uuid = ?");
    $userStmt->execute([$userUuid]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) jsonResponse(false, 'User not found.');
    $userId = $user['id']; // internal PK, used only for the actual UPDATE below

    if ($action === 'update_roles') {
        $roleSysIds = $_POST['role_sys_ids'] ?? [];
        if (!is_array($roleSysIds)) $roleSysIds = [];
        $roleSysIds = array_values(array_unique(array_filter(array_map('sanitize', $roleSysIds))));

        if (empty($roleSysIds)) jsonResponse(false, 'At least one role must be assigned.');

        // The currently-active role must stay in the list — changing
        // that requires the "Set Active Role" action instead, so this
        // never silently leaves a user with an active role they're no
        // longer assigned.
        if (!in_array($user['active_role_sys_id'], $roleSysIds, true)) {
            jsonResponse(false, 'The active role must remain checked — set a different active role first if you want to remove it.');
        }

        // Validate every submitted sys_id actually exists as a role.
        $placeholders = implode(',', array_fill(0, count($roleSysIds), '?'));
        $validCount = $db->prepare("SELECT COUNT(*) FROM system_roles WHERE sys_id IN ($placeholders)");
        $validCount->execute($roleSysIds);
        if ((int) $validCount->fetchColumn() !== count($roleSysIds)) {
            jsonResponse(false, 'One or more selected roles are invalid.');
        }

        $db->prepare("UPDATE users SET role_sys_ids = ? WHERE id = ?")
           ->execute([json_encode($roleSysIds), $userId]);
        jsonResponse(true, 'Assigned roles updated.');
    }

    if ($action === 'update_active_role') {
        $activeRoleSysId = sanitize($_POST['active_role_sys_id'] ?? '');
        if ($activeRoleSysId === '') jsonResponse(false, 'Missing role.');

        $currentRoleSysIds = json_decode($user['role_sys_ids'] ?? '[]', true) ?: [];
        if (!in_array($activeRoleSysId, $currentRoleSysIds, true)) {
            jsonResponse(false, 'You can only set an already-assigned role as active.');
        }

        $db->prepare("UPDATE users SET active_role_sys_id = ? WHERE id = ?")
           ->execute([$activeRoleSysId, $userId]);
        jsonResponse(true, 'Active role updated.');
    }

    jsonResponse(false, 'Unknown action.');
}