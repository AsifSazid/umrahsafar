<?php
// FILE PATH: /admin/vehicle-types.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
$csrf = csrfToken();
try {
    $db = getDB();
    $types = $db->query("SELECT * FROM vehicle_types ORDER BY sort_order, name")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($types as &$t) {
        $t['capacities'] = json_decode($t['capacities'] ?? 'null', true);
        $t['images']     = json_decode($t['images'] ?? '[]', true) ?: [];
    }
    unset($t);
} catch (Exception $e) { $types = []; }
$ICONS = ['car','truck','bus','train-front','ship','bike','caravan'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Vehicle Types | TravHub Admin</title>
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
    <div><h1 class="text-2xl font-bold">Vehicle Types</h1><p class="text-white/40 text-sm">Manage the vehicle types available across all transport routes</p></div>
    <button onclick="openModal()" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New Vehicle Type</button>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <div class="space-y-3">
    <?php foreach ($types as $t): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5" data-sys-id="<?= htmlspecialchars($t['sys_id']) ?>">
      <div class="flex items-start justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-3 min-w-0">
          <?php if ($t['images']): ?>
            <img src="<?= BASE_URL . '/' . htmlspecialchars($t['images'][0]) ?>" class="w-12 h-12 rounded-xl object-cover bg-dark shrink-0">
          <?php else: ?>
            <div class="w-12 h-12 rounded-xl bg-dark flex items-center justify-center shrink-0"><i data-lucide="<?= htmlspecialchars($t['icon']) ?>" class="w-5 h-5 text-secondary"></i></div>
          <?php endif; ?>
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-semibold text-sm"><?= htmlspecialchars($t['name']) ?></span>
              <?php if(!$t['is_active']): ?><span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full">Inactive</span><?php endif; ?>
            </div>
            <p class="text-xs text-white/30">
              <?php if ($t['capacities']): ?>
                <?= (int)($t['capacities']['seat'] ?? 0) ?> seats · <?= (int)($t['capacities']['luggage'] ?? 0) ?> luggage
              <?php else: ?>
                Capacity not set
              <?php endif; ?>
              · <?= count($t['images']) ?> image<?= count($t['images'])===1?'':'s' ?>
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <button onclick='editType(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-3 py-2 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
          <button onclick="toggleType('<?= htmlspecialchars($t['sys_id'], ENT_QUOTES) ?>')" class="px-3 py-2 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="<?= $t['is_active']?'eye-off':'eye' ?>" class="w-3.5 h-3.5"></i></button>
          <button onclick="deleteType('<?= htmlspecialchars($t['sys_id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($t['name'],ENT_QUOTES) ?>')" class="px-3 py-2 text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$types): ?>
    <div class="text-center py-16 text-white/30 bg-white/5 border border-white/10 rounded-2xl"><i data-lucide="truck" class="w-10 h-10 mx-auto mb-2 opacity-30"></i><p class="text-sm">No vehicle types yet.</p></div>
    <?php endif; ?>
  </div>
</main>
</div>

<div id="modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-lg my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg" id="modal-title">New Vehicle Type</h2>
      <button onclick="closeModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="type-form" class="p-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="sys_id" value="">
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Name</label>
        <input type="text" name="name" required placeholder="e.g. Minibus" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Icon</label>
        <select name="icon" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
          <?php foreach ($ICONS as $ic): ?><option value="<?= $ic ?>"><?= $ic ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Total Seat</label>
          <input type="number" name="capacity_seat" min="0" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
        <div><label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Total Luggage Capacity (Big)</label>
          <input type="number" name="capacity_luggage" min="0" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none"></div>
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Sort Order</label>
        <input type="number" name="sort_order" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>

      <!-- Images — for a new vehicle type, files are held locally and
           uploaded automatically right after the type is created
           (the upload needs a sys_id, which only exists once saved). -->
      <div id="images-section" class="border-t border-white/10 pt-4">
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-2">Images (JPG/PNG, any number, no size limit)</label>
        <div id="image-gallery" class="flex flex-wrap gap-2 mb-3"></div>
        <input type="file" id="image-input" accept="image/jpeg,image/png" multiple class="text-xs text-white/50 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-white/5 file:text-white/70 file:text-xs hover:file:bg-white/10">
        <p id="image-status" class="text-[10px] text-white/30 mt-1"></p>
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
let currentSysId = '';
let currentImages = [];
let pendingFiles = []; // File objects picked before the vehicle type has a sys_id yet
let pendingPreviewUrls = [];

function renderGallery() {
  const gallery = document.getElementById('image-gallery');
  const saved = currentImages.map(path => `
    <div class="relative w-16 h-16 rounded-lg overflow-hidden bg-dark group">
      <img src="<?= BASE_URL ?>/${path}" class="w-full h-full object-cover">
      <button type="button" onclick="removeImage('${path.replace(/'/g,"\\'")}')"
        class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
        <i data-lucide="trash-2" class="w-4 h-4 text-red-400"></i>
      </button>
    </div>`).join('');
  const pending = pendingPreviewUrls.map((url, i) => `
    <div class="relative w-16 h-16 rounded-lg overflow-hidden bg-dark group border-2 border-dashed border-secondary/50">
      <img src="${url}" class="w-full h-full object-cover opacity-70">
      <button type="button" onclick="removePendingFile(${i})"
        class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
        <i data-lucide="x" class="w-4 h-4 text-white"></i>
      </button>
      <span class="absolute bottom-0 inset-x-0 bg-secondary/80 text-primary text-[8px] text-center font-bold py-0.5">Pending</span>
    </div>`).join('');
  gallery.innerHTML = saved + pending;
  lucide.createIcons();
}

function removePendingFile(index) {
  URL.revokeObjectURL(pendingPreviewUrls[index]);
  pendingFiles.splice(index, 1);
  pendingPreviewUrls.splice(index, 1);
  renderGallery();
}

function openModal(t=null) {
  const form = document.getElementById('type-form');
  form.reset();
  document.getElementById('modal-title').textContent = t ? 'Edit Vehicle Type' : 'New Vehicle Type';
  // form.action is a RESERVED HTMLFormElement property (the submit URL) —
  // it always shadows a same-named child <input>, so setting .value on
  // it silently does nothing. form.elements[...] correctly reaches the
  // actual named <input> regardless of any reserved-property collision.
  form.elements['action'].value = t ? 'update' : 'create';
  form.elements['sys_id'].value = t ? (t.sys_id || '') : '';
  currentSysId = t ? (t.sys_id || '') : '';
  currentImages = t ? (t.images || []) : [];
  pendingPreviewUrls.forEach(url => URL.revokeObjectURL(url));
  pendingFiles = [];
  pendingPreviewUrls = [];

  if (t) {
    form.elements['name'].value = t.name || '';
    form.elements['icon'].value = t.icon || 'car';
    form.elements['capacity_seat'].value = (t.capacities && t.capacities.seat) || 0;
    form.elements['capacity_luggage'].value = (t.capacities && t.capacities.luggage) || 0;
    form.elements['sort_order'].value = t.sort_order || 0;
  }
  renderGallery();
  document.getElementById('modal-overlay').classList.add('open');
}
function editType(t) { openModal(t); }
function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); }

document.getElementById('type-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('type-btn');
  btn.disabled = true; btn.textContent = 'Saving...';
  const res = await fetch(`<?= BASE_URL ?>/api/vehicle-types.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();

  // Brand-new vehicle type just got its sys_id — upload any images that
  // were picked before saving, using that sys_id now.
  const newSysId = data.sys_id || currentSysId;
  if (data.success && pendingFiles.length && newSysId) {
    btn.textContent = 'Uploading images...';
    const imgFd = new FormData();
    imgFd.append('csrf_token', csrf);
    imgFd.append('action', 'upload_image');
    imgFd.append('sys_id', newSysId);
    pendingFiles.forEach(f => imgFd.append('images[]', f));
    const imgRes = await fetch(`<?= BASE_URL ?>/api/vehicle-types.php`, {method:'POST', body: imgFd});
    const imgData = await imgRes.json();
    if (!imgData.success) showToast(imgData.message, false);
  }

  showToast(data.message, data.success);
  btn.disabled = false; btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save'; lucide.createIcons();
  if (data.success) { closeModal(); setTimeout(()=>location.reload(), 700); }
});

document.getElementById('image-input').addEventListener('change', async function() {
  if (!this.files.length) return;

  // Already-saved vehicle type (has a sys_id) — upload right away, as before.
  if (currentSysId) {
    const status = document.getElementById('image-status');
    status.textContent = 'Uploading...';
    const fd = new FormData();
    fd.append('csrf_token', csrf);
    fd.append('action', 'upload_image');
    fd.append('sys_id', currentSysId);
    for (const file of this.files) fd.append('images[]', file);
    const res = await fetch(`<?= BASE_URL ?>/api/vehicle-types.php`, {method:'POST', body:fd});
    const data = await res.json();
    showToast(data.message, data.success);
    status.textContent = '';
    this.value = '';
    if (data.images) { currentImages = data.images; renderGallery(); }
    return;
  }

  // Brand-new vehicle type — no sys_id yet, so just hold the files
  // locally (with a local preview) until the form is actually saved.
  for (const file of this.files) {
    pendingFiles.push(file);
    pendingPreviewUrls.push(URL.createObjectURL(file));
  }
  this.value = '';
  renderGallery();
});

async function removeImage(path) {
  if (!confirm('Remove this image?')) return;
  const fd = new FormData();
  fd.append('csrf_token', csrf);
  fd.append('action', 'delete_image');
  fd.append('sys_id', currentSysId);
  fd.append('path', path);
  const res = await fetch(`<?= BASE_URL ?>/api/vehicle-types.php`, {method:'POST', body:fd});
  const data = await res.json();
  showToast(data.message, data.success);
  if (data.success) { currentImages = data.images; renderGallery(); }
}

async function toggleType(sysId) {
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','toggle'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/vehicle-types.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}
async function deleteType(sysId, name) {
  if (!confirm(`Delete vehicle type "${name}"?`)) return;
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','delete'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/vehicle-types.php`,{method:'POST',body:fd});
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