<?php
// FILE PATH: /admin/hotel-view.php
// Read-only hotel detail view — hotel info + all room types + all media
// (external URLs, uploaded images/videos, YouTube embeds).
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();

// Super-admin + admin only — page-level enforcement (not just a hidden
// button on admin/hotels.php).
$allowedRoles = ['super-admin', 'admin'];
if (!in_array($_SESSION['role_alias'] ?? '', $allowedRoles, true)) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

// Formats a room type's {"unit":"sqr-m","value":300} size into a readable
// label like "300 m²".
function formatRoomSize(?array $size): string {
    if (!$size || !isset($size['value'])) return '—';
    $labels = ['sqr-m' => 'm²', 'sqr-cm' => 'cm²', 'sqr-ft' => 'ft²', 'sqr-in' => 'in²'];
    $unit = $labels[$size['unit'] ?? ''] ?? ($size['unit'] ?? '');
    return $size['value'] . ' ' . $unit;
}

$hotelUuid = trim($_GET['hotel'] ?? '');
if ($hotelUuid === '') { header('Location: ' . BASE_URL . '/admin/hotels.php'); exit; }

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM hotels WHERE uuid = ?");
    $stmt->execute([$hotelUuid]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$hotel) { header('Location: ' . BASE_URL . '/admin/hotels.php'); exit; }
    foreach (['checkin_schedule','distance_info','age_ranges','image_urls','images','videos','youtube_urls'] as $f) {
        $hotel[$f] = json_decode($hotel[$f] ?? 'null', true);
    }

    $rtStmt = $db->prepare("SELECT * FROM room_types WHERE hotel_sys_id = ? ORDER BY sort_order, name");
    $rtStmt->execute([$hotel['sys_id']]);
    $roomTypes = $rtStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($roomTypes as &$rt) {
        foreach (['person_capacity','bed_config','amenities','add_on','view_info','size','image_urls','images','videos','youtube_urls'] as $f) {
            $rt[$f] = json_decode($rt[$f] ?? 'null', true);
        }
        $pStmt = $db->prepare("SELECT board_prices FROM room_prices WHERE room_type_sys_id = ?");
        $pStmt->execute([$rt['sys_id']]);
        $rt['board_prices'] = json_decode($pStmt->fetchColumn() ?: '[]', true) ?: [];
    }
    unset($rt);
} catch (Exception $e) {
    header('Location: ' . BASE_URL . '/admin/hotels.php'); exit;
}

// Extract a YouTube video ID from any common URL shape, for embedding.
function youtubeEmbedId(string $url): ?string {
    if (preg_match('#(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})#', $url, $m)) {
        return $m[1];
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($hotel['name']) ?> | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Poppins','sans-serif']},colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625',emerald:'#3AAB71',teal:'#02CCFE'}}}}</script>
<style>
body{background:#111625}
.lightbox{display:none} .lightbox.open{display:flex}
.thumb{cursor:zoom-in}
</style>
</head>
<body class="text-white font-sans min-h-screen">
<div class="flex min-h-screen">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="flex-1 p-4 lg:p-8 overflow-auto">
  <div class="flex items-center gap-3 mb-6 flex-wrap">
    <a href="<?= BASE_URL ?>/admin/hotels.php" class="text-white/40 hover:text-white"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
    <div class="flex-1 min-w-0">
      <h1 class="text-2xl font-bold"><?= htmlspecialchars($hotel['name']) ?></h1>
      <p class="text-white/40 text-sm"><?= htmlspecialchars($hotel['city']) ?> · <?= (int)$hotel['star_rating'] ?>-Star · <?= htmlspecialchars($hotel['sys_id']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/admin/hotel-create.php?hotel=<?= urlencode($hotel['uuid']) ?>" class="bg-secondary hover:bg-emerald text-primary font-bold px-4 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="edit" class="w-4 h-4"></i> Edit</a>
  </div>

  <!-- Hotel info -->
  <div class="bg-white/5 border border-white/10 rounded-2xl p-6 mb-6">
    <h2 class="font-bold mb-4">Hotel Information</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
      <div><p class="text-[11px] text-white/30 uppercase tracking-wider mb-1">Check-in</p><p><?= htmlspecialchars($hotel['checkin_schedule']['in'] ?? '—') ?></p></div>
      <div><p class="text-[11px] text-white/30 uppercase tracking-wider mb-1">Check-out</p><p><?= htmlspecialchars($hotel['checkin_schedule']['out'] ?? '—') ?></p></div>
      <div><p class="text-[11px] text-white/30 uppercase tracking-wider mb-1">Phone</p><p><?= htmlspecialchars($hotel['phone'] ?: '—') ?></p></div>
      <div><p class="text-[11px] text-white/30 uppercase tracking-wider mb-1">Email</p><p><?= htmlspecialchars($hotel['email'] ?: '—') ?></p></div>
    </div>
    <div class="mb-4">
      <p class="text-[11px] text-white/30 uppercase tracking-wider mb-1">Address</p>
      <p class="text-sm"><?= htmlspecialchars($hotel['address'] ?: '—') ?></p>
    </div>
    <div class="mb-4">
      <p class="text-[11px] text-white/30 uppercase tracking-wider mb-1">Description</p>
      <p class="text-sm text-white/70"><?= nl2br(htmlspecialchars($hotel['description'] ?: '—')) ?></p>
    </div>
    <?php $d = $hotel['distance_info']; if ($d): ?>
    <div class="mb-4 bg-dark rounded-xl p-4">
      <p class="text-[11px] text-white/30 uppercase tracking-wider mb-1">Distance</p>
      <p class="text-sm"><?= htmlspecialchars($d['value'] ?? 0) ?> <?= htmlspecialchars($d['unit'] ?? 'm') ?> from <?= htmlspecialchars($d['landmark'] ?? '—') ?><?= !empty($d['gate_no']) ? ' (Gate '.htmlspecialchars($d['gate_no']).')' : '' ?>
      <?php if (!empty($d['walking']['enabled'])): ?> · <?= (int)($d['walking']['minutes'] ?? 0) ?> min walk<?php endif; ?></p>
    </div>
    <?php endif; ?>
    <?php $a = $hotel['age_ranges']; if ($a): ?>
    <div class="bg-dark rounded-xl p-4">
      <p class="text-[11px] text-white/30 uppercase tracking-wider mb-1">Age Ranges</p>
      <p class="text-sm">Infant: <?= (int)($a['infant']['min']??0) ?>-<?= (int)($a['infant']['max']??0) ?> yrs · Child: <?= (int)($a['child']['min']??0) ?>-<?= (int)($a['child']['max']??0) ?> yrs</p>
    </div>
    <?php endif; ?>
  </div>

  <!-- Hotel media -->
  <?php include __DIR__ . '/_media-block.php'; renderMediaBlock('Hotel Media', $hotel, 'hotel'); ?>

  <?php if ($roomTypes): ?>
  <!-- Room types -->
  <h2 class="font-bold text-lg mb-4 mt-8">Room Types (<?= count($roomTypes) ?>)</h2>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <?php foreach ($roomTypes as $rt): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
      <div class="flex items-start justify-between gap-3 flex-wrap mb-3">
        <div>
          <h3 class="font-bold"><?= htmlspecialchars($rt['name']) ?></h3>
          <p class="text-[11px] text-white/30 font-mono"><?= htmlspecialchars($rt['sys_id']) ?></p>
        </div>
        <div class="text-sm text-white/60"><?= (int)($rt['person_capacity']['adults']??0) ?> adults · <?= (int)($rt['person_capacity']['children']??0) ?> children · <?= htmlspecialchars(formatRoomSize($rt['size'])) ?></div>
      </div>
      <p class="text-sm text-white/60 mb-3"><?= nl2br(htmlspecialchars($rt['description'] ?: '—')) ?></p>

      <?php if ($rt['bed_config']): ?>
      <div class="mb-3">
        <p class="text-[11px] text-white/30 uppercase tracking-wider mb-1">Bed Configuration</p>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($rt['bed_config'] as $b): ?>
          <span class="text-xs bg-dark border border-white/10 px-3 py-1 rounded-full"><?= htmlspecialchars($b['bed_type']) ?> × <?= (int)$b['quantity'] ?> (cap. <?= (int)$b['capacity_per_bed'] ?>/bed)</span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($rt['amenities']): ?>
      <div class="mb-3">
        <p class="text-[11px] text-white/30 uppercase tracking-wider mb-1">Amenities</p>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($rt['amenities'] as $am): ?><span class="text-xs bg-dark border border-white/10 px-3 py-1 rounded-full"><?= htmlspecialchars($am) ?></span><?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($rt['add_on']['bed'])): ?>
      <p class="text-xs text-secondary mb-3">Extra bed add-on: SR <?= number_format((float)($rt['add_on']['charge']??0),2) ?></p>
      <?php endif; ?>

      <?php if (!empty($rt['view_info']['enabled'])): ?>
      <p class="text-xs text-secondary mb-3 flex items-center gap-1"><i data-lucide="eye" class="w-3.5 h-3.5"></i> <?= htmlspecialchars($rt['view_info']['description'] ?: 'View available') ?></p>
      <?php endif; ?>

      <?php if ($rt['board_prices']): ?>
      <div class="mb-4">
        <p class="text-[11px] text-white/30 uppercase tracking-wider mb-2">Boarding Types & Prices</p>
        <div class="space-y-2">
          <?php foreach ($rt['board_prices'] as $bp): ?>
          <div class="bg-dark rounded-lg p-3 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium"><?= htmlspecialchars($bp['board_type_title']) ?></p>
              <p class="text-xs text-white/50"><?= htmlspecialchars($bp['valid_from']) ?> → <?= htmlspecialchars($bp['valid_to']) ?></p>
            </div>
            <span class="text-secondary font-bold text-sm">SR <?= number_format((float)$bp['price'],2) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php renderMediaBlock('Room Media', $rt, 'room_' . $rt['sys_id']); ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>
</div>

<!-- Combined media slider — one modal, arrows step through every file in
     the group (images, uploaded videos, YouTube) in one sequence, in the
     order they were added: image_urls, then images, then videos, then
     youtube_urls. Content div switches shape per item type. -->
<div id="slider-modal" class="lightbox fixed inset-0 z-[70] bg-black/90 items-center justify-center p-6" onclick="if(event.target===this) closeSlider()">
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

<!-- "See More" modal — full grid for one group, grouped by type; clicking
     any item opens the same combined slider, positioned on that item. -->
<div id="see-more-modal" class="lightbox fixed inset-0 z-[60] bg-black/80 items-start justify-center p-6 overflow-auto">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-3xl my-6">
    <div class="flex items-center justify-between p-5 border-b border-white/10">
      <h3 class="font-bold" id="see-more-title">All Media</h3>
      <button onclick="closeSeeMore()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <div id="see-more-grid" class="p-5"></div>
  </div>
</div>

<script>
lucide.createIcons();

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

// "See More" — shows every item in the group's combined list, grouped by
// type for display, but each thumbnail opens the same combined slider
// (so arrowing from a "See More" click still moves through everything).
function openSeeMore(items) {
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
  document.getElementById('see-more-grid').innerHTML = html;
  window.currentSeeMoreItems = items;
  lucide.createIcons();
  document.getElementById('see-more-modal').classList.add('open');
}
function closeSeeMore() { document.getElementById('see-more-modal').classList.remove('open'); }
</script>
</body></html>