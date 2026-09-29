<?php
// FILE PATH: /admin/assign-role.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();

// Super-admin only — page-level enforcement, not just a hidden button
// on admin/users.php.
if (($_SESSION['role_alias'] ?? null) !== 'super-admin') {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$csrf = csrfToken();
$userUuid = trim($_GET['user'] ?? '');
if (!$userUuid) { header('Location: ' . BASE_URL . '/admin/users.php'); exit; }

try {
    $db = getDB();
    $userStmt = $db->prepare("SELECT id, uuid, name, email, role_sys_ids, active_role_sys_id FROM users WHERE uuid = ?");
    $userStmt->execute([$userUuid]);
    $targetUser = $userStmt->fetch(PDO::FETCH_ASSOC);
    if (!$targetUser) { header('Location: ' . BASE_URL . '/admin/users.php'); exit; }

    $assignedRoleSysIds = json_decode($targetUser['role_sys_ids'] ?? '[]', true) ?: [];
    $roles = $db->query("SELECT sys_id, name, role_alias FROM system_roles ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    header('Location: ' . BASE_URL . '/admin/users.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Assign Role | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Poppins','sans-serif']},colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625',emerald:'#3AAB71',teal:'#02CCFE'}}}}</script>
<style>body{background:#111625}</style>
</head>
<body class="text-white font-sans min-h-screen">
<div class="flex min-h-screen">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="flex-1 p-4 lg:p-8 overflow-auto">
  <div class="flex items-center gap-3 mb-6">
    <a href="<?= BASE_URL ?>/admin/users.php" class="text-white/40 hover:text-white"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
    <div>
      <h1 class="text-2xl font-bold">Assign Role</h1>
      <p class="text-white/40 text-sm"><?= htmlspecialchars($targetUser['name']) ?> · <?= htmlspecialchars($targetUser['email']) ?></p>
    </div>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <div class="grid grid-cols-1 lg:grid-cols-10 gap-6 w-full">
    <!-- Assign Roles (70%) -->
    <div class="lg:col-span-7 bg-white/5 border border-white/10 rounded-2xl p-6">
      <h2 class="font-bold mb-4">Assign Roles</h2>
      <form id="roles-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="action" value="update_roles">
        <input type="hidden" name="user" value="<?= htmlspecialchars($targetUser['uuid']) ?>">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
          <?php foreach ($roles as $r): $checked = in_array($r['sys_id'], $assignedRoleSysIds, true); ?>
          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input type="checkbox" name="role_sys_ids[]" value="<?= htmlspecialchars($r['sys_id']) ?>" <?= $checked ? 'checked' : '' ?>
                   class="w-4 h-4 rounded border-white/20 bg-dark accent-secondary">
            <?= htmlspecialchars($r['name']) ?>
          </label>
          <?php endforeach; ?>
        </div>
        <button type="submit" id="roles-btn" class="bg-secondary hover:bg-emerald text-primary font-bold px-6 py-2.5 rounded-xl text-sm flex items-center gap-2">
          <i data-lucide="check" class="w-4 h-4"></i> Update
        </button>
      </form>
    </div>

    <!-- Set Active Role (30%) -->
    <div class="lg:col-span-3 bg-white/5 border border-white/10 rounded-2xl p-6">
      <h2 class="font-bold mb-1">Set Active Role</h2>
      <p class="text-white/40 text-xs mb-4">This decides where this user can log in — only roles checked above are selectable.</p>
      <form id="active-role-form" class="space-y-3">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="action" value="update_active_role">
        <input type="hidden" name="user" value="<?= htmlspecialchars($targetUser['uuid']) ?>">
        <select name="active_role_sys_id" id="active-role-select" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
          <?php foreach ($roles as $r): if (!in_array($r['sys_id'], $assignedRoleSysIds, true)) continue; ?>
          <option value="<?= htmlspecialchars($r['sys_id']) ?>" <?= $r['sys_id'] === $targetUser['active_role_sys_id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" id="active-role-btn" class="w-full bg-secondary hover:bg-emerald text-primary font-bold px-6 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2">
          <i data-lucide="check" class="w-4 h-4"></i> Update
        </button>
      </form>
    </div>
  </div>
</main>
</div>
<script>
lucide.createIcons();

// When a checkbox is unchecked, drop that role from the active-role
// dropdown immediately (without a round-trip) — and re-add it if
// re-checked — so the dropdown only ever offers currently-checked roles.
document.querySelectorAll('input[name="role_sys_ids[]"]').forEach(cb => {
  cb.addEventListener('change', () => {
    const select = document.getElementById('active-role-select');
    const existing = select.querySelector(`option[value="${CSS.escape(cb.value)}"]`);
    if (cb.checked && !existing) {
      const opt = document.createElement('option');
      opt.value = cb.value;
      opt.textContent = cb.closest('label').textContent.trim();
      select.appendChild(opt);
    } else if (!cb.checked && existing) {
      existing.remove();
    }
  });
});

document.getElementById('roles-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('roles-btn');
  btn.disabled = true;
  const res = await fetch(`${window.BASE_URL}/api/assign-role.php`, {method:'POST', body:new FormData(e.target)});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false;
  if (data.success) setTimeout(() => location.reload(), 700);
});

document.getElementById('active-role-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('active-role-btn');
  btn.disabled = true;
  const res = await fetch(`${window.BASE_URL}/api/assign-role.php`, {method:'POST', body:new FormData(e.target)});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false;
  if (data.success) setTimeout(() => location.reload(), 700);
});

function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm ${ok?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  t.classList.remove('hidden'); setTimeout(()=>t.classList.add('hidden'),3000);
}
</script>
</body></html>