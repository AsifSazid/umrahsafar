<?php
// FILE PATH: /admin/transport.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
$csrf = csrfToken();
try {
    $db = getDB();
    $routes = $db->query("SELECT * FROM transport_routes ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($routes as &$r) {
        $r['destinations']    = json_decode($r['destinations'], true) ?: [];
        $r['vehicle_options'] = json_decode($r['vehicle_options'], true) ?: [];
    }
    unset($r);
    $vehicleTypes = $db->query("SELECT * FROM vehicle_types WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll(PDO::FETCH_ASSOC);
    // sys_id -> name lookup, so route cards can show "Car" instead of a raw sys_id
    $vehicleNameBySysId = [];
    foreach ($vehicleTypes as $v) { $vehicleNameBySysId[$v['sys_id']] = $v['name']; }
} catch (Exception $e) { $routes = []; $vehicleTypes = []; $vehicleNameBySysId = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Transport Routes | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Poppins','sans-serif']},colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625',emerald:'#3AAB71',teal:'#02CCFE'}}}}</script>
<style>body{background:#111625}#modal-overlay{display:none}#modal-overlay.open{display:flex}</style>
</head>
<body class="text-white font-sans min-h-screen">
<div class="flex min-h-screen">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="flex-1 p-4 lg:p-8 overflow-auto">
  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div><h1 class="text-2xl font-bold">Transport Routes</h1><p class="text-white/40 text-sm">Multi-stop routes with per-vehicle pricing (SAR base price)</p></div>
    <button onclick="openModal()" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New Route</button>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <?php if (!$vehicleTypes): ?>
  <div class="bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm rounded-xl p-4 mb-5 flex items-center gap-2">
    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i> No vehicle types yet — <a href="<?= BASE_URL ?>/admin/vehicle-types.php" class="underline font-bold">add some first</a> before creating routes.
  </div>
  <?php endif; ?>

  <!-- Routes list -->
  <div class="space-y-3">
    <?php foreach ($routes as $r): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
      <div class="flex items-start justify-between gap-3 flex-wrap">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="font-semibold text-sm"><?= htmlspecialchars($r['origin']) ?></span>
            <?php foreach ($r['destinations'] as $stop): ?>
              <i data-lucide="arrow-right" class="w-3 h-3 text-white/30"></i>
              <span class="font-semibold text-sm"><?= htmlspecialchars($stop) ?></span>
            <?php endforeach; ?>
            <?php if(!$r['is_active']): ?><span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full ml-1">Inactive</span><?php endif; ?>
          </div>
          <p class="text-[11px] text-white/30 font-mono"><?= htmlspecialchars($r['slug']) ?> · <?= htmlspecialchars($r['sys_id']) ?></p>
        </div>
        <div class="flex items-center gap-2">
          <button onclick='editRoute(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="edit" class="w-3 h-3 inline"></i></button>
          <button onclick="toggleRoute('<?= htmlspecialchars($r['sys_id'], ENT_QUOTES) ?>')" class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="<?= $r['is_active']?'eye-off':'eye' ?>" class="w-3 h-3 inline"></i></button>
          <button onclick="deleteRoute('<?= htmlspecialchars($r['sys_id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($r['slug'],ENT_QUOTES) ?>')" class="px-3 py-1.5 text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg"><i data-lucide="trash-2" class="w-3 h-3 inline"></i></button>
        </div>
      </div>
      <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-white/5">
        <?php foreach ($r['vehicle_options'] as $vo): ?>
          <span class="text-xs bg-white/5 border border-white/10 px-3 py-1 rounded-full">
            <?= htmlspecialchars($vehicleNameBySysId[$vo['vehicle_type_sys_id']] ?? $vo['vehicle_type_sys_id']) ?> · <span class="text-secondary font-mono"><?= number_format($vo['price'],0) ?> SAR</span>
          </span>
        <?php endforeach; ?>
        <?php if (!$r['vehicle_options']): ?><span class="text-xs text-white/20">No vehicle prices set.</span><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$routes): ?>
    <div class="text-center py-16 text-white/30 bg-white/5 border border-white/10 rounded-2xl"><i data-lucide="map" class="w-10 h-10 mx-auto mb-2 opacity-30"></i><p class="text-sm">No routes yet.</p></div>
    <?php endif; ?>
  </div>
</main>
</div>

<!-- Modal -->
<div id="modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-2xl my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg" id="modal-title">New Route</h2>
      <button onclick="closeModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="route-form" class="p-6 space-y-5">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="create_route">
      <input type="hidden" name="sys_id" value="">
      <input type="hidden" name="destinations" value="[]">
      <input type="hidden" name="vehicle_options" value="[]">

      <!-- Route section -->
      <div>
        <p class="text-xs text-white/40 uppercase tracking-wider mb-3">Route</p>
        <div class="grid grid-cols-2 gap-3 mb-3">
          <div><label class="text-xs text-white/40 block mb-1.5">Origin</label>
            <input type="text" name="origin" required placeholder="e.g. Makkah" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none" oninput="renderSlugPreview()"></div>
          <div><label class="text-xs text-white/40 block mb-1.5">Sort Order</label>
            <input type="number" name="sort_order" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        </div>
        <label class="text-xs text-white/40 block mb-1.5">Destinations (stops, in order — supports circuits e.g. Makkah → Madinah → Makkah)</label>
        <div id="destinations-list" class="space-y-2 mb-2"></div>
        <button type="button" onclick="addDestination()" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-2 rounded-lg flex items-center gap-1.5"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Destination</button>
        <p class="text-[11px] text-white/20 font-mono mt-2">Slug: <span id="slug-preview">—</span></p>
      </div>

      <!-- Vehicle pricing section -->
      <div class="border-t border-white/10 pt-4">
        <p class="text-xs text-white/40 uppercase tracking-wider mb-3">Vehicle Type & Price (SAR)</p>
        <div id="vehicle-rows" class="space-y-2 mb-2"></div>
        <button type="button" onclick="addVehicleRow()" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-2 rounded-lg flex items-center gap-1.5"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Vehicle Type</button>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="flex-1 bg-white/5 hover:bg-white/10 font-bold py-3 rounded-xl text-sm">Cancel</button>
        <button type="submit" class="flex-1 bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm" id="route-btn"><i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save Route</button>
      </div>
    </form>
  </div>
</div>
<script>
lucide.createIcons();
const csrf = '<?= htmlspecialchars($csrf) ?>';
const VEHICLE_TYPES = <?= json_encode($vehicleTypes) ?>; // each has sys_id + name

function slugify(origin, stops) {
  const clean = s => (s||'').replace(/[^A-Za-z0-9]+/g,'');
  return [origin, ...stops].map(clean).filter(Boolean).join('-');
}
function renderSlugPreview() {
  const origin = document.querySelector('[name="origin"]').value;
  const stops = getDestinations();
  document.getElementById('slug-preview').textContent = slugify(origin, stops) || '—';
}

// ── Destinations (repeatable) ──
function getDestinations() {
  return [...document.querySelectorAll('#destinations-list input')].map(i => i.value.trim()).filter(Boolean);
}
function syncDestinations() {
  document.querySelector('[name="destinations"]').value = JSON.stringify(getDestinations());
  renderSlugPreview();
}
function addDestination(value='') {
  const div = document.createElement('div');
  div.className = 'flex gap-2';
  div.innerHTML = `<input type="text" value="${value.replace(/"/g,'&quot;')}" placeholder="e.g. Madinah" class="flex-1 bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none" oninput="syncDestinations()">
    <button type="button" onclick="this.parentElement.remove(); syncDestinations();" class="px-3 bg-white/5 hover:bg-red-500/20 hover:text-red-400 rounded-xl"><i data-lucide="x" class="w-4 h-4"></i></button>`;
  document.getElementById('destinations-list').appendChild(div);
  lucide.createIcons();
  syncDestinations();
}

// ── Vehicle rows (repeatable) — select stores the vehicle's sys_id ──
function getVehicleRows() {
  return [...document.querySelectorAll('#vehicle-rows > div')].map(row => ({
    vehicle_type_sys_id: row.querySelector('select').value,
    price: parseFloat(row.querySelector('input').value) || 0
  })).filter(v => v.vehicle_type_sys_id);
}
function syncVehicleRows() {
  document.querySelector('[name="vehicle_options"]').value = JSON.stringify(getVehicleRows());
}
function addVehicleRow(vehicleSysId='', price=0) {
  if (!VEHICLE_TYPES.length) { alert('Add a vehicle type first (Admin → Vehicle Types).'); return; }
  const div = document.createElement('div');
  div.className = 'flex gap-2';
  const options = VEHICLE_TYPES.map(v => `<option value="${v.sys_id}" ${v.sys_id===vehicleSysId?'selected':''}>${v.name}</option>`).join('');
  div.innerHTML = `
    <select class="flex-1 bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none" onchange="syncVehicleRows()">${options}</select>
    <input type="number" step="0.01" value="${price}" placeholder="Price SAR" class="w-32 bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none" oninput="syncVehicleRows()">
    <button type="button" onclick="this.parentElement.remove(); syncVehicleRows();" class="px-3 bg-white/5 hover:bg-red-500/20 hover:text-red-400 rounded-xl"><i data-lucide="x" class="w-4 h-4"></i></button>`;
  document.getElementById('vehicle-rows').appendChild(div);
  lucide.createIcons();
  syncVehicleRows();
}

function openModal(route=null) {
  const form = document.getElementById('route-form');
  form.reset();
  document.getElementById('destinations-list').innerHTML = '';
  document.getElementById('vehicle-rows').innerHTML = '';
  document.getElementById('modal-title').textContent = route ? 'Edit Route' : 'New Route';
  // form.action is a RESERVED HTMLFormElement property (the submit URL) —
  // it always shadows a same-named child <input>, so setting .value on
  // it silently does nothing. form.elements[...] correctly reaches the
  // actual named <input> regardless of any reserved-property collision.
  form.elements['action'].value = route ? 'update_route' : 'create_route';
  form.elements['sys_id'].value = route ? (route.sys_id || '') : '';
  if (route) {
    form.elements['origin'].value = route.origin || '';
    form.elements['sort_order'].value = route.sort_order || 0;
    (route.destinations || []).forEach(d => addDestination(d));
    (route.vehicle_options || []).forEach(v => addVehicleRow(v.vehicle_type_sys_id, v.price));
  } else {
    addDestination();
    addVehicleRow();
  }
  renderSlugPreview();
  document.getElementById('modal-overlay').classList.add('open');
}
function editRoute(route) { openModal(route); }
function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); }

document.getElementById('route-form').addEventListener('submit', async e => {
  e.preventDefault();
  syncDestinations(); syncVehicleRows();
  const btn = document.getElementById('route-btn');
  btn.disabled = true; btn.textContent = 'Saving...';
  const res = await fetch(`<?= BASE_URL ?>/api/transport-routes.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false; btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save Route'; lucide.createIcons();
  if (data.success) { closeModal(); setTimeout(()=>location.reload(), 700); }
});

async function toggleRoute(sysId) {
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','toggle_route'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/transport-routes.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}
async function deleteRoute(sysId, slug) {
  if (!confirm(`Delete route "${slug}"?`)) return;
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','delete_route'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/transport-routes.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}
function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm ${ok?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  t.classList.remove('hidden'); setTimeout(()=>t.classList.add('hidden'),3000);
}
</script>
</body></html>