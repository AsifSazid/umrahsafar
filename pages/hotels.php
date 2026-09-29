<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'Umrah Hotels | TravHub — Makkah & Madinah';

$whatsappNumber = getSetting('whatsapp_number', '');
$whatsappNumber = $whatsappNumber !== '' ? ltrim($whatsappNumber, '+') : '8801000000000';

include dirname(__DIR__) . '/includes/header.php';
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="pt-28 pb-24">
<!-- Hero -->
<div class="max-w-7xl mx-auto px-6 mb-12">
    <div class="text-center mb-10">
        <span class="text-secondary font-bold tracking-widest uppercase text-xs">Handpicked Accommodations</span>
        <h1 class="text-4xl lg:text-5xl font-bold mt-2">Makkah & Madinah Hotels</h1>
        <p class="text-white/40 mt-4 max-w-xl mx-auto">All hotels are personally verified for quality, location and proximity to Haram.</p>
    </div>
    <!-- Filters -->
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-white/5 border border-white/10 rounded-2xl p-5">
        <div class="flex flex-wrap gap-3 items-center">
            <span class="text-xs text-white/40 uppercase tracking-wider">City:</span>
            <?php foreach (['All','Makkah','Madinah','Jeddah'] as $c): ?>
            <button class="city-filter <?= $c==='All'?'bg-secondary text-primary':'bg-white/5 text-white/60' ?> px-4 py-1.5 rounded-lg text-xs font-semibold transition-all" data-city="<?= $c ?>"><?= $c ?></button>
            <?php endforeach; ?>
            <span class="text-xs text-white/40 uppercase tracking-wider ml-3">Stars:</span>
            <select id="star-filter" class="bg-dark border border-white/10 text-white text-xs rounded-lg px-3 py-1.5 focus:border-secondary focus:outline-none">
                <option value="All">All Stars</option>
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
            </select>
        </div>
        <div class="flex items-center gap-3">
            <i data-lucide="search" class="w-4 h-4 text-white/40"></i>
            <input id="hotel-search" type="text" placeholder="Search hotel name..." class="bg-transparent text-sm focus:outline-none placeholder-white/20 w-48">
        </div>
    </div>
</div>

<!-- Hotel Grid -->
<div class="max-w-7xl mx-auto px-6">
    <div id="hotel-loading" class="py-16 text-center">
        <i data-lucide="hotel" class="w-10 h-10 mx-auto mb-3 text-white/20 animate-pulse"></i>
        <p class="text-white/30 text-sm">Loading hotels...</p>
    </div>
    <div id="hotel-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"></div>
    <div id="hotel-empty" class="hidden py-16 text-center">
        <i data-lucide="hotel" class="w-12 h-12 text-white/20 mx-auto mb-4"></i>
        <h3 class="text-xl font-bold mb-2">No Hotels Available</h3>
        <p class="text-white/40 text-sm">Please check back soon.</p>
    </div>
    <div id="hotel-no-results" class="hidden py-16 text-center">
        <i data-lucide="search-x" class="w-12 h-12 text-white/20 mx-auto mb-4"></i>
        <h3 class="text-xl font-bold mb-2">No Hotels Found</h3>
        <p class="text-white/40 text-sm">Try adjusting your search or filters.</p>
    </div>
</div>
</main>

<!-- ══════════ HOTEL DETAILS MODAL ══════════ -->
<div id="hotel-modal-overlay" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-sm items-center justify-center p-4" style="display:none">
  <div class="bg-dark border border-white/10 rounded-2xl w-full max-w-6xl max-h-[92vh] overflow-y-auto relative">
    <button onclick="closeHotelModal()" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center"><i data-lucide="x" class="w-5 h-5"></i></button>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
      <!-- Left: media slider -->
      <div class="p-6 lg:border-r border-white/10">
        <div class="relative rounded-xl overflow-hidden bg-black aspect-[4/3] flex items-center justify-center">
          <img id="hm-slider-img" src="" class="w-full h-full object-contain hidden" referrerpolicy="no-referrer">
          <video id="hm-slider-video" src="" controls class="w-full h-full object-contain hidden"></video>
          <iframe id="hm-slider-yt" src="" class="w-full h-full hidden" frameborder="0" allowfullscreen allow="autoplay"></iframe>
          <div id="hm-slider-empty" class="text-white/20 text-sm hidden">No media available</div>
          <button onclick="hmSliderNav(-1)" class="absolute left-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/50 hover:bg-black/70 flex items-center justify-center"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
          <button onclick="hmSliderNav(1)" class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/50 hover:bg-black/70 flex items-center justify-center"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
          <div id="hm-slider-counter" class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/60 text-xs px-2.5 py-1 rounded-full"></div>
        </div>
        <div id="hm-thumbs" class="grid grid-cols-6 gap-2 mt-3"></div>
      </div>

      <!-- Right: details -->
      <div class="p-6">
        <h2 id="hm-name" class="text-2xl font-bold mb-1"></h2>
        <p id="hm-meta" class="text-white/40 text-sm mb-4"></p>
        <p id="hm-desc" class="text-white/60 text-sm mb-4"></p>
        <p id="hm-address" class="text-white/40 text-xs mb-1 flex items-center gap-1.5"></p>
        <p id="hm-distance" class="text-secondary text-xs mb-6 flex items-center gap-1.5"></p>

        <h3 class="font-bold text-lg mb-3 pt-4 border-t border-white/10">Room Types</h3>
        <div id="hm-bed-tabs" class="flex gap-2 flex-wrap mb-4"></div>
        <div id="hm-room-list" class="space-y-4 mb-6">
          <p class="text-xs text-white/30 text-center py-4">Loading room types...</p>
        </div>

        <a id="hm-whatsapp-btn" href="#" target="_blank" class="flex items-center justify-center gap-2 bg-secondary hover:bg-emerald text-primary font-bold py-3.5 rounded-xl transition-all duration-300">
          <i data-lucide="message-circle" class="w-5 h-5"></i> Inquire on WhatsApp
        </a>
      </div>
    </div>
  </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/currency-handler.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
window.BASE_URL = <?= json_encode(BASE_URL) ?>;
const WHATSAPP_NUMBER = <?= json_encode($whatsappNumber) ?>;
const HOTEL_API_BASE = `${window.BASE_URL}/api/hotels.php`;

function youtubeIdFromUrl(url) {
  const m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{6,})/);
  return m ? m[1] : null;
}
function formatRoomSize(size) {
  if (!size || !size.value) return '';
  const labels = {'sqr-m':'m²','sqr-cm':'cm²','sqr-ft':'ft²','sqr-in':'in²'};
  return `${size.value} ${labels[size.unit] || size.unit || ''}`;
}

let hotels = [];
let cityFilter = 'All', starFilter = 'All', searchQuery = '';

function fetchHotelsList() {
  document.getElementById('hotel-loading').classList.remove('hidden');
  document.getElementById('hotel-grid').innerHTML = '';
  document.getElementById('hotel-empty').classList.add('hidden');
  document.getElementById('hotel-no-results').classList.add('hidden');

  const params = new URLSearchParams();
  if (cityFilter !== 'All') params.set('city', cityFilter);
  if (starFilter !== 'All') params.set('star', starFilter);
  const qs = params.toString();

  fetch(qs ? `${HOTEL_API_BASE}?${qs}` : HOTEL_API_BASE)
    .then(r => r.json())
    .then(d => {
      hotels = (d && d.success) ? (d.hotels || []) : [];
      document.getElementById('hotel-loading').classList.add('hidden');
      renderHotelGrid();
    })
    .catch(() => {
      hotels = [];
      document.getElementById('hotel-loading').classList.add('hidden');
      renderHotelGrid();
    });
}

function renderHotelGrid() {
  const grid = document.getElementById('hotel-grid');
  const visible = hotels.filter(h => searchQuery === '' || h.name.toLowerCase().includes(searchQuery.toLowerCase()));

  if (!hotels.length) { document.getElementById('hotel-empty').classList.remove('hidden'); grid.innerHTML = ''; return; }
  document.getElementById('hotel-empty').classList.add('hidden');
  document.getElementById('hotel-no-results').classList.toggle('hidden', visible.length > 0);

  grid.innerHTML = visible.map(h => {
    const thumb = (h.images && h.images[0]) ? `${window.BASE_URL}/${h.images[0]}` : (h.image_urls && h.image_urls[0]) || null;
    const dist = h.distance_info;
    let distHtml = '';
    if (dist) {
      const dm = (dist.unit === 'km') ? (dist.value * 1000) : (dist.value || 999);
      const near = dm <= 100, mid = dm <= 300;
      const colorClass = near ? 'secondary' : (mid ? 'yellow-400' : 'white/40');
      distHtml = `<div class="flex items-center gap-1.5 mb-4">
          <i data-lucide="navigation" class="w-3.5 h-3.5 text-${colorClass}"></i>
          <span class="text-xs font-medium text-${colorClass}">${dist.value}${dist.unit||'m'} from ${dist.landmark||''}</span>
          ${near ? `<span class="bg-secondary/10 text-secondary text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider ml-1">Walking</span>` : ''}
        </div>`;
    }
    return `<div class="hotel-card bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl overflow-hidden group hover:-translate-y-1 transition-all duration-300 flex flex-col cursor-pointer" onclick="openHotelModal('${h.sys_id}')">
      <div class="relative h-52 overflow-hidden">
        ${thumb ? `<img src="${thumb}" alt="${h.name}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" referrerpolicy="no-referrer">`
                : `<div class="w-full h-full flex items-center justify-center bg-dark"><i data-lucide="image" class="w-8 h-8 text-white/10"></i></div>`}
        <div class="absolute top-3 left-3 bg-dark/80 backdrop-blur-sm px-2.5 py-1 rounded-lg text-xs font-bold">${h.city||''}</div>
        <div class="absolute top-3 right-3 bg-secondary/90 text-primary px-2.5 py-1 rounded-lg text-xs font-bold">${'★'.repeat(h.star_rating||0)}</div>
      </div>
      <div class="p-6 flex flex-col flex-1">
        <h3 class="font-bold text-base mb-1 leading-snug">${h.name}</h3>
        <p class="text-white/40 text-xs mb-3 line-clamp-2">${h.description||''}</p>
        ${distHtml}
        <div class="mt-auto flex items-center justify-between pt-4 border-t border-white/10">
          <div>
            <span class="text-[10px] text-white/40 uppercase tracking-wider block">Tap to explore</span>
            <span class="text-sm font-bold text-secondary">Rooms & prices</span>
          </div>
          <span class="flex items-center gap-1.5 bg-secondary/10 text-secondary font-semibold py-2 px-4 rounded-xl text-xs">
            <i data-lucide="eye" class="w-3.5 h-3.5"></i> View Details
          </span>
        </div>
      </div>
    </div>`;
  }).join('');
  lucide.createIcons();
}

document.querySelectorAll('.city-filter').forEach(btn => {
  btn.addEventListener('click', () => {
    cityFilter = btn.getAttribute('data-city');
    document.querySelectorAll('.city-filter').forEach(b => {
      const active = b.getAttribute('data-city') === cityFilter;
      b.className = `city-filter ${active ? 'bg-secondary text-primary' : 'bg-white/5 text-white/60'} px-4 py-1.5 rounded-lg text-xs font-semibold transition-all`;
    });
    fetchHotelsList();
  });
});
document.getElementById('star-filter').addEventListener('change', e => { starFilter = e.target.value; fetchHotelsList(); });
document.getElementById('hotel-search').addEventListener('input', e => { searchQuery = e.target.value; renderHotelGrid(); });

fetchHotelsList();

// ── Hotel Details Modal ──
let hmMedia = [], hmIndex = 0, hmActiveHotel = null, hmRoomTypes = null, hmBedFilter = 'all';

function openHotelModal(hotelSysId) {
  const h = hotels.find(x => x.sys_id === hotelSysId);
  if (!h) return;
  hmActiveHotel = h;
  hmRoomTypes = null;
  hmBedFilter = 'all';

  document.getElementById('hm-name').textContent = h.name;
  document.getElementById('hm-meta').textContent = `${h.city} · ${'★'.repeat(h.star_rating||0)}`;
  document.getElementById('hm-desc').textContent = h.description || '';
  document.getElementById('hm-address').innerHTML = h.address ? `<i data-lucide="map-pin" class="w-3.5 h-3.5"></i> ${h.address}` : '';
  const dist = h.distance_info;
  document.getElementById('hm-distance').innerHTML = dist ? `<i data-lucide="navigation" class="w-3.5 h-3.5"></i> ${dist.value}${dist.unit||'m'} from ${dist.landmark||''}${dist.walking&&dist.walking.enabled?` · ${dist.walking.minutes} min walk`:''}` : '';
  document.getElementById('hm-whatsapp-btn').href = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent("I'm interested in " + h.name + " for my Umrah trip.")}`;

  hmMedia = buildCombinedMedia(h, []);
  hmIndex = 0;
  renderHmThumbs();
  renderHmSlider();
  document.getElementById('hm-bed-tabs').innerHTML = '';
  document.getElementById('hm-room-list').innerHTML = '<p class="text-xs text-white/30 text-center py-4">Loading room types...</p>';

  document.getElementById('hotel-modal-overlay').style.display = 'flex';
  lucide.createIcons();

  fetchRoomTypesForModal(hotelSysId);
}
function closeHotelModal() {
  document.getElementById('hotel-modal-overlay').style.display = 'none';
  document.getElementById('hm-slider-video').pause();
  document.getElementById('hm-slider-yt').src = '';
}

function buildCombinedMedia(hotel, roomTypes) {
  const combined = [];
  (hotel.image_urls||[]).forEach(u => combined.push({type:'image', src:u}));
  (hotel.images||[]).forEach(p => combined.push({type:'image', src:`${window.BASE_URL}/${p}`}));
  roomTypes.forEach(rt => {
    (rt.image_urls||[]).forEach(u => combined.push({type:'image', src:u}));
    (rt.images||[]).forEach(p => combined.push({type:'image', src:`${window.BASE_URL}/${p}`}));
  });
  (hotel.videos||[]).forEach(p => combined.push({type:'video', src:`${window.BASE_URL}/${p}`}));
  roomTypes.forEach(rt => (rt.videos||[]).forEach(p => combined.push({type:'video', src:`${window.BASE_URL}/${p}`})));
  (hotel.youtube_urls||[]).map(youtubeIdFromUrl).filter(Boolean).forEach(id => combined.push({type:'youtube', id}));
  roomTypes.forEach(rt => (rt.youtube_urls||[]).map(youtubeIdFromUrl).filter(Boolean).forEach(id => combined.push({type:'youtube', id})));
  return combined;
}

function fetchRoomTypesForModal(hotelSysId) {
  fetch(`${HOTEL_API_BASE}?scope=room_types&hotel_sys_id=${encodeURIComponent(hotelSysId)}`)
    .then(r => r.json())
    .then(async d => {
      const roomTypes = (d && d.success) ? (d.room_types || []) : [];
      await Promise.all(roomTypes.map(async rt => {
        rt.bed_count = (rt.bed_config||[]).reduce((sum,b) => sum + (b.quantity||0), 0);
        try {
          const res = await fetch(`${window.BASE_URL}/api/room-prices.php?room_type_sys_id=${encodeURIComponent(rt.sys_id)}`);
          const pd = await res.json();
          rt.board_prices = (pd && pd.success && pd.prices) ? (pd.prices.board_prices || []) : [];
        } catch(e) { rt.board_prices = []; }
      }));
      hmRoomTypes = roomTypes;

      if (hmActiveHotel && hmActiveHotel.sys_id === hotelSysId) {
        hmMedia = buildCombinedMedia(hmActiveHotel, roomTypes);
        renderHmThumbs();
        renderHmSlider();
        renderBedTabs();
        renderRoomList();
      }
    })
    .catch(() => {
      hmRoomTypes = [];
      document.getElementById('hm-room-list').innerHTML = '<p class="text-xs text-white/20">Couldn\'t load room types.</p>';
    });
}

function renderHmThumbs() {
  const wrap = document.getElementById('hm-thumbs');
  if (!hmMedia.length) { wrap.innerHTML = ''; return; }
  wrap.innerHTML = hmMedia.map((m, i) => {
    const thumbSrc = m.type === 'image' ? m.src : m.type === 'youtube' ? `https://img.youtube.com/vi/${m.id}/default.jpg` : '';
    return `<div onclick="hmIndex=${i}; renderHmSlider();" class="thumb aspect-square rounded-lg overflow-hidden bg-white/5 border ${i===hmIndex?'border-secondary':'border-white/10'} cursor-pointer relative">
      ${m.type==='video' ? `<video src="${m.src}#t=0.5" preload="metadata" muted class="w-full h-full object-cover pointer-events-none"></video><div class="absolute inset-0 flex items-center justify-center bg-black/20"><i data-lucide="play-circle" class="w-4 h-4 text-white"></i></div>` :
        `<img src="${thumbSrc}" class="w-full h-full object-cover" referrerpolicy="no-referrer">`}
    </div>`;
  }).join('');
  lucide.createIcons();
}
function renderHmSlider() {
  const img = document.getElementById('hm-slider-img'), video = document.getElementById('hm-slider-video'), yt = document.getElementById('hm-slider-yt'), empty = document.getElementById('hm-slider-empty');
  img.classList.add('hidden'); video.pause(); video.classList.add('hidden'); video.src=''; yt.classList.add('hidden'); yt.src=''; empty.classList.add('hidden');

  if (!hmMedia.length) { empty.classList.remove('hidden'); document.getElementById('hm-slider-counter').textContent=''; return; }
  const m = hmMedia[hmIndex];
  if (m.type === 'image') { img.src = m.src; img.classList.remove('hidden'); }
  else if (m.type === 'video') { video.src = m.src; video.classList.remove('hidden'); }
  else if (m.type === 'youtube') { yt.src = `https://www.youtube.com/embed/${m.id}`; yt.classList.remove('hidden'); }
  document.getElementById('hm-slider-counter').textContent = `${hmIndex+1} / ${hmMedia.length}`;
  document.querySelectorAll('#hm-thumbs .thumb').forEach((t,i) => t.classList.toggle('border-secondary', i===hmIndex));
}
function hmSliderNav(dir) {
  if (!hmMedia.length) return;
  hmIndex = (hmIndex + dir + hmMedia.length) % hmMedia.length;
  renderHmSlider();
}

function renderBedTabs() {
  const roomTypes = hmRoomTypes || [];
  const bedCounts = [...new Set(roomTypes.map(rt => rt.bed_count || 0))].sort((a,b)=>a-b);
  const wrap = document.getElementById('hm-bed-tabs');
  if (bedCounts.length <= 1) { wrap.innerHTML = ''; return; }
  wrap.innerHTML = `<button onclick="hmBedFilter='all'; renderRoomList();" class="hm-bed-tab px-3 py-1.5 rounded-lg text-xs font-semibold ${hmBedFilter==='all'?'bg-secondary text-primary':'bg-white/5 text-white/60'}">All</button>` +
    bedCounts.map(bc => `<button onclick="hmBedFilter=${bc}; renderRoomList();" class="hm-bed-tab px-3 py-1.5 rounded-lg text-xs font-semibold ${hmBedFilter===bc?'bg-secondary text-primary':'bg-white/5 text-white/60'}">${bc} bed${bc===1?'':'s'}</button>`).join('');
}
function renderBedTabsActiveOnly() {
  document.querySelectorAll('#hm-bed-tabs button').forEach(btn => {
    const isAll = btn.textContent.trim() === 'All';
    const active = isAll ? hmBedFilter === 'all' : parseInt(btn.textContent) === hmBedFilter;
    btn.className = `hm-bed-tab px-3 py-1.5 rounded-lg text-xs font-semibold ${active?'bg-secondary text-primary':'bg-white/5 text-white/60'}`;
  });
}
function renderRoomList() {
  renderBedTabsActiveOnly();
  const roomTypes = (hmRoomTypes || []).filter(rt => hmBedFilter === 'all' || rt.bed_count === hmBedFilter);
  const list = document.getElementById('hm-room-list');
  if (!roomTypes.length) { list.innerHTML = '<p class="text-xs text-white/20">No room types available.</p>'; return; }

  list.innerHTML = roomTypes.map(rt => {
    const cap = rt.person_capacity || {};
    const sizeLabel = formatRoomSize(rt.size);
    const cheapest = (rt.board_prices||[]).reduce((min,bp) => (min===null||bp.price<min) ? bp.price : min, null);
    return `<div class="bg-white/5 border border-white/10 rounded-xl p-4">
      <div class="flex items-start justify-between gap-2 mb-1">
        <h4 class="font-bold text-sm">${rt.name}</h4>
        ${cheapest!==null ? `<span class="text-secondary font-bold text-sm shrink-0">SR ${Number(cheapest).toFixed(0)}</span>` : ''}
      </div>
      <p class="text-[11px] text-white/40 mb-2">Max ${cap.adults||0} Adults${cap.children?' + '+cap.children+' Children':''}${sizeLabel?' · '+sizeLabel:''}${rt.bed_count?' · '+rt.bed_count+' bed'+(rt.bed_count===1?'':'s'):''}</p>
      ${rt.description ? `<p class="text-xs text-white/50 mb-2">${rt.description}</p>` : ''}
      ${(rt.amenities&&rt.amenities.length) ? `<div class="flex flex-wrap gap-1.5">${rt.amenities.map(a=>`<span class="text-[10px] bg-dark border border-white/10 px-2 py-0.5 rounded-full text-white/50">${a}</span>`).join('')}</div>` : ''}
      ${(rt.view_info&&Number(rt.view_info.enabled)) ? `<p class="text-[11px] text-secondary mt-2 flex items-center gap-1"><i data-lucide="eye" class="w-3 h-3"></i> ${rt.view_info.description||'View available'}</p>` : ''}
    </div>`;
  }).join('');
  lucide.createIcons();
}
document.getElementById('hotel-modal-overlay').addEventListener('click', e => { if (e.target.id === 'hotel-modal-overlay') closeHotelModal(); });
</script>
</body>
</html>