<?php
// FILE PATH: /admin/visa-types.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
$csrf = csrfToken();
try {
    $db = getDB();
    $types = $db->query("SELECT * FROM visa_types ORDER BY sort_order, name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $types = []; }
$ICONS = ['file-text','plane','check-circle-2','shield-check','stamp','globe'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Visa Types | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Poppins','sans-serif']},colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625',emerald:'#3AAB71',teal:'#02CCFE'}}}}</script>
<style>body{background:#111625}#modal-overlay{display:none}#modal-overlay.open{display:flex}
.price-input:focus{outline:none;border-color:#50BC81}
</style>
</head>
<body class="text-white font-sans min-h-screen">
<div class="flex min-h-screen">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="flex-1 p-4 lg:p-8 overflow-auto">
  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div><h1 class="text-2xl font-bold">Visa Types</h1><p class="text-white/40 text-sm">Click any price to edit it instantly — no need to open the full form.</p></div>
    <button onclick="openModal()" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New Visa Type</button>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <div class="space-y-3">
    <?php foreach ($types as $t): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5 flex items-center justify-between gap-4 flex-wrap" data-sys-id="<?= htmlspecialchars($t['sys_id']) ?>">
      <div class="flex items-center gap-4 min-w-0">
        <div class="w-11 h-11 rounded-xl bg-secondary/10 flex items-center justify-center shrink-0"><i data-lucide="<?= htmlspecialchars($t['icon']) ?>" class="w-5 h-5 text-secondary"></i></div>
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-semibold text-sm"><?= htmlspecialchars($t['name']) ?></span>
            <?php if(!$t['is_active']): ?><span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full">Inactive</span><?php endif; ?>
          </div>
          <p class="text-xs text-white/30 truncate max-w-md"><?= htmlspecialchars($t['description'] ?: '—') ?></p>
        </div>
      </div>
      <div class="flex items-center gap-3 shrink-0">
        <!-- For Umrah toggle -->
        <button type="button" onclick="toggleForUmrah('<?= htmlspecialchars($t['sys_id'], ENT_QUOTES) ?>', this)"
                data-for-umrah="<?= (int)$t['for_umrah'] ?>"
                class="px-3 py-2 text-xs rounded-lg font-bold flex items-center gap-1.5 <?= $t['for_umrah'] ? 'bg-secondary/10 text-secondary' : 'bg-white/5 text-white/40' ?>">
          <i data-lucide="<?= $t['for_umrah'] ? 'check-square' : 'square' ?>" class="w-3.5 h-3.5"></i> For Umrah
        </button>
        <!-- Quick price editor -->
        <div class="flex items-center bg-dark border border-white/10 rounded-xl overflow-hidden">
          <span class="pl-3 text-xs text-white/30">SR</span>
          <input type="number" step="0.01" value="<?= number_format((float)$t['price'],2,'.','') ?>"
                 class="price-input w-24 bg-transparent px-2 py-2 text-sm font-bold text-secondary border-2 border-transparent"
                 data-sys-id="<?= htmlspecialchars($t['sys_id']) ?>">
        </div>
        <button onclick='editType(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-3 py-2 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
        <button onclick="toggleType('<?= htmlspecialchars($t['sys_id'], ENT_QUOTES) ?>')" class="px-3 py-2 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="<?= $t['is_active']?'eye-off':'eye' ?>" class="w-3.5 h-3.5"></i></button>
        <button onclick="deleteType('<?= htmlspecialchars($t['sys_id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($t['name'],ENT_QUOTES) ?>')" class="px-3 py-2 text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$types): ?>
    <div class="text-center py-16 text-white/30 bg-white/5 border border-white/10 rounded-2xl"><i data-lucide="file-text" class="w-10 h-10 mx-auto mb-2 opacity-30"></i><p class="text-sm">No visa types yet.</p></div>
    <?php endif; ?>
  </div>
</main>
</div>

<!-- Full edit modal -->
<div id="modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-md my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg" id="modal-title">New Visa Type</h2>
      <button onclick="closeModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="type-form" class="p-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="sys_id" value="">
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Name</label>
        <input type="text" name="name" required placeholder="e.g. Umrah Visa" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Price (SAR)</label>
        <input type="number" step="0.01" name="price" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <label class="flex items-center gap-2 text-sm cursor-pointer">
        <input type="checkbox" name="for_umrah" value="1" class="w-4 h-4 rounded border-white/20 bg-dark accent-secondary">
        For Umrah
      </label>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Description</label>
        <textarea name="description" rows="2" placeholder="Short note shown to customers" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none resize-none"></textarea>
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Icon</label>
        <select name="icon" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
          <?php foreach ($ICONS as $ic): ?><option value="<?= $ic ?>"><?= $ic ?></option><?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Sort Order</label>
        <input type="number" name="sort_order" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="flex-1 bg-white/5 hover:bg-white/10 font-bold py-3 rounded-xl text-sm">Cancel</button>
        <button type="submit" class="flex-1 bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm" id="type-btn"><i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save</button>
      </div>
    </form>
  </div>
</div>
<script>
lucide.createIcons();
const csrf = '<?= htmlspecialchars($csrf) ?>';

function openModal(t=null) {
  const form = document.getElementById('type-form');
  form.reset();
  document.getElementById('modal-title').textContent = t ? 'Edit Visa Type' : 'New Visa Type';
  // form.action is a RESERVED HTMLFormElement property (the submit URL) —
  // it always shadows a same-named child <input>, so setting .value on
  // it silently does nothing. form.elements[...] correctly reaches the
  // actual named <input> regardless of any reserved-property collision.
  form.elements['action'].value = t ? 'update' : 'create';
  form.elements['sys_id'].value = t ? (t.sys_id || '') : '';
  if (t) {
    form.elements['name'].value = t.name || '';
    form.elements['price'].value = t.price || 0;
    form.elements['for_umrah'].checked = !!Number(t.for_umrah);
    form.elements['description'].value = t.description || '';
    form.elements['icon'].value = t.icon || 'file-text';
    form.elements['sort_order'].value = t.sort_order || 0;
  }
  document.getElementById('modal-overlay').classList.add('open');
}
function editType(t) { openModal(t); }
function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); }

document.getElementById('type-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('type-btn');
  btn.disabled = true; btn.textContent = 'Saving...';
  const res = await fetch(`<?= BASE_URL ?>/api/visa-types.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false; btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save'; lucide.createIcons();
  if (data.success) { closeModal(); setTimeout(()=>location.reload(), 700); }
});

async function toggleType(sysId) {
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','toggle'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/visa-types.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}

// Toggles instantly in place (button color/icon), no page reload —
// matches the quick-price editor's no-reload feel.
async function toggleForUmrah(sysId, btnEl) {
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','toggle_for_umrah'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/visa-types.php`,{method:'POST',body:fd});
  const data = await res.json();
  if (!data.success) { showToast(data.message || 'Failed to toggle.', false); return; }
  const on = !!data.for_umrah;
  btnEl.dataset.forUmrah = on ? '1' : '0';
  btnEl.className = `px-3 py-2 text-xs rounded-lg font-bold flex items-center gap-1.5 ${on ? 'bg-secondary/10 text-secondary' : 'bg-white/5 text-white/40'}`;
  btnEl.innerHTML = `<i data-lucide="${on ? 'check-square' : 'square'}" class="w-3.5 h-3.5"></i> For Umrah`;
  lucide.createIcons();
}

async function deleteType(sysId, name) {
  if (!confirm(`Delete visa type "${name}"?`)) return;
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','delete'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/visa-types.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}

// Quick inline price editing — saves on blur or Enter, no page reload
document.querySelectorAll('.price-input').forEach(input => {
  const original = input.value;
  input.addEventListener('keydown', e => { if (e.key === 'Enter') input.blur(); });
  input.addEventListener('focus', () => input.select());
  input.addEventListener('blur', async () => {
    if (input.value === original || input.value === '') return;
    const fd = new FormData();
    fd.append('csrf_token', csrf);
    fd.append('action', 'quick_price');
    fd.append('sys_id', input.dataset.sysId);
    fd.append('price', input.value);
    input.style.borderColor = '#50BC81';
    const res = await fetch(`<?= BASE_URL ?>/api/visa-types.php`, {method:'POST', body:fd});
    const data = await res.json();
    if (data.success) {
      showToast('Price updated.', true);
      input.style.borderColor = 'transparent';
    } else {
      showToast(data.message || 'Failed to update price.', false);
      input.value = original;
    }
  });
});

function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm ${ok?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  t.classList.remove('hidden'); setTimeout(()=>t.classList.add('hidden'),3000);
}
</script>
</body></html>