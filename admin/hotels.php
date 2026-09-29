<?php
// FILE PATH: /admin/hotels.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
$csrf = csrfToken();

try {
    $db = getDB();
    $globalBoardTypes = $db->query("SELECT * FROM room_board_types WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll(PDO::FETCH_ASSOC);
    $hotels = $db->query("SELECT * FROM hotels ORDER BY sort_order, name")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($hotels as &$h) {
        $rtStmt = $db->prepare("SELECT * FROM room_types WHERE hotel_sys_id = ? ORDER BY sort_order, name");
        $rtStmt->execute([$h['sys_id']]);
        $h['room_types'] = $rtStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($h['room_types'] as &$rt) {
            $rt['person_capacity'] = json_decode($rt['person_capacity'] ?? 'null', true);
            foreach (['image_urls','images','videos','youtube_urls'] as $f) {
                $rt[$f] = json_decode($rt[$f] ?? 'null', true) ?: [];
            }
            $pStmt = $db->prepare("SELECT board_prices FROM room_prices WHERE room_type_sys_id = ?");
            $pStmt->execute([$rt['sys_id']]);
            $rt['board_prices'] = json_decode($pStmt->fetchColumn() ?: '[]', true) ?: [];
        }
        unset($rt);
    }
    unset($h);
} catch (Exception $e) { $hotels = []; $globalBoardTypes = []; }

// Extract a YouTube video ID from any common URL shape.
function youtubeEmbedId(string $url): ?string {
    if (preg_match('#(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})#', $url, $m)) {
        return $m[1];
    }
    return null;
}

// One combined, ordered list for a room type's media (image_urls, then
// images, then videos, then youtube_urls) — this is what the slider
// arrows step through, same order/shape as admin/_media-block.php uses.
function roomCombinedMedia(array $rt): array {
    $combined = [];
    foreach ($rt['image_urls'] ?: [] as $u) $combined[] = ['type' => 'image', 'src' => $u];
    foreach ($rt['images'] ?: [] as $p)     $combined[] = ['type' => 'image', 'src' => BASE_URL . '/' . $p];
    foreach ($rt['videos'] ?: [] as $p)     $combined[] = ['type' => 'video', 'src' => BASE_URL . '/' . $p];
    foreach (array_filter(array_map('youtubeEmbedId', $rt['youtube_urls'] ?: [])) as $vid) $combined[] = ['type' => 'youtube', 'id' => $vid];
    return $combined;
}

// Priority preview for a room type's card: images (urls+uploaded) first,
// then uploaded videos, then YouTube — first available type wins, up to 3
// thumbnails — each carrying its index into the combined list above so
// clicking it opens the slider at the right position.
function roomMediaPreview(array $combined): array {
    $imageEntries = $videoEntries = $youtubeEntries = [];
    foreach ($combined as $idx => $c) {
        if ($c['type'] === 'image') $imageEntries[] = ['idx' => $idx] + $c;
        elseif ($c['type'] === 'video') $videoEntries[] = ['idx' => $idx] + $c;
        elseif ($c['type'] === 'youtube') $youtubeEntries[] = ['idx' => $idx] + $c;
    }
    if ($imageEntries) return ['type' => 'images', 'items' => array_slice($imageEntries, 0, 3)];
    if ($videoEntries) return ['type' => 'videos', 'items' => array_slice($videoEntries, 0, 3)];
    if ($youtubeEntries) return ['type' => 'youtube', 'items' => array_slice($youtubeEntries, 0, 3)];
    return ['type' => null, 'items' => []];
}
function roomMediaCounts(array $rt): array {
    return [
        'images'  => count($rt['image_urls'] ?: []) + count($rt['images'] ?: []),
        'videos'  => count($rt['videos'] ?: []),
        'youtube' => count(array_filter(array_map('youtubeEmbedId', $rt['youtube_urls'] ?: []))),
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Hotels | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Poppins','sans-serif']},colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625',emerald:'#3AAB71',teal:'#02CCFE'}}}}</script>
<style>
body{background:#111625}
.modal-overlay{display:none} .modal-overlay.open{display:flex}
.accordion-body{display:none} .accordion-body.open{display:block}
.accordion-chevron{transition:transform .2s} .accordion.open .accordion-chevron{transform:rotate(180deg)}
</style>
</head>
<body class="text-white font-sans min-h-screen">
<div class="flex min-h-screen">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="flex-1 p-4 lg:p-8 overflow-auto">
  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div><h1 class="text-2xl font-bold">Hotels</h1><p class="text-white/40 text-sm">Click a hotel to expand its room types.</p></div>
    <a href="<?= BASE_URL ?>/admin/hotel-create.php" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New Hotel</a>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <div class="space-y-3">
    <?php foreach ($hotels as $h): ?>
    <div class="accordion bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
      <div onclick="toggleAccordion(this)" class="w-full flex items-center justify-between gap-3 p-5 text-left hover:bg-white/5 transition-colors cursor-pointer">
        <div class="flex items-center gap-3 min-w-0">
          <i data-lucide="chevron-down" class="accordion-chevron w-4 h-4 text-white/40 shrink-0"></i>
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-semibold text-sm"><?= htmlspecialchars($h['name']) ?></span>
              <span class="text-[10px] bg-white/10 px-2 py-0.5 rounded-full"><?= htmlspecialchars($h['city']) ?></span>
              <?php if(!$h['is_active']): ?><span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full">Inactive</span><?php endif; ?>
            </div>
            <p class="text-[11px] text-white/30 font-mono"><?= htmlspecialchars($h['sys_id']) ?> · <?= count($h['room_types']) ?> room type<?= count($h['room_types'])===1?'':'s' ?></p>
          </div>
        </div>
        <div class="flex items-center gap-2 shrink-0" onclick="event.stopPropagation()">
          <a href="<?= BASE_URL ?>/admin/hotel-view.php?hotel=<?= urlencode($h['uuid']) ?>" class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="eye" class="w-3.5 h-3.5 inline"></i></a>
          <a href="<?= BASE_URL ?>/admin/hotel-create.php?hotel=<?= urlencode($h['uuid']) ?>" class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="edit" class="w-3.5 h-3.5 inline"></i></a>
          <button onclick="toggleHotel('<?= htmlspecialchars($h['sys_id'],ENT_QUOTES) ?>')" class="px-3 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="<?= $h['is_active']?'eye-off':'eye' ?>" class="w-3.5 h-3.5 inline"></i></button>
          <button onclick="deleteHotel('<?= htmlspecialchars($h['sys_id'],ENT_QUOTES) ?>', '<?= htmlspecialchars($h['name'],ENT_QUOTES) ?>')" class="px-3 py-1.5 text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg"><i data-lucide="trash-2" class="w-3.5 h-3.5 inline"></i></button>
        </div>
      </div>
      <div class="accordion-body px-5 pb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($h['room_types'] as $rt):
            $mediaCounts = roomMediaCounts($rt);
            $combinedMedia = roomCombinedMedia($rt);
            $mediaPreview = roomMediaPreview($combinedMedia);
            $mediaLabels = [];
            if ($mediaCounts['images'])  $mediaLabels[] = $mediaCounts['images'] . ' image' . ($mediaCounts['images']===1?'':'s');
            if ($mediaCounts['videos'])  $mediaLabels[] = $mediaCounts['videos'] . ' video' . ($mediaCounts['videos']===1?'':'s');
            if ($mediaCounts['youtube']) $mediaLabels[] = $mediaCounts['youtube'] . ' youtube video' . ($mediaCounts['youtube']===1?'':'s');
            $roomJsVar = 'roomMedia_' . preg_replace('/[^A-Za-z0-9_]/', '_', $rt['sys_id']);
          ?>
          <?php if ($combinedMedia): ?><script>var <?= $roomJsVar ?> = <?= json_encode($combinedMedia, JSON_HEX_APOS|JSON_HEX_QUOT) ?>;</script><?php endif; ?>
          <div class="bg-dark border border-white/10 rounded-2xl p-4 flex flex-col" data-sys-id="<?= htmlspecialchars($rt['sys_id']) ?>">
            <div>
              <span class="font-semibold text-sm block"><?= htmlspecialchars($rt['name']) ?><?php if(!$rt['is_active']): ?> <span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full align-middle">Inactive</span><?php endif; ?></span>
              <p class="text-[11px] text-white/30 mt-0.5"><?= $mediaLabels ? htmlspecialchars(implode(' | ', $mediaLabels)) : 'No media yet' ?></p>
              <p class="text-xs text-white/40 mt-2"><?= (int)($rt['person_capacity']['adults']??0) ?> adults · <?= (int)($rt['person_capacity']['children']??0) ?> children</p>
              <div class="flex flex-wrap gap-1 mt-2">
                <?php foreach ($rt['board_prices'] as $bp): ?>
                <span class="text-[9px] bg-white/5 border border-white/10 px-2 py-0.5 rounded-full text-white/50"><?= htmlspecialchars($bp['board_type_title']) ?> — SR <?= number_format((float)$bp['price'],0) ?></span>
                <?php endforeach; ?>
                <?php if (!$rt['board_prices']): ?><span class="text-[10px] text-white/20">No prices set yet</span><?php endif; ?>
              </div>

              <?php if ($mediaPreview['items']): ?>
              <div class="mt-3">
                <div class="grid grid-cols-3 gap-1.5">
                  <?php foreach ($mediaPreview['items'] as $m):
                    if ($m['type'] === 'image'): ?>
                    <img src="<?= htmlspecialchars($m['src']) ?>" onclick="openSlider(<?= $roomJsVar ?>, <?= $m['idx'] ?>)" class="thumb w-full aspect-square rounded-lg object-cover bg-white/5 border border-white/10" loading="lazy" onerror="this.style.opacity=0.2" referrerpolicy="no-referrer">
                    <?php elseif ($m['type'] === 'video'): ?>
                    <div onclick="openSlider(<?= $roomJsVar ?>, <?= $m['idx'] ?>)" class="thumb w-full aspect-square rounded-lg bg-white/5 border border-white/10 relative overflow-hidden">
                      <video src="<?= htmlspecialchars($m['src']) ?>#t=0.5" preload="metadata" muted class="w-full h-full object-cover pointer-events-none"></video>
                      <div class="absolute inset-0 flex items-center justify-center bg-black/20"><i data-lucide="play-circle" class="w-4 h-4 text-white"></i></div>
                    </div>
                    <?php else: ?>
                    <img src="https://img.youtube.com/vi/<?= htmlspecialchars($m['id']) ?>/hqdefault.jpg" onclick="openSlider(<?= $roomJsVar ?>, <?= $m['idx'] ?>)" class="thumb w-full aspect-square rounded-lg object-cover bg-white/5 border border-white/10" loading="lazy">
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
                <button type="button" onclick='openHotelsSeeMore(<?= $roomJsVar ?>)' class="text-[11px] bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg mt-2">See More</button>
              </div>
              <?php endif; ?>
            </div>
            <div class="flex gap-2 mt-3 pt-3 border-t border-white/5">
              <button onclick='openPricesModal(<?= json_encode(["sys_id"=>$rt["sys_id"],"name"=>$rt["name"],"board_prices"=>$rt["board_prices"]], JSON_HEX_APOS|JSON_HEX_QUOT) ?>)' class="flex-1 text-[11px] bg-white/5 hover:bg-white/10 px-2 py-2 rounded-lg font-medium">+ Prices</button>
              <button onclick="toggleRoomType('<?= htmlspecialchars($rt['sys_id'],ENT_QUOTES) ?>')" class="px-3 py-2 text-[11px] bg-white/5 hover:bg-white/10 rounded-lg" title="<?= $rt['is_active']?'Deactivate':'Activate' ?>"><i data-lucide="<?= $rt['is_active']?'eye-off':'eye' ?>" class="w-3.5 h-3.5 inline"></i></button>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (!$h['room_types']): ?>
          <p class="text-sm text-white/30 col-span-full">No room types yet — <a href="<?= BASE_URL ?>/admin/hotel-create.php?hotel=<?= urlencode($h['uuid']) ?>" class="text-secondary underline">add one</a>.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$hotels): ?>
    <div class="text-center py-16 text-white/30 bg-white/5 border border-white/10 rounded-2xl"><i data-lucide="building-2" class="w-10 h-10 mx-auto mb-2 opacity-30"></i><p class="text-sm">No hotels yet.</p></div>
    <?php endif; ?>
  </div>
</main>
</div>

<!-- ══════════ PRICES MODAL ══════════ -->
<div id="prices-modal-overlay" class="modal-overlay fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-3xl my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg">Prices — <span id="prices-modal-room-name"></span></h2>
      <button onclick="closePricesModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <div class="p-6">
      <table class="w-full text-sm border border-white/10 rounded-xl overflow-hidden">
        <thead>
          <tr class="bg-white/10 text-white/50 text-[11px] uppercase tracking-wider">
            <th class="px-3 py-2 w-10"><input type="checkbox" id="prices-select-all" onchange="toggleSelectAllRows(this.checked)" class="w-4 h-4 accent-secondary"></th>
            <th class="text-left px-3 py-2">Boarding Type</th>
            <th class="text-left px-3 py-2">Validation — From</th>
            <th class="text-left px-3 py-2">To</th>
            <th class="text-left px-3 py-2 w-28">Price (SAR)</th>
            <th class="w-10"></th>
          </tr>
        </thead>
        <tbody id="prices-rows"></tbody>
      </table>
      <div class="flex items-center justify-between mt-4">
        <button type="button" onclick="addPriceRow()" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-2 rounded-lg flex items-center gap-1"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Add More</button>
        <button type="button" onclick="savePricesTable()" id="prices-save-btn" class="bg-secondary hover:bg-emerald text-primary font-bold px-5 py-2.5 rounded-xl text-sm">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
lucide.createIcons();
const csrf = '<?= htmlspecialchars($csrf) ?>';
const GLOBAL_BOARD_TYPES = <?= json_encode($globalBoardTypes) ?>;

function toggleAccordion(btn) {
  const acc = btn.closest('.accordion');
  const body = acc.querySelector('.accordion-body');
  const willOpen = !body.classList.contains('open');

  // Close every other accordion first, so only one is ever open at a time.
  document.querySelectorAll('.accordion.open').forEach(other => {
    if (other !== acc) {
      other.classList.remove('open');
      other.querySelector('.accordion-body').classList.remove('open');
    }
  });

  body.classList.toggle('open', willOpen);
  acc.classList.toggle('open', willOpen);
}

async function toggleHotel(sysId) {
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','toggle_hotel'); fd.append('sys_id',sysId);
  const res = await fetch(`${window.BASE_URL}/api/hotels.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}
async function toggleRoomType(sysId) {
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','toggle_room_type'); fd.append('sys_id',sysId);
  const res = await fetch(`${window.BASE_URL}/api/hotels.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}
async function deleteHotel(sysId, name) {
  if (!confirm(`Delete "${name}" and ALL its room types, categories, and prices? This can't be undone.`)) return;
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','delete_hotel'); fd.append('sys_id',sysId);
  const res = await fetch(`${window.BASE_URL}/api/hotels.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}

// ══════════ Service Category modal ══════════
// ══════════ Prices modal — every global board type is offered as a row;
// checking a row makes its validity+price editable, unchecking clears and
// locks it again. "+Add More" appends a custom, one-off row (its own name
// field) with a For-This-Room / For-All-Rooms choice. Save submits every
// checked row together in one go. ══════════
let currentPricesRoomSysId = '';
let priceRowSeq = 0;

function openPricesModal(rt) {
  currentPricesRoomSysId = rt.sys_id;
  document.getElementById('prices-modal-room-name').textContent = rt.name;
  const tbody = document.getElementById('prices-rows');
  const existing = rt.board_prices || [];

  // Global catalog rows — pre-checked + pre-filled when this room already
  // has a saved price for that board type.
  const catalogRows = GLOBAL_BOARD_TYPES.map(bt => {
    const match = existing.find(bp => bp.board_type_sys_id === bt.sys_id);
    return buildCatalogRow(bt.sys_id, bt.name, match);
  }).join('');

  // Any already-saved "For This Room" one-off entries (board_type_sys_id
  // is null) show up as pre-filled custom rows too, so re-opening the
  // modal doesn't lose them.
  const customRows = existing.filter(bp => bp.board_type_sys_id === null)
    .map(bp => buildCustomRow(bp)).join('');

  tbody.innerHTML = catalogRows + customRows;
  updateSelectAllState();
  lucide.createIcons();
  document.getElementById('prices-modal-overlay').classList.add('open');
}
function closePricesModal() { document.getElementById('prices-modal-overlay').classList.remove('open'); }

function buildCatalogRow(boardSysId, title, existing) {
  const checked = !!existing;
  return `
    <tr class="border-t border-white/5" data-board-sys-id="${boardSysId}" data-custom="0">
      <td class="px-3 py-2"><input type="checkbox" class="row-check w-4 h-4 accent-secondary" onchange="onRowCheckToggle(this)" ${checked?'checked':''}></td>
      <td class="px-3 py-2 font-medium">${title}</td>
      <td class="px-3 py-2"><input type="date" class="row-from bg-dark border border-white/10 rounded-lg px-3 py-2 text-xs focus:border-secondary focus:outline-none" ${checked?'':'readonly'} value="${existing?existing.valid_from:''}"></td>
      <td class="px-3 py-2"><input type="date" class="row-to bg-dark border border-white/10 rounded-lg px-3 py-2 text-xs focus:border-secondary focus:outline-none" ${checked?'':'readonly'} value="${existing?existing.valid_to:''}"></td>
      <td class="px-3 py-2"><input type="number" step="0.01" class="row-price bg-dark border border-white/10 rounded-lg px-3 py-2 text-xs w-24 focus:border-secondary focus:outline-none" ${checked?'':'readonly'} value="${existing?existing.price:''}"></td>
      <td class="px-3 py-2"></td>
    </tr>`;
}
function buildCustomRow(existing) {
  const id = ++priceRowSeq;
  const isAll = false; // existing saved rows are always "For This Room" (board_type_sys_id null means that)
  return `
    <tr class="border-t border-white/5" data-custom="1" data-row-id="${id}">
      <td class="px-3 py-2"><input type="checkbox" class="row-check w-4 h-4 accent-secondary" onchange="onRowCheckToggle(this)" checked></td>
      <td class="px-3 py-2"><input type="text" class="row-title bg-dark border border-white/10 rounded-lg px-3 py-2 text-xs w-full focus:border-secondary focus:outline-none" placeholder="Boarding type name" value="${(existing&&existing.board_type_title||'').replace(/"/g,'&quot;')}"></td>
      <td class="px-3 py-2"><input type="date" class="row-from bg-dark border border-white/10 rounded-lg px-3 py-2 text-xs focus:border-secondary focus:outline-none" value="${existing?existing.valid_from:''}"></td>
      <td class="px-3 py-2"><input type="date" class="row-to bg-dark border border-white/10 rounded-lg px-3 py-2 text-xs focus:border-secondary focus:outline-none" value="${existing?existing.valid_to:''}"></td>
      <td class="px-3 py-2"><input type="number" step="0.01" class="row-price bg-dark border border-white/10 rounded-lg px-3 py-2 text-xs w-24 focus:border-secondary focus:outline-none" value="${existing?existing.price:''}"></td>
      <td class="px-3 py-2">
        <div class="flex flex-col gap-1 text-[10px] text-white/50">
          <label class="flex items-center gap-1"><input type="radio" name="scope-${id}" value="this" class="row-scope w-3 h-3 accent-secondary" checked> This Room</label>
          <label class="flex items-center gap-1"><input type="radio" name="scope-${id}" value="all" class="row-scope w-3 h-3 accent-secondary"> All Rooms</label>
        </div>
      </td>
    </tr>`;
}

function addPriceRow() {
  document.getElementById('prices-rows').insertAdjacentHTML('beforeend', buildCustomRow(null));
  updateSelectAllState();
  lucide.createIcons();
}

// Catalog row unchecked → clears + locks its inputs (data isn't gone,
// just not being edited right now). Custom/appended row unchecked → the
// whole row is removed outright, since it never existed until this form.
function onRowCheckToggle(checkbox) {
  const row = checkbox.closest('tr');
  const isCustom = row.dataset.custom === '1';
  if (!checkbox.checked && isCustom) {
    row.remove();
  } else {
    const from = row.querySelector('.row-from'), to = row.querySelector('.row-to'), price = row.querySelector('.row-price');
    [from, to, price].forEach(inp => { inp.readOnly = !checkbox.checked; if (!checkbox.checked) inp.value = ''; });
  }
  updateSelectAllState();
}
function toggleSelectAllRows(checked) {
  document.querySelectorAll('#prices-rows .row-check').forEach(cb => {
    if (cb.checked !== checked) { cb.checked = checked; onRowCheckToggle(cb); }
  });
}
function updateSelectAllState() {
  const boxes = [...document.querySelectorAll('#prices-rows .row-check')];
  document.getElementById('prices-select-all').checked = boxes.length > 0 && boxes.every(b => b.checked);
}

async function savePricesTable() {
  const btn = document.getElementById('prices-save-btn');
  btn.disabled = true; btn.textContent = 'Saving...';

  const rows = [...document.querySelectorAll('#prices-rows > tr')].filter(r => r.querySelector('.row-check').checked);
  const boardPrices = [];
  let hasError = false;

  for (const row of rows) {
    const from = row.querySelector('.row-from').value;
    const to = row.querySelector('.row-to').value;
    const price = row.querySelector('.row-price').value;
    const isCustom = row.dataset.custom === '1';
    const title = isCustom ? row.querySelector('.row-title').value.trim() : row.children[1].textContent.trim();

    if (!title || !from || !to || price === '') { hasError = true; continue; } // required once checked

    let boardTypeSysId = isCustom ? null : row.dataset.boardSysId;

    if (isCustom) {
      const scope = row.querySelector('.row-scope:checked').value;
      if (scope === 'all') {
        // Reuse an existing global board type of the same name — never
        // create a duplicate catalog entry for the same title.
        const existingMatch = GLOBAL_BOARD_TYPES.find(bt => bt.name.toLowerCase() === title.toLowerCase());
        if (existingMatch) {
          boardTypeSysId = existingMatch.sys_id;
        } else {
          const fd = new FormData();
          fd.append('csrf_token', csrf); fd.append('action', 'create'); fd.append('name', title);
          const res = await fetch(`${window.BASE_URL}/api/room-board-types.php`, {method:'POST', body:fd});
          const data = await res.json();
          if (data.success) {
            boardTypeSysId = data.sys_id;
            GLOBAL_BOARD_TYPES.push({sys_id: data.sys_id, name: title});
          } else {
            hasError = true; continue;
          }
        }
      }
    }

    boardPrices.push({ board_type_sys_id: boardTypeSysId, board_type_title: title, valid_from: from, valid_to: to, price: parseFloat(price) || 0 });
  }

  if (hasError) showToast('Some checked rows were missing required fields and were skipped.', false);

  const fd = new FormData();
  fd.append('csrf_token', csrf);
  fd.append('action', 'save');
  fd.append('room_type_sys_id', currentPricesRoomSysId);
  fd.append('board_prices', JSON.stringify(boardPrices));
  const res = await fetch(`${window.BASE_URL}/api/room-prices.php`, {method:'POST', body:fd});
  const data = await res.json();

  btn.disabled = false; btn.textContent = 'Save';
  showToast(data.message, data.success);
  if (data.success) { closePricesModal(); setTimeout(() => location.reload(), 700); }
}

function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm ${ok?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  t.classList.remove('hidden'); setTimeout(()=>t.classList.add('hidden'),3000);
}

// ══════════ Media lightboxes (image / uploaded video / YouTube) ══════════
// ══════════ Combined media slider — one modal, arrows step through
// every file (images, uploaded videos, YouTube) in one sequence. ══════════
let sliderItems = [];
let sliderIndex = 0;

function openSlider(items, startIndex) {
  sliderItems = items;
  sliderIndex = startIndex;
  renderSliderItem();
  document.getElementById('slider-modal').classList.add('open');
}
function closeSlider() {
  const video = document.getElementById('slider-video');
  video.pause(); video.src = '';
  document.getElementById('slider-youtube-frame').src = '';
  document.getElementById('slider-modal').classList.remove('open');
}
function sliderPrev() { sliderIndex = (sliderIndex - 1 + sliderItems.length) % sliderItems.length; renderSliderItem(); }
function sliderNext() { sliderIndex = (sliderIndex + 1) % sliderItems.length; renderSliderItem(); }
function renderSliderItem() {
  const item = sliderItems[sliderIndex];
  const img = document.getElementById('slider-img');
  const video = document.getElementById('slider-video');
  const ytWrap = document.getElementById('slider-youtube-wrap');
  img.style.display = 'none';
  video.pause(); video.style.display = 'none'; video.src = '';
  ytWrap.style.display = 'none';
  document.getElementById('slider-youtube-frame').src = '';

  if (item.type === 'image') {
    img.src = item.src; img.style.display = 'block';
  } else if (item.type === 'video') {
    video.src = item.src; video.style.display = 'block'; video.play();
  } else if (item.type === 'youtube') {
    ytWrap.style.display = 'block';
    document.getElementById('slider-youtube-frame').src = `https://www.youtube.com/embed/${item.id}?autoplay=1`;
  }
  document.getElementById('slider-counter').textContent = sliderItems.length > 1 ? `${sliderIndex + 1} / ${sliderItems.length}` : '';
}

// ══════════ "See More" — full grid for one room type's combined media,
// grouped by type for display; every thumbnail opens the same slider. ══════════
function openHotelsSeeMore(items) {
  const images = items.map((it, i) => ({...it, i})).filter(it => it.type === 'image');
  const videos = items.map((it, i) => ({...it, i})).filter(it => it.type === 'video');
  const youtube = items.map((it, i) => ({...it, i})).filter(it => it.type === 'youtube');

  let html = '';
  if (images.length) {
    html += `<div><p class="text-xs font-semibold text-white/60 mb-2">Images</p><div class="grid grid-cols-3 sm:grid-cols-4 gap-2">${
      images.map(it => `<img src="${it.src}" onclick="openSlider(currentSeeMoreItems, ${it.i})" class="thumb w-full aspect-square rounded-lg object-cover bg-dark border border-white/10" referrerpolicy="no-referrer">`).join('')
    }</div></div>`;
  }
  if (videos.length) {
    html += `<div class="mt-4"><p class="text-xs font-semibold text-white/60 mb-2">Uploaded Videos</p><div class="grid grid-cols-3 sm:grid-cols-4 gap-2">${
      videos.map(it => `<div onclick="openSlider(currentSeeMoreItems, ${it.i})" class="thumb w-full aspect-square rounded-lg bg-dark border border-white/10 relative overflow-hidden"><video src="${it.src}#t=0.5" preload="metadata" muted class="w-full h-full object-cover pointer-events-none"></video><div class="absolute inset-0 flex items-center justify-center bg-black/20"><i data-lucide="play-circle" class="w-6 h-6 text-white drop-shadow"></i></div></div>`).join('')
    }</div></div>`;
  }
  if (youtube.length) {
    html += `<div class="mt-4"><p class="text-xs font-semibold text-white/60 mb-2">From YouTube</p><div class="grid grid-cols-3 sm:grid-cols-4 gap-2">${
      youtube.map(it => `<img src="https://img.youtube.com/vi/${it.id}/hqdefault.jpg" onclick="openSlider(currentSeeMoreItems, ${it.i})" class="thumb w-full aspect-square rounded-lg object-cover bg-dark border border-white/10">`).join('')
    }</div></div>`;
  }
  document.getElementById('see-more-grid').innerHTML = html || '<p class="text-sm text-white/30">No media yet.</p>';
  window.currentSeeMoreItems = items;
  lucide.createIcons();
  document.getElementById('see-more-modal').classList.add('open');
}
function closeSeeMore() { document.getElementById('see-more-modal').classList.remove('open'); }
</script>

<!-- Combined media slider — one modal, arrows step through every file in
     the room type's media (images, uploaded videos, YouTube) in one
     sequence, regardless of type. -->
<div id="slider-modal" class="modal-overlay fixed inset-0 z-[70] bg-black/90 items-center justify-center p-6" onclick="if(event.target===this) closeSlider()">
  <button onclick="closeSlider()" class="absolute top-4 right-4 text-white/60 hover:text-white z-10"><i data-lucide="x" class="w-6 h-6"></i></button>
  <button onclick="sliderPrev()" class="absolute left-4 text-white/60 hover:text-white z-10"><i data-lucide="chevron-left" class="w-9 h-9"></i></button>
  <button onclick="sliderNext()" class="absolute right-4 text-white/60 hover:text-white z-10"><i data-lucide="chevron-right" class="w-9 h-9"></i></button>
  <div class="max-w-full max-h-full flex items-center justify-center" onclick="event.stopPropagation()">
    <img id="slider-img" src="" class="max-w-full max-h-[85vh] rounded-xl" style="display:none" referrerpolicy="no-referrer">
    <video id="slider-video" src="" controls class="max-w-full max-h-[85vh] rounded-xl" style="display:none"></video>
    <div id="slider-youtube-wrap" class="w-full max-w-3xl aspect-video" style="display:none">
      <iframe id="slider-youtube-frame" src="" class="w-full h-full rounded-xl" frameborder="0" allowfullscreen allow="autoplay"></iframe>
    </div>
  </div>
  <div id="slider-counter" class="absolute bottom-5 left-1/2 -translate-x-1/2 text-white/50 text-sm"></div>
</div>

<!-- "See More" modal — full grid, grouped by media type; every item opens the same slider -->
<div id="see-more-modal" class="modal-overlay fixed inset-0 z-[60] bg-black/80 items-start justify-center p-4 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-3xl my-6">
    <div class="flex items-center justify-between p-5 border-b border-white/10">
      <h3 class="font-bold" id="see-more-title">Media</h3>
      <button onclick="closeSeeMore()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <div id="see-more-grid" class="p-5"></div>
  </div>
</div>
</body></html>