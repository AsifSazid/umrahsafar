<?php
// FILE PATH: /admin/users.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
if (!canAccessTab('users')) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}
$csrf = csrfToken();
$isSuperAdmin = ($_SESSION['role_alias'] ?? null) === 'super-admin';

try {
    $db = getDB();
    $roleMap = [];
    foreach ($db->query("SELECT sys_id, name, role_alias FROM system_roles") as $r) {
        $roleMap[$r['sys_id']] = $r;
    }
    $users = $db->query("SELECT * FROM users ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as &$u) {
        $u['role_sys_ids_arr']  = json_decode($u['role_sys_ids'] ?? '[]', true) ?: [];
        $u['active_role_alias'] = $roleMap[$u['active_role_sys_id']]['role_alias'] ?? null;
        $u['active_role_name']  = $roleMap[$u['active_role_sys_id']]['name'] ?? $u['active_role_sys_id'];
    }
    unset($u);
    $employeeUsers = array_values(array_filter($users, fn($u) => $u['active_role_alias'] !== 'client'));
    $clientUsers   = array_values(array_filter($users, fn($u) => $u['active_role_alias'] === 'client'));
} catch (Exception $e) {
    $roleMap = []; $employeeUsers = []; $clientUsers = [];
}

// Renders one user's row — shared between both tabs so the markup
// (and the role-badge / block-button logic) can't drift apart.
function renderUserRow(array $u, array $roleMap, bool $isSelf, bool $isSuperAdmin): void {
    $isActive = (int) $u['is_active'] === 1;
    ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5 flex items-center justify-between gap-4 flex-wrap" data-id="<?= $u['id'] ?>">
      <div class="flex items-center gap-4 min-w-0">
        <div class="w-11 h-11 rounded-xl bg-secondary/10 flex items-center justify-center shrink-0"><i data-lucide="user" class="w-5 h-5 text-secondary"></i></div>
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-semibold text-sm"><?= htmlspecialchars($u['name']) ?></span>
            <?php if (!$isActive): ?><span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full">Blocked</span><?php endif; ?>
          </div>
          <p class="text-xs text-white/30"><?= htmlspecialchars($u['email']) ?><?= $u['username'] ? ' · ' . htmlspecialchars($u['username']) : '' ?><?= $u['phone'] ? ' · ' . htmlspecialchars($u['phone']) : '' ?></p>
          <div class="flex flex-wrap gap-1.5 mt-2">
            <?php foreach ($u['role_sys_ids_arr'] as $roleSysId):
                $roleName = $roleMap[$roleSysId]['name'] ?? $roleSysId;
                $isThisActive = $roleSysId === $u['active_role_sys_id'];
            ?>
            <span class="text-[10px] px-2 py-0.5 rounded-full <?= $isThisActive ? 'font-bold text-secondary bg-secondary/10 border border-secondary/20' : 'text-white/40 bg-white/5' ?>"><?= htmlspecialchars($roleName) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button type="button" onclick='editUser(<?= json_encode(["uuid"=>$u["uuid"],"name"=>$u["name"],"email"=>$u["email"],"phone"=>$u["phone"]], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-3 py-2 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
        <?php if ($isSuperAdmin): ?>
        <a href="<?= BASE_URL ?>/admin/assign-role.php?user=<?= urlencode($u['uuid']) ?>" class="px-3 py-2 text-xs bg-white/5 hover:bg-white/10 rounded-lg font-medium">Assign Role</a>
        <?php endif; ?>
        <?php if ($isSelf): ?>
        <span class="px-3 py-2 text-xs text-white/20" title="You can't block your own account">Block</span>
        <?php else: ?>
        <button type="button" onclick="toggleUserActive('<?= htmlspecialchars($u['uuid'], ENT_QUOTES) ?>', <?= $isActive ? 1 : 0 ?>)"
                id="block-btn-<?= htmlspecialchars($u['uuid']) ?>"
                class="px-3 py-2 text-xs rounded-lg font-bold <?= $isActive ? 'bg-red-500/10 hover:bg-red-500/20 text-red-400' : 'bg-secondary/10 hover:bg-secondary/20 text-secondary' ?>">
          <?= $isActive ? 'Block' : 'Unblock' ?>
        </button>
        <?php endif; ?>
      </div>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Users | TravHub Admin</title>
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
  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold">Users</h1>
      <p class="text-white/40 text-sm">Manage employee and client accounts.</p>
    </div>
    <button onclick="openCreateModal()" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New User</button>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <!-- Tabs -->
  <div class="flex gap-2 mb-6 bg-white/5 border border-white/10 rounded-xl p-1 w-fit">
    <button type="button" id="tab-employee-btn" onclick="switchUserTab('employee')" class="px-5 py-2.5 rounded-lg text-sm font-bold transition-all bg-secondary text-primary">Employee Users</button>
    <button type="button" id="tab-client-btn" onclick="switchUserTab('client')" class="px-5 py-2.5 rounded-lg text-sm font-bold transition-all text-white/50 hover:text-white">Client Users</button>
  </div>

  <!-- Employee Users -->
  <div id="employee-panel" class="space-y-3">
    <?php foreach ($employeeUsers as $u): ?>
      <?php renderUserRow($u, $roleMap, $u['id'] === (int) ($_SESSION['admin_id'] ?? 0), $isSuperAdmin); ?>
    <?php endforeach; ?>
    <?php if (!$employeeUsers): ?>
    <div class="text-center py-16 text-white/30 bg-white/5 border border-white/10 rounded-2xl"><i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-30"></i><p class="text-sm">No employee users yet.</p></div>
    <?php endif; ?>
  </div>

  <!-- Client Users -->
  <div id="client-panel" class="hidden space-y-3">
    <?php foreach ($clientUsers as $u): ?>
      <?php renderUserRow($u, $roleMap, false, $isSuperAdmin); ?>
    <?php endforeach; ?>
    <?php if (!$clientUsers): ?>
    <div class="text-center py-16 text-white/30 bg-white/5 border border-white/10 rounded-2xl"><i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-30"></i><p class="text-sm">No client users yet.</p></div>
    <?php endif; ?>
  </div>
</main>
</div>

<!-- Create User modal — always registers as Guest (matches self-register flow);
     super-admin assigns a real role afterward via Assign Role. -->
<div id="create-modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto" style="display:none;">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-md my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg">New User</h2>
      <button onclick="closeCreateModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="create-form" class="p-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="register">
      <input type="hidden" name="password" value="123456">
      <input type="hidden" name="confirm_password" value="123456">
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Full Name</label>
        <input type="text" name="name" required class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Email</label>
        <input type="email" name="email" id="create-email" required class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
        <p id="create-email-msg" class="text-[10px] mt-1"></p>
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Phone</label>
        <input type="tel" name="phone" id="create-phone" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
        <p id="create-phone-msg" class="text-[10px] mt-1"></p>
      </div>
      <div class="bg-secondary/5 border border-secondary/20 rounded-xl p-3 text-xs text-white/50">
        Default password: <span class="font-mono text-secondary">123456</span> — the user should change it after first login. Account is created as <strong class="text-white/70">Guest</strong>; assign a real role afterward.
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeCreateModal()" class="flex-1 bg-white/5 hover:bg-white/10 font-bold py-3 rounded-xl text-sm">Cancel</button>
        <button type="submit" id="create-btn" class="flex-1 bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm">Create</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit User modal -->
<div id="edit-modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto" style="display:none;">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-md my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg">Edit User</h2>
      <button onclick="closeEditModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="edit-form" class="p-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="user" id="edit-user-uuid" value="">
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Full Name</label>
        <input type="text" name="name" id="edit-name" required class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Email</label>
        <input type="email" name="email" id="edit-email" required class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
        <p id="edit-email-msg" class="text-[10px] mt-1"></p>
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Phone</label>
        <input type="tel" name="phone" id="edit-phone" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
        <p id="edit-phone-msg" class="text-[10px] mt-1"></p>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeEditModal()" class="flex-1 bg-white/5 hover:bg-white/10 font-bold py-3 rounded-xl text-sm">Cancel</button>
        <button type="submit" id="edit-btn" class="flex-1 bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm">Save</button>
      </div>
    </form>
  </div>
</div>
<script>
lucide.createIcons();
const csrf = '<?= htmlspecialchars($csrf) ?>';

function switchUserTab(tab) {
  const empBtn = document.getElementById('tab-employee-btn');
  const cliBtn = document.getElementById('tab-client-btn');
  const empPanel = document.getElementById('employee-panel');
  const cliPanel = document.getElementById('client-panel');
  const active = 'px-5 py-2.5 rounded-lg text-sm font-bold transition-all bg-secondary text-primary';
  const inactive = 'px-5 py-2.5 rounded-lg text-sm font-bold transition-all text-white/50 hover:text-white';
  if (tab === 'employee') {
    empBtn.className = active; cliBtn.className = inactive;
    empPanel.classList.remove('hidden'); cliPanel.classList.add('hidden');
  } else {
    cliBtn.className = active; empBtn.className = inactive;
    cliPanel.classList.remove('hidden'); empPanel.classList.add('hidden');
  }
}

// ── Create User modal ──
function openCreateModal() {
  document.getElementById('create-form').reset();
  setMsg('create-email-msg', '', true);
  setMsg('create-phone-msg', '', true);
  document.getElementById('create-modal-overlay').style.display = 'flex';
}
function closeCreateModal() { document.getElementById('create-modal-overlay').style.display = 'none'; }

function setMsg(id, text, ok) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = text;
  el.className = `text-[10px] mt-1 ${ok ? 'text-secondary' : 'text-red-400'}`;
}

function wireLiveCheck(inputId, msgId, checkUrlBase) {
  let timer;
  const input = document.getElementById(inputId);
  if (!input || input.dataset.liveCheckWired) return; // wire once, never stack duplicate listeners
  input.dataset.liveCheckWired = '1';
  input.addEventListener('input', function() {
    clearTimeout(timer);
    const val = this.value.trim();
    if (!val) { setMsg(msgId, '', true); return; }
    timer = setTimeout(async () => {
      try {
        const excludeUserUuid = this.dataset.excludeUserUuid || '';
        const excl = excludeUserUuid ? `&exclude_user_uuid=${encodeURIComponent(excludeUserUuid)}` : '';
        const param = inputId.includes('email') ? 'email' : 'phone';
        const res = await fetch(`${window.BASE_URL}/${checkUrlBase}?${param}=${encodeURIComponent(val)}${excl}`);
        const data = await res.json();
        setMsg(msgId, data.message, data.success ? !data.exists : false);
      } catch (e) { setMsg(msgId, 'Could not check right now.', false); }
    }, 400);
  });
}
wireLiveCheck('create-email', 'create-email-msg', 'api/check-email.php');
wireLiveCheck('create-phone', 'create-phone-msg', 'api/check-phone.php');
wireLiveCheck('edit-email', 'edit-email-msg', 'api/check-email.php');
wireLiveCheck('edit-phone', 'edit-phone-msg', 'api/check-phone.php');

document.getElementById('create-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('create-btn');
  btn.disabled = true; btn.textContent = 'Creating...';
  const res = await fetch(`${window.BASE_URL}/api/user-auth.php`, {method:'POST', body:new FormData(e.target)});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false; btn.textContent = 'Create';
  if (data.success) { closeCreateModal(); setTimeout(() => location.reload(), 700); }
});

// ── Edit User modal ──
function editUser(u) {
  document.getElementById('edit-user-uuid').value = u.uuid;
  document.getElementById('edit-name').value = u.name || '';
  document.getElementById('edit-email').value = u.email || '';
  document.getElementById('edit-phone').value = u.phone || '';
  document.getElementById('edit-email').dataset.excludeUserUuid = u.uuid;
  document.getElementById('edit-phone').dataset.excludeUserUuid = u.uuid;
  setMsg('edit-email-msg', '', true);
  setMsg('edit-phone-msg', '', true);
  document.getElementById('edit-modal-overlay').style.display = 'flex';
}
function closeEditModal() { document.getElementById('edit-modal-overlay').style.display = 'none'; }

document.getElementById('edit-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('edit-btn');
  btn.disabled = true; btn.textContent = 'Saving...';
  const res = await fetch(`${window.BASE_URL}/api/users.php`, {method:'POST', body:new FormData(e.target)});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false; btn.textContent = 'Save';
  if (data.success) { closeEditModal(); setTimeout(() => location.reload(), 700); }
});

async function toggleUserActive(userUuid, currentlyActive) {
  const label = currentlyActive ? 'block' : 'unblock';
  if (!confirm(`Are you sure you want to ${label} this user?`)) return;
  const fd = new FormData();
  fd.append('csrf_token', csrf);
  fd.append('action', 'toggle_active');
  fd.append('user', userUuid);
  const res = await fetch(`${window.BASE_URL}/api/users.php`, {method:'POST', body:fd});
  const data = await res.json();
  showToast(data.message, data.success);
  if (data.success) setTimeout(() => location.reload(), 700);
}

function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm ${ok?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  t.classList.remove('hidden'); setTimeout(()=>t.classList.add('hidden'),3000);
}
</script>
</body></html>