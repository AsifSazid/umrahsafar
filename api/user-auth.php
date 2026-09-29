<?php
// FILE PATH: /api/user-auth.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';
require_once dirname(__DIR__) . '/data/server/username_generator.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Method not allowed.');
if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid security token.');

$action = sanitize($_POST['action'] ?? '');
$db = getDB();

if ($action === 'register') {
    $regRateKey = 'register_' . ($_SERVER['REMOTE_ADDR'] ?? '');
    $regRateWindow = 3600;
    if (!rateLimit($regRateKey, 5, $regRateWindow)) {
        $remaining = rateLimitRemainingSeconds($regRateKey, $regRateWindow);
        $mins = (int) ceil($remaining / 60);
        jsonResponse(false, "Too many attempts. Try again in {$mins} minute" . ($mins === 1 ? '' : 's') . ".", ['retry_after_seconds' => $remaining]);
    }

    $name  = sanitize($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = sanitize($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $conf  = $_POST['confirm_password'] ?? '';

    if (!$name || !$email)     jsonResponse(false, 'Name and valid email required.');
    if (strlen($pass) < 6)     jsonResponse(false, 'Password must be at least 6 characters.');
    if ($pass !== $conf)       jsonResponse(false, 'Passwords do not match.');

    // Check duplicates (email AND phone, matching the register form's
    // own live checks — re-verified here since the live checks can't
    // be trusted alone against a race between two submissions).
    $exists = $db->prepare("SELECT id FROM users WHERE email = :e");
    $exists->execute([':e' => $email]);
    if ($exists->fetch()) jsonResponse(false, 'This email is already registered.');

    if ($phone !== '') {
        $existsPhone = $db->prepare("SELECT id FROM users WHERE phone = :p");
        $existsPhone->execute([':p' => $phone]);
        if ($existsPhone->fetch()) jsonResponse(false, 'This phone number is already registered.');
    }

    // Registering from the public site always creates a Guest account —
    // an admin/staff member upgrades them to Client later once verified.
    $guestRoleSysId = $db->query("SELECT sys_id FROM system_roles WHERE role_alias = 'guest'")->fetchColumn();
    if (!$guestRoleSysId) jsonResponse(false, 'Registration is temporarily unavailable.');

    $ids      = generateSysIdAndUuid($db, 'users');
    $username = generateUsername($db, 'guest');
    $hash     = password_hash($pass, PASSWORD_BCRYPT);
    $now      = date('Y-m-d H:i:s');
    $metadata = json_encode(['created_at' => $now, 'created_by' => 'self-register', 'updated_at' => $now, 'updated_by' => 'self-register']);

    $stmt = $db->prepare("
        INSERT INTO users (sys_id, uuid, role_sys_ids, active_role_sys_id, name, username, email, phone, password_hash, is_verified, metadata)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)
    ");
    $stmt->execute([
        $ids['sys_id'], $ids['uuid'],
        json_encode([$guestRoleSysId]), $guestRoleSysId,
        $name, $username, $email, $phone ?: null, $hash,
        $metadata,
    ]);

    // Guests can't log in to this dashboard (client-only) — no session
    // is started here, matching that restriction.
    jsonResponse(true, 'Account created. An admin will verify your account before you can log in.', [
        'username' => $username,
    ]);
}

if ($action === 'login') {
    $loginRateKey = 'login_' . ($_SERVER['REMOTE_ADDR'] ?? '');
    $loginRateWindow = 180;
    if (!rateLimit($loginRateKey, 5, $loginRateWindow)) {
        $remaining = rateLimitRemainingSeconds($loginRateKey, $loginRateWindow);
        $mins = (int) ceil($remaining / 60);
        jsonResponse(false, "Too many login attempts. Try again in {$mins} minute" . ($mins === 1 ? '' : 's') . ".", ['retry_after_seconds' => $remaining]);
    }

    $login = sanitize($_POST['login'] ?? '');
    $pass  = $_POST['password'] ?? '';
    // '1' = pages/user-dashboard.php (client-only), '0'/absent = admin/index.php (any non-client role)
    $clientLogin = ($_POST['client_login'] ?? '0') === '1';

    if ($login === '') jsonResponse(false, 'Username or email required.');

    $stmt = $db->prepare("
        SELECT u.id, u.name, u.email, u.password_hash, u.is_active, r.role_alias
        FROM users u
        JOIN system_roles r ON r.sys_id = u.active_role_sys_id
        WHERE u.email = :login1 OR u.username = :login2
        LIMIT 1
    ");
    $stmt->execute([':login1' => $login, ':login2' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // The restriction itself always checks the account's REAL role from
    // the database — client_login only selects which rule applies, it
    // can't be used to grant access a role doesn't actually have.
    if ($user) {
        $roleOk = $clientLogin ? ($user['role_alias'] === 'client') : ($user['role_alias'] !== 'client');
        if (!$roleOk) $user = false;
    }

    if (!$user || !password_verify($pass, $user['password_hash']))
        jsonResponse(false, 'Invalid username/email or password.');

    // Checked only after the password is confirmed correct — so a wrong
    // password on a blocked account still just says "invalid", never
    // revealing account status to someone who isn't its real owner.
    if ((int) $user['is_active'] === 0) {
        jsonResponse(false, 'You are temporarily blocked! Please contact office.');
    }

    $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id")->execute([':id' => $user['id']]);

    // Which session gets set — and where the redirect goes — follows
    // from which check just passed above (tied to the verified role),
    // not from the incoming flag on its own.
    if ($clientLogin) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        jsonResponse(true, 'Login successful.', ['redirect' => BASE_URL . '/pages/user-dashboard.php']);
    } else {
        $_SESSION['admin_id']            = $user['id'];
        $_SESSION['admin_username']      = $user['name'];
        $_SESSION['role_alias']          = $user['role_alias'];
        $_SESSION['admin_last_activity'] = time();
        jsonResponse(true, 'Login successful.', ['redirect' => BASE_URL . '/admin/dashboard.php']);
    }
}

if ($action === 'logout') {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
    jsonResponse(true, 'Logged out.', ['redirect' => BASE_URL . '/pages/index.php']);
}

if ($action === 'change_password') {
    if (!isUserLoggedIn()) jsonResponse(false, 'Not logged in.');
    $old  = $_POST['old_password'] ?? '';
    $new  = $_POST['new_password'] ?? '';
    $conf = $_POST['confirm_password'] ?? '';

    if ($new !== $conf)         jsonResponse(false, 'New passwords do not match.');
    if (strlen($new) < 6)       jsonResponse(false, 'Password must be at least 6 characters.');

    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($old, $user['password_hash']))
        jsonResponse(false, 'Current password is incorrect.');

    $hash = password_hash($new, PASSWORD_BCRYPT);
    $db->prepare("UPDATE users SET password_hash = :h WHERE id = :id")
       ->execute([':h' => $hash, ':id' => $_SESSION['user_id']]);
    jsonResponse(true, 'Password changed successfully.');
}

jsonResponse(false, 'Unknown action.');