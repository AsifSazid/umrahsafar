<?php
// FILE PATH: /admin/moyallem.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
$csrf = csrfToken();
try {
    $db = getDB();
    $services = $db->query("SELECT * FROM moyallem_services ORDER BY sort_order, name")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($services as &$s) { $s['prices'] = json_decode($s['category_prices'], true) ?: []; }
    unset($s);
} catch (Exception $e) { $services = []; }
$ICONS = ['star','users','map','map-pin','award','heart','book-open','compass','shield','user-check','mosque'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Moyallem | TravHub Admin</title>
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
    <div><h1 class="text-2xl font-bold">Moyallem</h1><p class="text-white/40 text-sm">Spiritual guide services, each priced by General/Expert/VIP category. Click a price to edit it instantly.</p></div>
    <div class="flex gap-2">
      <button onclick="openModal()" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New Service</button>
    </div>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <div class="space-y-3">
    <?php foreach ($services as $svc): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5" data-sys-id="<?= htmlspecialchars($svc['sys_id']) ?>">
      <div class="flex items-start justify-between gap-3 flex-wrap mb-3">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 bg-secondary/10 rounded-xl flex items-center justify-center shrink-0"><i data-lucide="<?= htmlspecialchars($svc['icon']) ?>" class="w-4 h-4 text-secondary"></i></div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-semibold text-sm"><?= htmlspecialchars($svc['name']) ?></span>
              <?php if(!$svc['is_active']): ?><span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full">Inactive</span><?php endif; ?>
            </div>
            <p class="text-[11px] text-white/30 font-mono"><?= htmlspecialchars($svc['sys_id']) ?></p>
            <p class="text-xs text-white/30 mt-1 max-w-md"><?= htmlspecialchars($svc['description']) ?></p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button onclick='editService(<?= json_encode($svc, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="edit" class="w-3 h-3 inline"></i></button>
          <button onclick="toggleService('<?= htmlspecialchars($svc['sys_id'], ENT_QUOTES) ?>')" class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="<?= $svc['is_active']?'eye-off':'eye' ?>" class="w-3 h-3 inline"></i></button>
          <button onclick="deleteService('<?= htmlspecialchars($svc['sys_id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($svc['name'],ENT_QUOTES) ?>')" class="px-3 py-1.5 text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg"><i data-lucide="trash-2" class="w-3 h-3 inline"></i></button>
        </div>
      </div>
      <div class="flex flex-wrap gap-3 pt-3 border-t border-white/5">
        <?php foreach ($svc['prices'] as $p): ?>
          <div class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-xl pl-3 pr-1 py-1">
            <span class="text-xs font-medium"><?= htmlspecialchars($p['category']) ?></span>
            <div class="flex items-center bg-dark border border-white/10 rounded-lg overflow-hidden">
              <span class="pl-2 text-[10px] text-white/30">SR</span>
              <input type="number" step="0.01" value="<?= number_format((float)$p['price'],2,'.','') ?>"
                     class="price-input w-20 bg-transparent px-1.5 py-1 text-xs font-bold text-secondary border-2 border-transparent"
                     data-sys-id="<?= htmlspecialchars($svc['sys_id']) ?>" data-category="<?= htmlspecialchars($p['category'],ENT_QUOTES) ?>">
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (!$svc['prices']): ?><span class="text-xs text-white/20">No category prices set.</span><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$services): ?>
    <div class="text-center py-16 text-white/30 bg-white/5 border border-white/10 rounded-2xl"><i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-30"></i><p class="text-sm">No Moyallem services yet.</p></div>
    <?php endif; ?>
  </div>
</main>
</div>

<!-- Modal -->
<div id="modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-lg my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg" id="modal-title">New Moyallem Service</h2>
      <button onclick="closeModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="service-form" class="p-6 space-y-5">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="sys_id" value="">
      <input type="hidden" name="category_prices" value="[]">

      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Service Name</label>
        <input type="text" name="name" required placeholder="e.g. Moyallem in Makkah" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
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

      <div class="border-t border-white/10 pt-4">
        <p class="text-xs text-white/40 uppercase tracking-wider mb-3">Category & Price (SAR)</p>
        <div id="price-rows" class="space-y-2 mb-2"></div>
        <datalist id="moyallem-categories"><option value="General"><option value="Expert"><option value="VIP"></datalist>
        <button type="button" onclick="addPriceRow()" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-2 rounded-lg flex items-center gap-1.5"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Category</button>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="flex-1 bg-white/5 hover:bg-white/10 font-bold py-3 rounded-xl text-sm">Cancel</button>
        <button type="submit" class="flex-1 bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm" id="service-btn"><i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save</button>
      </div>
    </form>
  </div>
</div>
<script>
lucide.createIcons();
const csrf = '<?= htmlspecialchars($csrf) ?>';

function getPriceRows() {
  return [...document.querySelectorAll('#price-rows > div')].map(row => ({
    category: row.querySelector('.category-input').value.trim(),
    price: parseFloat(row.querySelector('.price-field').value) || 0
  })).filter(p => p.category);
}
function syncPriceRows() {
  document.querySelector('[name="category_prices"]').value = JSON.stringify(getPriceRows());
}
function addPriceRow(category='', price=0) {
  const div = document.createElement('div');
  div.className = 'flex gap-2';
  div.innerHTML = `
    <input type="text" list="moyallem-categories" value="${category.replace(/"/g,'&quot;')}" placeholder="e.g. General" class="category-input flex-1 bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none" oninput="syncPriceRows()">
    <input type="number" step="0.01" value="${price}" placeholder="Price SAR" class="price-field w-32 bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none" oninput="syncPriceRows()">
    <button type="button" onclick="this.parentElement.remove(); syncPriceRows();" class="px-3 bg-white/5 hover:bg-red-500/20 hover:text-red-400 rounded-xl"><i data-lucide="x" class="w-4 h-4"></i></button>`;
  document.getElementById('price-rows').appendChild(div);
  lucide.createIcons();
  syncPriceRows();
}

function openModal(svc=null) {
  const form = document.getElementById('service-form');
  form.reset();
  document.getElementById('price-rows').innerHTML = '';
  document.getElementById('modal-title').textContent = svc ? 'Edit Moyallem Service' : 'New Moyallem Service';
  // form.action and form.id are RESERVED HTMLFormElement properties (the
  // submit URL and the element's own id attribute) — they always shadow
  // same-named child inputs, so setting .value on them silently does
  // nothing. form.elements[...] correctly reaches the actual named
  // <input> regardless of any reserved-property collision.
  form.elements['action'].value = svc ? 'update' : 'create';
  form.elements['sys_id'].value = svc ? (svc.sys_id || '') : '';
  if (svc) {
    form.elements['name'].value = svc.name || '';
    form.elements['description'].value = svc.description || '';
    form.elements['icon'].value = svc.icon || 'star';
    form.elements['sort_order'].value = svc.sort_order || 0;
    (svc.prices || []).forEach(p => addPriceRow(p.category, p.price));
  } else {
    addPriceRow('General', 0);
    addPriceRow('Expert', 0);
    addPriceRow('VIP', 0);
  }
  document.getElementById('modal-overlay').classList.add('open');
}
function editService(svc) { openModal(svc); }
function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); }

document.getElementById('service-form').addEventListener('submit', async e => {
  e.preventDefault();
  syncPriceRows();
  const btn = document.getElementById('service-btn');
  btn.disabled = true; btn.textContent = 'Saving...';
  const res = await fetch(`<?= BASE_URL ?>/api/moyallem-handler.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false; btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save'; lucide.createIcons();
  if (data.success) { closeModal(); setTimeout(()=>location.reload(), 700); }
});

async function toggleService(sysId) {
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','toggle'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/moyallem-handler.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}
async function deleteService(sysId, name) {
  if (!confirm(`Delete "${name}"?`)) return;
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','delete'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/moyallem-handler.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}

// Quick inline price editing on the list view — one category at a time
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
    fd.append('category', input.dataset.category);
    fd.append('price', input.value);
    const res = await fetch(`<?= BASE_URL ?>/api/moyallem-handler.php`, {method:'POST', body:fd});
    const data = await res.json();
    if (data.success) { showToast('Price updated.', true); }
    else { showToast(data.message || 'Failed to update price.', false); input.value = original; }
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