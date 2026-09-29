<?php
// FILE PATH: /admin/ziarah.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
$csrf = csrfToken();
try {
    $db = getDB();
    $tours = $db->query("SELECT * FROM ziarah ORDER BY sort_order, name")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tours as &$z) {
        $z['itinerary_arr']     = json_decode($z['itinerary'], true) ?: [];
        $z['route_sys_ids_arr'] = json_decode($z['route_sys_ids'], true) ?: [];
    }
    unset($z);
    $routes = $db->query("SELECT sys_id, slug, origin, destinations FROM transport_routes ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($routes as &$r) {
        $stops = json_decode($r['destinations'], true) ?: [];
        $r['label'] = implode(' → ', array_merge([$r['origin']], $stops));
    }
    unset($r);
} catch (Exception $e) { $tours = []; $routes = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ziarah | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Poppins','sans-serif']},colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625',emerald:'#3AAB71',teal:'#02CCFE'}}}}</script>
<style>
body{background:#111625}
#modal-overlay{display:none}#modal-overlay.open{display:flex}
.rte-toolbar button{ transition: background .15s; }
.rte-toolbar button:hover{ background: rgba(255,255,255,0.1); }
.rte-editor{ min-height: 90px; }
.rte-editor:empty:before{ content: attr(data-placeholder); color: rgba(255,255,255,0.25); }
.rte-editor p{ margin: 0 0 0.5em 0; }
.rte-editor ul, .rte-editor ol{ margin: 0 0 0.5em 1.2em; }
</style>
</head>
<body class="text-white font-sans min-h-screen">
<div class="flex min-h-screen">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="flex-1 p-4 lg:p-8 overflow-auto">
  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div><h1 class="text-2xl font-bold">Ziarah</h1><p class="text-white/40 text-sm">Guided historical & spiritual site visits — itinerary, linked routes, Moyallem option, pricing.</p></div>
    <button onclick="openModal()" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New Ziarah</button>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <?php if (!$routes): ?>
  <div class="bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm rounded-xl p-4 mb-5 flex items-center gap-2">
    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i> No transport routes yet — <a href="<?= BASE_URL ?>/admin/transport.php" class="underline font-bold">add some first</a> so you can link Ziarah tours to a route.
  </div>
  <?php endif; ?>

  <div class="space-y-3">
    <?php foreach ($tours as $z): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5" data-sys-id="<?= htmlspecialchars($z['sys_id']) ?>">
      <div class="flex items-start justify-between gap-3 flex-wrap mb-3">
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-semibold text-sm"><?= htmlspecialchars($z['name']) ?></span>
            <?php if(!$z['is_active']): ?><span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full">Inactive</span><?php endif; ?>
            <?php if($z['moyallem_enabled']): ?><span class="text-[9px] bg-secondary/10 text-secondary px-2 py-0.5 rounded-full">Moyallem available</span><?php endif; ?>
          </div>
          <p class="text-[11px] text-white/30 font-mono"><?= htmlspecialchars($z['sys_id']) ?> · <?= htmlspecialchars($z['possible_duration']) ?></p>
          <p class="text-xs text-white/40 mt-1 max-w-lg"><?= htmlspecialchars($z['description']) ?></p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
          <span class="text-secondary font-bold text-sm">SR <?= number_format((float)$z['price'],0) ?></span>
          <button onclick='editZiarah(<?= json_encode($z, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="edit" class="w-3 h-3 inline"></i></button>
          <button onclick="toggleZiarah('<?= htmlspecialchars($z['sys_id'], ENT_QUOTES) ?>')" class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="<?= $z['is_active']?'eye-off':'eye' ?>" class="w-3 h-3 inline"></i></button>
          <button onclick="deleteZiarah('<?= htmlspecialchars($z['sys_id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($z['name'],ENT_QUOTES) ?>')" class="px-3 py-1.5 text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg"><i data-lucide="trash-2" class="w-3 h-3 inline"></i></button>
        </div>
      </div>
      <div class="flex flex-wrap gap-2 pt-3 border-t border-white/5">
        <?php foreach ($z['itinerary_arr'] as $item): ?>
          <span class="text-[11px] bg-white/5 border border-white/10 px-2.5 py-1 rounded-full text-white/60"><?= htmlspecialchars($item['title']) ?></span>
        <?php endforeach; ?>
        <?php if (!$z['itinerary_arr']): ?><span class="text-xs text-white/20">No itinerary items yet.</span><?php endif; ?>
      </div>
      <?php
        $linkedLabels = [];
        foreach ($routes as $r) { if (in_array($r['sys_id'], $z['route_sys_ids_arr'], true)) $linkedLabels[] = $r['label']; }
      ?>
      <?php if ($linkedLabels): ?>
      <div class="flex items-center gap-1.5 mt-2 text-[11px] text-white/30"><i data-lucide="route" class="w-3 h-3"></i> <?= htmlspecialchars(implode(' · ', $linkedLabels)) ?></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if (!$tours): ?>
    <div class="text-center py-16 text-white/30 bg-white/5 border border-white/10 rounded-2xl"><i data-lucide="landmark" class="w-10 h-10 mx-auto mb-2 opacity-30"></i><p class="text-sm">No Ziarah tours yet.</p></div>
    <?php endif; ?>
  </div>
</main>
</div>

<!-- Modal -->
<div id="modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-2xl my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg" id="modal-title">New Ziarah</h2>
      <button onclick="closeModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="ziarah-form" class="p-6 space-y-5">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="sys_id" value="">
      <input type="hidden" name="itinerary" value="[]">
      <input type="hidden" name="route_sys_ids" value="[]">

      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Name</label>
        <input type="text" name="name" required placeholder="e.g. Makkah Historical Ziyarah" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Description</label>
        <textarea name="description" rows="2" placeholder="One or two lines shown to customers" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none resize-none"></textarea>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Possible Duration</label>
          <input type="text" name="possible_duration" placeholder="e.g. Half Day (4-5 hours)" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
        </div>
        <div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Price (SAR)</label>
          <input type="number" step="0.01" name="price" value="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
        </div>
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Sort Order</label>
        <input type="number" name="sort_order" value="0" class="w-32 bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>

      <label class="flex items-center gap-2 text-sm cursor-pointer">
        <input type="checkbox" name="moyallem_enabled" value="1" class="w-4 h-4 accent-secondary">
        Moyallem option available for this Ziarah
      </label>

      <div class="border-t border-white/10 pt-4">
        <p class="text-xs text-white/40 uppercase tracking-wider mb-3">Linked Transport Routes</p>
        <div class="grid grid-cols-1 gap-1.5 max-h-40 overflow-auto bg-dark border border-white/10 rounded-xl p-3">
          <?php foreach ($routes as $r): ?>
          <label class="flex items-center gap-2 text-sm cursor-pointer py-1">
            <input type="checkbox" class="route-checkbox w-4 h-4 accent-secondary" value="<?= htmlspecialchars($r['sys_id']) ?>" onchange="syncRouteSysIds()">
            <?= htmlspecialchars($r['label']) ?>
          </label>
          <?php endforeach; ?>
          <?php if (!$routes): ?><p class="text-xs text-white/20">No routes available yet.</p><?php endif; ?>
        </div>
      </div>

      <div class="border-t border-white/10 pt-4">
        <p class="text-xs text-white/40 uppercase tracking-wider mb-3">Itinerary</p>
        <div id="itinerary-items" class="space-y-3 mb-2"></div>
        <button type="button" onclick="addItineraryItem()" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-2 rounded-lg flex items-center gap-1.5"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Itinerary Item</button>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="flex-1 bg-white/5 hover:bg-white/10 font-bold py-3 rounded-xl text-sm">Cancel</button>
        <button type="submit" class="flex-1 bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm" id="ziarah-btn"><i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save</button>
      </div>
    </form>
  </div>
</div>
<script>
lucide.createIcons();
const csrf = '<?= htmlspecialchars($csrf) ?>';

// ── Route multi-select (sys_id-based) ──
function syncRouteSysIds() {
  const ids = [...document.querySelectorAll('.route-checkbox:checked')].map(cb => cb.value);
  document.querySelector('[name="route_sys_ids"]').value = JSON.stringify(ids);
}

// ── Itinerary items (multiple add, each with a basic rich-text editor) ──
// No external rich-text library is loaded on this admin panel, so this uses
// a minimal native contenteditable + document.execCommand toolbar — enough
// for bold/italic/lists/links without pulling in a new dependency.
function getItineraryItems() {
  return [...document.querySelectorAll('#itinerary-items > div')].map(row => ({
    title: row.querySelector('.item-title').value.trim(),
    description: row.querySelector('.rte-editor').innerHTML.trim()
  })).filter(i => i.title);
}
function syncItinerary() {
  document.querySelector('[name="itinerary"]').value = JSON.stringify(getItineraryItems());
}
function rteCmd(btn, cmd, val) {
  const editor = btn.closest('.rte-wrap').querySelector('.rte-editor');
  editor.focus();
  document.execCommand(cmd, false, val || null);
  syncItinerary();
}
function addItineraryItem(title = '', html = '') {
  const div = document.createElement('div');
  div.className = 'bg-white/5 border border-white/10 rounded-xl p-3';
  div.innerHTML = `
    <div class="flex items-center gap-2 mb-2">
      <input type="text" class="item-title flex-1 bg-dark border border-white/10 rounded-lg px-3 py-2 text-sm focus:border-secondary focus:outline-none" placeholder="Stop title, e.g. Jabal al-Noor" value="${title.replace(/"/g,'&quot;')}" oninput="syncItinerary()">
      <button type="button" onclick="this.closest('#itinerary-items > div').remove(); syncItinerary();" class="px-3 py-2 bg-white/5 hover:bg-red-500/20 hover:text-red-400 rounded-lg shrink-0"><i data-lucide="x" class="w-4 h-4"></i></button>
    </div>
    <div class="rte-wrap">
      <div class="rte-toolbar flex gap-1 mb-1.5">
        <button type="button" onclick="rteCmd(this,'bold')" class="w-7 h-7 rounded-md text-xs font-bold flex items-center justify-center">B</button>
        <button type="button" onclick="rteCmd(this,'italic')" class="w-7 h-7 rounded-md text-xs italic flex items-center justify-center">I</button>
        <button type="button" onclick="rteCmd(this,'insertUnorderedList')" class="w-7 h-7 rounded-md flex items-center justify-center"><i data-lucide="list" class="w-3.5 h-3.5"></i></button>
        <button type="button" onclick="const u=prompt('Link URL:'); if(u) rteCmd(this,'createLink',u)" class="w-7 h-7 rounded-md flex items-center justify-center"><i data-lucide="link" class="w-3.5 h-3.5"></i></button>
      </div>
      <div class="rte-editor bg-dark border border-white/10 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-secondary" contenteditable="true" data-placeholder="Short description for this stop..." oninput="syncItinerary()">${html}</div>
    </div>`;
  document.getElementById('itinerary-items').appendChild(div);
  lucide.createIcons();
  syncItinerary();
}

function openModal(z = null) {
  const form = document.getElementById('ziarah-form');
  form.reset();
  document.getElementById('itinerary-items').innerHTML = '';
  document.querySelectorAll('.route-checkbox').forEach(cb => cb.checked = false);
  document.getElementById('modal-title').textContent = z ? 'Edit Ziarah' : 'New Ziarah';
  // form.action and form.id are RESERVED HTMLFormElement properties (the
  // submit URL and the element's own id attribute) — they always shadow
  // same-named child inputs, so setting .value on them silently does
  // nothing. form.elements[...] correctly reaches the actual named
  // <input> regardless of any reserved-property collision.
  form.elements['action'].value = z ? 'update' : 'create';
  form.elements['sys_id'].value = z ? (z.sys_id || '') : '';
  if (z) {
    form.elements['name'].value = z.name || '';
    form.elements['description'].value = z.description || '';
    form.elements['possible_duration'].value = z.possible_duration || '';
    form.elements['price'].value = z.price || 0;
    form.elements['sort_order'].value = z.sort_order || 0;
    form.elements['moyallem_enabled'].checked = !!(parseInt(z.moyallem_enabled));
    (z.route_sys_ids_arr || []).forEach(rsid => {
      const cb = document.querySelector(`.route-checkbox[value="${rsid}"]`);
      if (cb) cb.checked = true;
    });
    (z.itinerary_arr || []).forEach(item => addItineraryItem(item.title, item.description));
  } else {
    addItineraryItem();
  }
  syncRouteSysIds();
  syncItinerary();
  document.getElementById('modal-overlay').classList.add('open');
}
function editZiarah(z) { openModal(z); }
function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); }

document.getElementById('ziarah-form').addEventListener('submit', async e => {
  e.preventDefault();
  syncRouteSysIds();
  syncItinerary();
  const btn = document.getElementById('ziarah-btn');
  btn.disabled = true; btn.textContent = 'Saving...';
  const res = await fetch(`<?= BASE_URL ?>/api/ziarah.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();
  showToast(data.message, data.success);
  btn.disabled = false; btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i>Save'; lucide.createIcons();
  if (data.success) { closeModal(); setTimeout(()=>location.reload(), 700); }
});

async function toggleZiarah(sysId) {
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','toggle'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/ziarah.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}
async function deleteZiarah(sysId, name) {
  if (!confirm(`Delete "${name}"?`)) return;
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','delete'); fd.append('sys_id',sysId);
  const res = await fetch(`<?= BASE_URL ?>/api/ziarah.php`,{method:'POST',body:fd});
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