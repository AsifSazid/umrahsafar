<?php
// FILE PATH: /admin/system-roles.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
if (!canAccessTab('system-roles')) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}
$csrf = csrfToken();
try {
    $db = getDB();
    $roles = $db->query("SELECT * FROM system_roles ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $roles = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>System Roles | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Poppins','sans-serif']},colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625',emerald:'#3AAB71',teal:'#02CCFE'}}}}</script>
<style>
body{background:#111625}
#modal-overlay{display:none}
#modal-overlay.open{display:flex}
</style>
</head>
<body class="text-white font-sans min-h-screen">
<div class="flex min-h-screen">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="flex-1 p-4 lg:p-8 overflow-auto">
  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div><h1 class="text-2xl font-bold">System Roles</h1><p class="text-white/40 text-sm">Manage the roles users can be assigned — Super Admin only.</p></div>
    <button onclick="openModal()" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New Role</button>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <div class="space-y-3">
    <?php foreach ($roles as $r): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5 flex items-center justify-between gap-4 flex-wrap" data-id="<?= $r['id'] ?>">
      <div class="flex items-center gap-4 min-w-0">
        <div class="w-11 h-11 rounded-xl bg-secondary/10 flex items-center justify-center shrink-0"><i data-lucide="shield" class="w-5 h-5 text-secondary"></i></div>
        <div class="min-w-0">
          <span class="font-semibold text-sm"><?= htmlspecialchars($r['name']) ?></span>
          <p class="text-xs text-white/30 font-mono"><?= htmlspecialchars($r['role_alias']) ?> · <?= htmlspecialchars($r['sys_id']) ?></p>
        </div>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick='editRole(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-3 py-2 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$roles): ?>
    <div class="text-center py-16 text-white/30 bg-white/5 border border-white/10 rounded-2xl"><i data-lucide="shield" class="w-10 h-10 mx-auto mb-2 opacity-30"></i><p class="text-sm">No roles yet.</p></div>
    <?php endif; ?>
  </div>
</main>
</div>

<div id="modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-md my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg" id="modal-title">New Role</h2>
      <button onclick="closeModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="role-form" class="p-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="id" value="">
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Name</label>
        <input type="text" name="name" required placeholder="e.g. Staff" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Role Alias</label>
        <input type="text" name="role_alias" required placeholder="e.g. staff" pattern="[a-z0-9\-]+" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
        <p class="text-[10px] text-white/25 mt-1">Lowercase letters, numbers, and hyphens only — used internally for access checks.</p>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="flex-1 bg-white/5 hover:bg-white/10 font-bold py-3 rounded-xl text-sm">Cancel</button>
        <button type="submit" class="flex-1 bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm" id="role-btn"><i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save</button>
      </div>
    </form>
  </div>
</div>
<script>
lucide.createIcons();
const csrf = '<?= htmlspecialchars($csrf) ?>';

function openModal(r=null) {
  const form = document.getElementById('role-form');
  form.reset();
  document.getElementById('modal-title').textContent = r ? 'Edit Role' : 'New Role';
  // form.action and form.name are RESERVED HTMLFormElement properties
  // (the submit URL and the form's own name attribute) — they always
  // shadow same-named child inputs, so setting .value on them silently
  // does nothing. form.elements[...] correctly reaches the actual
  // named <input> regardless of any reserved-property collision.
  form.elements['action'].value = r ? 'update' : 'create';
  form.elements['id'].value = r ? (r.id || '') : '';
  if (r) {
    form.elements['name'].value = r.name || '';
    form.elements['role_alias'].value = r.role_alias || '';
  }
  document.getElementById('modal-overlay').classList.add('open');
}
function editRole(r) { openModal(r); }
function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); }

document.getElementById('role-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('role-btn');
  btn.disabled = true; btn.textContent = 'Saving...';
  const res = await fetch(`<?= BASE_URL ?>/api/system-roles.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false; btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save'; lucide.createIcons();
  if (data.success) { closeModal(); setTimeout(()=>location.reload(), 700); }
});

function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm ${ok?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  t.classList.remove('hidden'); setTimeout(()=>t.classList.add('hidden'),3000);
}
</script>
</body></html>