<?php
// FILE PATH: /admin/packages.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
$csrf = csrfToken();
try {
    $db = getDB();
    $packages = $db->query("SELECT * FROM packages ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $packages = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Packages | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Poppins','sans-serif']},colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625',emerald:'#3AAB71',teal:'#02CCFE'}}}}</script>
<style>
body{background:#111625}
.input-field{@apply w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none placeholder-white/20}
#modal-overlay{display:none}
#modal-overlay.open{display:flex}
</style>
</head>
<body class="text-white font-sans min-h-screen">
<div class="flex min-h-screen">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="flex-1 p-4 lg:p-8 overflow-auto">
  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div><h1 class="text-2xl font-bold">Packages</h1><p class="text-white/40 text-sm">Manage all Umrah packages</p></div>
    <button onclick="openModal()" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New Package</button>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <!-- Package List -->
  <div class="space-y-3" id="pkg-list">
    <?php foreach ($packages as $p): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5 flex flex-col md:flex-row md:items-center gap-4">
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap mb-1">
          <span class="font-bold text-base"><?= htmlspecialchars($p['name']) ?></span>
          <span class="text-[10px] bg-<?= $p['budget']==='luxury'?'yellow-400':($p['budget']==='premium'?'teal':'white/20') ?>/10 text-<?= $p['budget']==='luxury'?'yellow-400':($p['budget']==='premium'?'teal':'white/60') ?> border border-current/20 px-2 py-0.5 rounded-full uppercase tracking-wider"><?= $p['budget'] ?></span>
          <?php if(!$p['is_active']): ?><span class="text-[10px] bg-red-500/10 text-red-400 border border-red-500/20 px-2 py-0.5 rounded-full">Inactive</span><?php endif; ?>
          <?php if($p['badge']): ?><span class="text-[10px] bg-secondary/10 text-secondary border border-secondary/20 px-2 py-0.5 rounded-full"><?= htmlspecialchars($p['badge']) ?></span><?php endif; ?>
        </div>
        <div class="flex flex-wrap gap-4 text-xs text-white/40">
          <span><?= $p['duration'] ?> days</span>
          <span><?= $p['stars'] ?>★</span>
          <span class="text-secondary font-bold">SR <?= number_format($p['price_sar'],0) ?></span>
          <span><?= htmlspecialchars($p['hotel'] ?? '') ?></span>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <button onclick="editPackage(<?= $p['id'] ?>)" class="px-4 py-2 text-xs font-bold bg-white/5 hover:bg-white/10 rounded-lg flex items-center gap-1.5"><i data-lucide="edit" class="w-3.5 h-3.5"></i> Edit</button>
        <button onclick="togglePackage(<?= $p['id'] ?>)" class="px-4 py-2 text-xs font-bold bg-white/5 hover:bg-white/10 rounded-lg flex items-center gap-1.5"><i data-lucide="<?= $p['is_active']?'eye-off':'eye' ?>" class="w-3.5 h-3.5"></i> <?= $p['is_active']?'Hide':'Show' ?></button>
        <button onclick="deletePackage(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name'],ENT_QUOTES) ?>')" class="px-4 py-2 text-xs font-bold bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg flex items-center gap-1.5"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete</button>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$packages): ?><div class="text-center py-20 text-white/30"><i data-lucide="package" class="w-12 h-12 mx-auto mb-3 opacity-30"></i><p>No packages yet. Add your first package.</p></div><?php endif; ?>
  </div>
</main>
</div>

<!-- Modal -->
<div id="modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-2xl my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg" id="modal-title">New Package</h2>
      <button onclick="closeModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="pkg-form" class="p-6 space-y-4 overflow-auto max-h-[75vh]">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="id" value="">
      <div class="grid grid-cols-2 gap-3">
        <div class="col-span-2"><label class="label-xs">Package Name *</label><input type="text" name="name" required class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        <div><label class="label-xs">Budget Level</label>
          <select name="budget" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
            <option value="economy">Economy</option><option value="premium">Premium</option><option value="luxury">Luxury</option>
          </select></div>
        <div><label class="label-xs">Stars</label><input type="number" name="stars" min="1" max="5" value="3" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        <div><label class="label-xs">Price (SAR)</label><input type="number" name="price_sar" step="0.01" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        <div><label class="label-xs">Duration (days)</label><input type="number" name="duration" value="7" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        <div><label class="label-xs">Badge (e.g. Most Popular)</label><input type="text" name="badge" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        <div><label class="label-xs">Rating (0–5)</label><input type="number" name="rating" step="0.1" min="0" max="5" value="4.5" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        <div><label class="label-xs">Reviews Count</label><input type="number" name="reviews" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        <div><label class="label-xs">Sort Order</label><input type="number" name="sort_order" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        <div class="col-span-2"><label class="label-xs">Hotel Description</label><input type="text" name="hotel" placeholder="e.g. 4★ Hotel — 200m from Haram" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        <div><label class="label-xs">Flight Type</label><input type="text" name="flight" placeholder="e.g. Direct Flight" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        <div><label class="label-xs">Transport</label><input type="text" name="transport" placeholder="e.g. Private Car" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
      </div>
      <div>
        <label class="label-xs">Includes (one per line)</label>
        <textarea name="includes_raw" rows="4" placeholder="Umrah Visa&#10;Return flight&#10;Hotel accommodation" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none resize-y"></textarea>
      </div>
      <div>
        <label class="label-xs">Excludes (one per line)</label>
        <textarea name="excludes_raw" rows="3" placeholder="Personal shopping&#10;Tipping" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none resize-y"></textarea>
      </div>
      <!-- Itinerary builder -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="label-xs">Itinerary</label>
          <button type="button" onclick="addItinRow()" class="text-xs text-secondary hover:text-emerald flex items-center gap-1"><i data-lucide="plus" class="w-3 h-3"></i> Add Day</button>
        </div>
        <div id="itin-rows" class="space-y-2"></div>
      </div>
      <div class="flex items-center gap-3 pt-2">
        <label class="flex items-center gap-2 cursor-pointer text-sm"><input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-secondary rounded"> Active (visible on site)</label>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="flex-1 bg-white/5 hover:bg-white/10 font-bold py-3 rounded-xl text-sm">Cancel</button>
        <button type="submit" id="pkg-submit-btn" class="flex-1 bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm flex items-center justify-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Save Package</button>
      </div>
    </form>
  </div>
</div>

<style>
.label-xs { @apply text-xs text-white/40 uppercase tracking-wider block mb-1.5; }
</style>
<script>
lucide.createIcons();
const csrf = '<?= htmlspecialchars($csrf) ?>';
let itinCount = 0;

function addItinRow(day='', title='', desc='') {
  itinCount++;
  const r = document.createElement('div');
  r.className = 'flex gap-2 items-start';
  r.innerHTML = `<input type="number" name="itin_day[]" value="${day||itinCount}" placeholder="Day" class="w-16 shrink-0 bg-dark border border-white/10 rounded-lg px-2 py-2 text-xs focus:border-secondary focus:outline-none">
    <input type="text" name="itin_title[]" value="${title}" placeholder="Title" class="w-1/3 bg-dark border border-white/10 rounded-lg px-3 py-2 text-xs focus:border-secondary focus:outline-none">
    <input type="text" name="itin_desc[]" value="${desc}" placeholder="Description" class="flex-1 bg-dark border border-white/10 rounded-lg px-3 py-2 text-xs focus:border-secondary focus:outline-none">
    <button type="button" onclick="this.parentElement.remove()" class="text-red-400/60 hover:text-red-400 mt-1.5"><i data-lucide="x" class="w-4 h-4"></i></button>`;
  document.getElementById('itin-rows').appendChild(r);
  lucide.createIcons();
}

function openModal(pkg=null) {
  const form = document.getElementById('pkg-form');
  form.reset(); itinCount = 0; document.getElementById('itin-rows').innerHTML = '';
  document.getElementById('modal-title').textContent = pkg ? 'Edit Package' : 'New Package';
  form.action.value = pkg ? 'update' : 'create';
  if (pkg) {
    form.id_field.value = pkg.id || '';
    form.name.value = pkg.name || '';
    form.budget.value = pkg.budget || 'economy';
    form.stars.value = pkg.stars || 3;
    form.price_sar.value = pkg.price_sar || 0;
    form.duration.value = pkg.duration || 7;
    form.badge.value = pkg.badge || '';
    form.rating.value = pkg.rating || 4.5;
    form.reviews.value = pkg.reviews || 0;
    form.sort_order.value = pkg.sort_order || 0;
    form.hotel.value = pkg.hotel || '';
    form.flight.value = pkg.flight || '';
    form.transport.value = pkg.transport || '';
    form.is_active.checked = pkg.is_active == 1;
    try { form.includes_raw.value = (JSON.parse(pkg.includes_json||'[]')).join('\n'); } catch(e){}
    try { form.excludes_raw.value = (JSON.parse(pkg.excludes_json||'[]')).join('\n'); } catch(e){}
    try { const itin = JSON.parse(pkg.itinerary_json||'[]'); itin.forEach(r=>addItinRow(r.day,r.title,r.desc)); } catch(e){}
  } else { addItinRow(); }
  document.getElementById('modal-overlay').classList.add('open');
}

function closeModal() {
  document.getElementById('modal-overlay').classList.remove('open');
}

// Map includes/excludes textarea → array fields before submit
document.getElementById('pkg-form').addEventListener('submit', async e => {
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);
  // Parse includes/excludes from textarea
  const inc = (fd.get('includes_raw')||'').split('\n').map(s=>s.trim()).filter(Boolean);
  const exc = (fd.get('excludes_raw')||'').split('\n').map(s=>s.trim()).filter(Boolean);
  fd.delete('includes_raw'); fd.delete('excludes_raw');
  inc.forEach(v=>fd.append('includes[]', v));
  exc.forEach(v=>fd.append('excludes[]', v));
  const btn = document.getElementById('pkg-submit-btn');
  btn.disabled = true; btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin inline mr-1"></i>Saving...'; lucide.createIcons();
  const res = await fetch(`<?= BASE_URL ?>/api/packages-handler.php`, {method:'POST', body:fd});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false; btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save Package'; lucide.createIcons();
  if (data.success) { closeModal(); setTimeout(()=>location.reload(), 800); }
});

async function editPackage(id) {
  const res = await fetch(`<?= BASE_URL ?>/api/packages-handler.php?id=${id}`);
  const data = await res.json();
  if (data.success) openModal(data.package);
}

async function togglePackage(id) {
  const fd = new FormData();
  fd.append('csrf_token', csrf); fd.append('action','toggle'); fd.append('id', id);
  const res = await fetch(`<?= BASE_URL ?>/api/packages-handler.php`, {method:'POST', body:fd});
  const data = await res.json();
  showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}

async function deletePackage(id, name) {
  if (!confirm(`Delete package "${name}"? This cannot be undone.`)) return;
  const fd = new FormData();
  fd.append('csrf_token', csrf); fd.append('action','delete'); fd.append('id', id);
  const res = await fetch(`<?= BASE_URL ?>/api/packages-handler.php`, {method:'POST', body:fd});
  const data = await res.json();
  showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}

function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm ${ok?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  t.classList.remove('hidden');
  setTimeout(()=>t.classList.add('hidden'), 3000);
}

// Fix form id field name conflict
document.querySelector('[name="id"]').setAttribute('name','id_field');
</script>
</body></html>