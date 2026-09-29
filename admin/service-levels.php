<?php
// FILE PATH: /admin/service-levels.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
$csrf = csrfToken();
try {
    $db = getDB();
    $levels = $db->query("SELECT * FROM service_levels ORDER BY sort_order, name")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($levels as &$l) { $l['features'] = json_decode($l['features'] ?? '[]', true) ?: []; }
    unset($l);
} catch (Exception $e) { $levels = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Service Levels | TravHub Admin</title>
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
    <div><h1 class="text-2xl font-bold">Service Levels</h1><p class="text-white/40 text-sm">Economy / Premium / Luxury tiers — shown on the Travelers step of both builders. Click any price to edit it instantly.</p></div>
    <button onclick="openModal()" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New Service Level</button>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <div class="space-y-3">
    <?php foreach ($levels as $l): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5 flex items-center justify-between gap-4 flex-wrap" data-sys-id="<?= htmlspecialchars($l['sys_id']) ?>">
      <div class="flex items-center gap-4 min-w-0">
        <div class="w-11 h-11 rounded-xl bg-secondary/10 flex items-center justify-center shrink-0"><i data-lucide="star" class="w-5 h-5 text-secondary"></i></div>
        <div class="min-w-0">
          <div class="flex items-center gap-2">
            <span class="font-semibold text-sm"><?= htmlspecialchars($l['name']) ?></span>
            <?php if(!$l['is_active']): ?><span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full">Inactive</span><?php endif; ?>
          </div>
          <p class="text-xs text-white/30 truncate max-w-md"><?= htmlspecialchars($l['features'] ? implode(' · ', $l['features']) : '—') ?></p>
        </div>
      </div>
      <div class="flex items-center gap-3 shrink-0">
        <!-- Quick price editor -->
        <div class="flex items-center bg-dark border border-white/10 rounded-xl overflow-hidden">
          <span class="pl-3 text-xs text-white/30">SR</span>
          <input type="number" step="0.01" value="<?= number_format((float)$l['price'],2,'.','') ?>"
                 class="price-input w-24 bg-transparent px-2 py-2 text-sm font-bold text-secondary border-2 border-transparent"
                 data-sys-id="<?= htmlspecialchars($l['sys_id']) ?>">
        </div>
        <button onclick='editLevel(<?= json_encode($l, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-3 py-2 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
        <button onclick="toggleLevel('<?= htmlspecialchars($l['sys_id'], ENT_QUOTES) ?>')" class="px-3 py-2 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="<?= $l['is_active']?'eye-off':'eye' ?>" class="w-3.5 h-3.5"></i></button>
        <button onclick="deleteLevel('<?= htmlspecialchars($l['sys_id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($l['name'],ENT_QUOTES) ?>')" class="px-3 py-2 text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$levels): ?>
    <div class="text-center py-16 text-white/30 bg-white/5 border border-white/10 rounded-2xl"><i data-lucide="star" class="w-10 h-10 mx-auto mb-2 opacity-30"></i><p class="text-sm">No service levels yet.</p></div>
    <?php endif; ?>
  </div>
</main>
</div>

<!-- Full edit modal -->
<div id="modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-md my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg" id="modal-title">New Service Level</h2>
      <button onclick="closeModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="level-form" class="p-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="sys_id" value="">
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Name</label>
        <input type="text" name="name" required placeholder="e.g. Premium" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Price (SAR)</label>
        <input type="number" step="0.01" name="price" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Features (one per line)</label>
        <textarea name="features" rows="4" placeholder="4-Star Hotels&#10;Private transfers&#10;Ziyarah Tours" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none resize-none"></textarea>
        <p class="text-[10px] text-white/25 mt-1">Shown as bullet points on the traveler's Service Level card.</p>
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Sort Order</label>
        <input type="number" name="sort_order" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="flex-1 bg-white/5 hover:bg-white/10 font-bold py-3 rounded-xl text-sm">Cancel</button>
        <button type="submit" class="flex-1 bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm" id="level-btn"><i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save</button>
      </div>
    </form>
  </div>
</div>
<script>
lucide.createIcons();
const csrf = '<?= htmlspecialchars($csrf) ?>';

function openModal(l=null) {
  const form = document.getElementById('level-form');
  form.reset();
  document.getElementById('modal-title').textContent = l ? 'Edit Service Level' : 'New Service Level';
  // form.action is a RESERVED HTMLFormElement property (the submit URL) —
  // it always shadows a same-named child <input>, so setting .value on
  // it silently does nothing. form.elements[...] correctly reaches the
  // actual named <input> regardless of any reserved-property collision.
  form.elements['action'].value = l ? 'update' : 'create';
  form.elements['sys_id'].value = l ? (l.sys_id || '') : '';
  if (l) {
    form.elements['name'].value = l.name || '';
    form.elements['price'].value = l.price || 0;
    form.elements['features'].value = (l.features || []).join('\n');
    form.elements['sort_order'].value = l.sort_order || 0;
  }
  document.getElementById('modal-overlay').classList.add('open');
}
function editLevel(l) { openModal(l); }
function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); }

document.getElementById('level-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('level-btn');
  btn.disabled = true; btn.textContent = 'Saving...';
  const res = await fetch(`<?= BASE_URL ?>/api/service-levels.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false; btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save'; lucide.createIcons();
  if (data.success) { closeModal(); setTimeout(()=>location.reload(), 700); }
});

async function toggleLevel(sysId) {
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','toggle'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/service-levels.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}
async function deleteLevel(sysId, name) {
  if (!confirm(`Delete service level "${name}"?`)) return;
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','delete'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/service-levels.php`,{method:'POST',body:fd});
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
    const res = await fetch(`<?= BASE_URL ?>/api/service-levels.php`, {method:'POST', body:fd});
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