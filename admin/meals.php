<?php
// FILE PATH: /admin/meals.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
$csrf = csrfToken();
try {
    $db = getDB();
    $meals = $db->query("SELECT * FROM meals ORDER BY sort_order, name")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($meals as &$m) { $m['tiers'] = json_decode($m['price_tiers'], true) ?: []; }
    unset($m);
} catch (Exception $e) { $meals = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Meals | TravHub Admin</title>
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
    <div><h1 class="text-2xl font-bold">Meals</h1><p class="text-white/40 text-sm">Meal plans priced by Economy/Premium/Luxury tier, with a minimum group size. Click a price to edit it instantly.</p></div>
    <button onclick="openModal()" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New Meal Plan</button>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <div class="space-y-3">
    <?php foreach ($meals as $m): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5" data-sys-id="<?= htmlspecialchars($m['sys_id']) ?>">
      <div class="flex items-start justify-between gap-3 flex-wrap mb-3">
        <div>
          <div class="flex items-center gap-2">
            <span class="font-semibold text-sm"><?= htmlspecialchars($m['name']) ?></span>
            <?php if(!$m['is_active']): ?><span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full">Inactive</span><?php endif; ?>
          </div>
          <p class="text-[11px] text-white/30 font-mono"><?= htmlspecialchars($m['sys_id']) ?></p>
          <p class="text-xs text-white/30 mt-1">Requires at least <span class="text-white/60 font-medium"><?= (int)$m['min_adults_required'] ?></span> adults to book.</p>
        </div>
        <div class="flex items-center gap-2">
          <button onclick='editMeal(<?= json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="edit" class="w-3 h-3 inline"></i></button>
          <button onclick="toggleMeal('<?= htmlspecialchars($m['sys_id'], ENT_QUOTES) ?>')" class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="<?= $m['is_active']?'eye-off':'eye' ?>" class="w-3 h-3 inline"></i></button>
          <button onclick="deleteMeal('<?= htmlspecialchars($m['sys_id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($m['name'],ENT_QUOTES) ?>')" class="px-3 py-1.5 text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg"><i data-lucide="trash-2" class="w-3 h-3 inline"></i></button>
        </div>
      </div>
      <div class="flex flex-wrap gap-3 pt-3 border-t border-white/5">
        <?php foreach ($m['tiers'] as $t): ?>
          <div class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-xl pl-3 pr-1 py-1">
            <span class="text-xs font-medium"><?= htmlspecialchars($t['tier']) ?></span>
            <div class="flex items-center bg-dark border border-white/10 rounded-lg overflow-hidden">
              <span class="pl-2 text-[10px] text-white/30">SR</span>
              <input type="number" step="0.01" value="<?= number_format((float)$t['price'],2,'.','') ?>"
                     class="price-input w-20 bg-transparent px-1.5 py-1 text-xs font-bold text-secondary border-2 border-transparent"
                     data-sys-id="<?= htmlspecialchars($m['sys_id']) ?>" data-tier="<?= htmlspecialchars($t['tier'],ENT_QUOTES) ?>">
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (!$m['tiers']): ?><span class="text-xs text-white/20">No tier prices set.</span><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$meals): ?>
    <div class="text-center py-16 text-white/30 bg-white/5 border border-white/10 rounded-2xl"><i data-lucide="utensils" class="w-10 h-10 mx-auto mb-2 opacity-30"></i><p class="text-sm">No meal plans yet.</p></div>
    <?php endif; ?>
  </div>
</main>
</div>

<!-- Modal -->
<div id="modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-lg my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg" id="modal-title">New Meal Plan</h2>
      <button onclick="closeModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="meal-form" class="p-6 space-y-5">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="sys_id" value="">
      <input type="hidden" name="price_tiers" value="[]">

      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Plan Name</label>
        <input type="text" name="name" required value="Meal Package" placeholder="e.g. Meal Package" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Minimum Adults Required</label>
        <input type="number" name="min_adults_required" value="10" min="1" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
        <p class="text-[11px] text-white/30 mt-1">The builder blocks selecting this meal plan below this adult count.</p>
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Sort Order</label>
        <input type="number" name="sort_order" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>

      <div class="border-t border-white/10 pt-4">
        <p class="text-xs text-white/40 uppercase tracking-wider mb-3">Tier & Price (SAR, per person)</p>
        <div id="tier-rows" class="space-y-2 mb-2"></div>
        <datalist id="meal-tiers"><option value="Economy"><option value="Premium"><option value="Luxury"></datalist>
        <button type="button" onclick="addTierRow()" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-2 rounded-lg flex items-center gap-1.5"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Tier</button>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="flex-1 bg-white/5 hover:bg-white/10 font-bold py-3 rounded-xl text-sm">Cancel</button>
        <button type="submit" class="flex-1 bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm" id="meal-btn"><i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save</button>
      </div>
    </form>
  </div>
</div>
<script>
lucide.createIcons();
const csrf = '<?= htmlspecialchars($csrf) ?>';

function getTierRows() {
  return [...document.querySelectorAll('#tier-rows > div')].map(row => ({
    tier: row.querySelector('.tier-input').value.trim(),
    price: parseFloat(row.querySelector('.price-field').value) || 0
  })).filter(t => t.tier);
}
function syncTierRows() {
  document.querySelector('[name="price_tiers"]').value = JSON.stringify(getTierRows());
}
function addTierRow(tier='', price=0) {
  const div = document.createElement('div');
  div.className = 'flex gap-2';
  div.innerHTML = `
    <input type="text" list="meal-tiers" value="${tier.replace(/"/g,'&quot;')}" placeholder="e.g. Economy" class="tier-input flex-1 bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none" oninput="syncTierRows()">
    <input type="number" step="0.01" value="${price}" placeholder="Price SAR" class="price-field w-32 bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none" oninput="syncTierRows()">
    <button type="button" onclick="this.parentElement.remove(); syncTierRows();" class="px-3 bg-white/5 hover:bg-red-500/20 hover:text-red-400 rounded-xl"><i data-lucide="x" class="w-4 h-4"></i></button>`;
  document.getElementById('tier-rows').appendChild(div);
  lucide.createIcons();
  syncTierRows();
}

function openModal(m=null) {
  const form = document.getElementById('meal-form');
  form.reset();
  document.getElementById('tier-rows').innerHTML = '';
  document.getElementById('modal-title').textContent = m ? 'Edit Meal Plan' : 'New Meal Plan';
  // form.action and form.id are RESERVED HTMLFormElement properties (the
  // submit URL and the element's own id attribute) — they always shadow
  // same-named child inputs, so setting .value on them silently does
  // nothing. form.elements[...] correctly reaches the actual named
  // <input> regardless of any reserved-property collision.
  form.elements['action'].value = m ? 'update' : 'create';
  form.elements['sys_id'].value = m ? (m.sys_id || '') : '';
  if (m) {
    form.elements['name'].value = m.name || 'Meal Package';
    form.elements['min_adults_required'].value = m.min_adults_required || 10;
    form.elements['sort_order'].value = m.sort_order || 0;
    (m.tiers || []).forEach(t => addTierRow(t.tier, t.price));
  } else {
    addTierRow('Economy', 0);
    addTierRow('Premium', 0);
    addTierRow('Luxury', 0);
  }
  document.getElementById('modal-overlay').classList.add('open');
}
function editMeal(m) { openModal(m); }
function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); }

document.getElementById('meal-form').addEventListener('submit', async e => {
  e.preventDefault();
  syncTierRows();
  const btn = document.getElementById('meal-btn');
  btn.disabled = true; btn.textContent = 'Saving...';
  const res = await fetch(`<?= BASE_URL ?>/api/meals.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false; btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save'; lucide.createIcons();
  if (data.success) { closeModal(); setTimeout(()=>location.reload(), 700); }
});

async function toggleMeal(sysId) {
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','toggle'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/meals.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}
async function deleteMeal(sysId, name) {
  if (!confirm(`Delete "${name}"?`)) return;
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','delete'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/meals.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}

// Quick inline price editing on the list view — one tier at a time
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
    fd.append('tier', input.dataset.tier);
    fd.append('price', input.value);
    const res = await fetch(`<?= BASE_URL ?>/api/meals.php`, {method:'POST', body:fd});
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