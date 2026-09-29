<?php
// FILE PATH: /pages/package-builder.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle       = 'Build Your Journey | TravHub Umrah';
$pageDescription = 'Custom Umrah package builder — select transport, Moyallem, hotel and more.';
include dirname(__DIR__) . '/includes/header.php';
$csrf = csrfToken();
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<style type="text/tailwindcss">
@layer components {
  .glass-card  { @apply bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl; }
  .btn-primary { @apply bg-secondary hover:bg-emerald text-primary font-semibold py-3 px-6 rounded-xl transition-all duration-300; }
}
</style>

<!-- Meal-plan minimum-group-size modal -->
<div id="meal-blocked-modal-overlay" class="fixed inset-0 z-[60] bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="glass-card max-w-sm w-full p-6 text-center">
    <div class="w-12 h-12 rounded-full bg-amber-500/10 flex items-center justify-center mx-auto mb-4">
      <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-400"></i>
    </div>
    <h3 class="font-bold text-lg mb-2">Minimum Group Size</h3>
    <p id="meal-blocked-modal-text" class="text-white/50 text-sm mb-6">To avail this, you need at least 10 adult travelers.</p>
    <button onclick="closeMealBlockedModal()" class="w-full bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm">Got It</button>
  </div>
</div>

<section class="pt-28 pb-24">
  <div class="max-w-5xl mx-auto px-4">
    <!-- Hero text -->
    <div class="text-center mb-10">
      <span class="inline-block text-secondary bg-secondary/10 border border-secondary/20 px-4 py-1 rounded-full text-xs font-bold uppercase tracking-widest mb-3">Personalised Journey</span>
      <h1 class="text-3xl lg:text-4xl font-bold">Build Your Umrah Journey</h1>
      <p class="text-white/40 mt-2 text-sm">Step by step — your boarding pass fills in as you go</p>
    </div>

    <!-- Flight Path Progress -->
    <div class="mb-10 relative px-2">
      <div class="relative h-1.5 rounded-full overflow-hidden flight-path-track">
        <div id="flight-path-fill" class="h-full flight-path-fill" style="width:0%"></div>
      </div>
      <div id="flight-plane" class="absolute -top-2.5 transition-all duration-700" style="left:0%">
        <div class="w-6 h-6 -ml-3 rounded-full bg-secondary flex items-center justify-center shadow-lg shadow-secondary/40">
          <i data-lucide="plane" class="w-3.5 h-3.5 text-primary"></i>
        </div>
      </div>
      <div class="relative mt-4 h-9" id="step-tracker"></div>
    </div>

    <!-- Builder Grid -->
    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Step Content -->
      <div class="lg:col-span-2">
        <div id="step-hint-banner" class="hidden mb-4 px-4 py-3 rounded-xl border border-amber-400/20 bg-amber-400/5 text-amber-400/90 text-sm flex items-center gap-2">
          <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
          <span id="step-hint-text"></span>
        </div>
        <div id="step-content" class="min-h-[420px]"></div>
      </div>
      <!-- Sidebar: Nav buttons (sticky, above Boarding Pass) + Boarding Pass Summary -->
      <div class="lg:col-span-1">
        <div class="sticky top-28 space-y-4">
          <!-- Navigation — placed here (not below the step content) so it's
               always visible without scrolling, however long a step gets. -->
          <div class="glass-card p-4 flex justify-between items-center">
            <button id="prev-btn" disabled class="flex items-center gap-2 font-bold text-sm text-white/40 hover:text-white transition-colors disabled:opacity-20 disabled:cursor-not-allowed">
              <i data-lucide="arrow-left" class="w-4 h-4"></i> Previous
            </button>
            <button id="next-btn" class="btn-primary flex items-center gap-2 text-sm py-2.5 px-5">
              Next <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
          </div>
          <div class="boarding-stub rounded-2xl overflow-hidden">
          <div class="p-5">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-bold flex items-center gap-2 text-sm"><i data-lucide="ticket" class="w-4 h-4 text-secondary"></i> Boarding Pass</h3>
              <span class="text-[9px] text-white/30 uppercase tracking-widest">Preview</span>
            </div>
            <div id="ticket-punches" class="ticket-punch mb-4"></div>
            <div id="summary-details" class="space-y-3 mb-2 text-xs"></div>
          </div>
          <!-- perforated divider -->
          <div class="relative py-1">
            <div class="absolute left-0 top-1/2 w-4 h-4 -ml-2 -mt-2 rounded-full bg-dark"></div>
            <div class="absolute right-0 top-1/2 w-4 h-4 -mr-2 -mt-2 rounded-full bg-dark"></div>
            <div class="border-t-2 border-dashed border-white/10 mx-4"></div>
          </div>
          <div class="p-5 pt-3">
            <div class="flex justify-between items-center mb-1">
              <span class="text-white/40 text-xs uppercase tracking-wider">Est. Total</span>
              <span class="text-xl font-bold text-secondary" id="total-price">SR 0</span>
            </div>
            <p class="text-[9px] text-white/20 uppercase tracking-wider">Per person • Subject to availability</p>
          </div>
        </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/currency-handler.js?v=<?= ASSET_VERSION ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js?v=<?= ASSET_VERSION ?>"></script>
<script>
// ──────────────────────────────────────────
// STATE
// ──────────────────────────────────────────
const DEFAULT_SEL = {
  travelers: { name:'', email:'', phone:'', adults:1, children:0, infants:0 },
  choices: { visa:false, flight:false, hotel:false, transport:false, ziarah:false, moyallem:false, meal:false },
  visa:           null,
  packageType:    null,
  connectionType: null,     // 'Direct' | 'Connecting' | 'Both'
  flightDate:     null,
  duration:       10,
  makkahStay:     6,
  madinahStay:    4,
  hotelCategory:  '4-Star',
  accommodation:  [],  // array of { hotel_sys_id, hotel_name, room_type_sys_id, room_name, board_type_sys_id, board_name, price, max_adults, max_children }
  transport: [],  // array of { route_sys_id, route_name, vehicle_type, vehicle_type_sys_id, price } — multiple legs supported
  moyallem: [],   // array of { sys_id, name, category, price }
  ziarah: [],     // array of { sys_id, name, price, wantsTransport, routeId, routeName, wantsMoyallem, moyallemCategory, moyallemPriceSar }
  meal: null,     // { meal_sys_id, meal_name, tier, price }
  totalBase:      0
};

function loadSel() {
  // Defensive merge — never trust localStorage shape blindly. Older/other
  // pages (e.g. the homepage mini-builder) may have written a partial or
  // differently-shaped object under the same key; a missing nested field
  // (like `moyallem`) would otherwise crash every render on this page.
  let saved = {};
  try { saved = JSON.parse(localStorage.getItem('umrah_journey_v2')) || {}; }
  catch(e) { saved = {}; }
  return {
    ...DEFAULT_SEL,
    ...saved,
    travelers: { ...DEFAULT_SEL.travelers, ...(saved.travelers || {}) },
    choices:   { ...DEFAULT_SEL.choices,   ...(saved.choices   || {}) },
    transport: Array.isArray(saved.transport) ? saved.transport : DEFAULT_SEL.transport,
    moyallem:  Array.isArray(saved.moyallem) ? saved.moyallem : DEFAULT_SEL.moyallem,
    ziarah:    Array.isArray(saved.ziarah)   ? saved.ziarah   : DEFAULT_SEL.ziarah,
    accommodation: Array.isArray(saved.accommodation) ? saved.accommodation : DEFAULT_SEL.accommodation,
  };
}

const state = {
  currentStep: 1,
  visited: [1],
  currency: localStorage.getItem('travhub_currency') || 'SAR',
  symbols: { SAR: 'SR ', USD: '$', BDT: '৳' },
  rates: { SAR: 1, USD: 0.2664, BDT: 32.5 },
  transportRoutes: [],
  vehicleTypesById: {},
  moyallemServices: [],
  visaTypes: [],
  flights: [],
  ziarahList: [],
  meals: [],
  serviceLevels: [],
  hotels: [],
  hotelsLoading: false,
  hotelsFetchFailed: false,
  hotelsFetchAttempted: false,
  expandedHotelSysId: null,
  expandedRoomTypeSysId: null,
  selectedHotelCity: 'Makkah',
  roomTypeCache: {},
  globalBoardTypes: undefined,
  roomPricesCache: {},
  transportPickerRouteId: null,
  ziarahAutoOpenedFor: new Set(),
  sel: loadSel()
};

// ── Dynamic step list — depends on "Your Choice" toggles (state.sel.choices) ──
// Every step has a stable `key` used everywhere in logic, and its display
// `id`/position among currently-visible steps is recalculated live.
const ALL_STEPS = [
  { key:'travelers',     title:'Travelers',     icon:'users',        desc:'Who is going?' },
  { key:'duration',      title:'Duration',      icon:'calendar',     desc:'Stay length' },
  { key:'choices',       title:'Your Choice',   icon:'list-checks',  desc:'What do you need?' },
  { key:'flight',        title:'Flights',       icon:'plane',        desc:'Travel mode' },
  { key:'accommodation', title:'Accommodation', icon:'hotel',        desc:'Hotel & room' },
  { key:'ziarah',        title:'Ziarah',        icon:'landmark',     desc:'Guided visits' },
  { key:'transport',     title:'Transport',     icon:'car',          desc:'Ground travel' },
  { key:'moyallem',      title:'Moyallem',      icon:'user-check',   desc:'Spiritual guide' },
  { key:'review',        title:'Review',        icon:'check-circle', desc:'Final check' },
];

// `key` here isn't limited to entries in ALL_STEPS — 'visa' is also passed
// in from getVisaPrice()/review/summary to gate visa-related UI even though
// Visa no longer has its own dedicated step (see "Your Choice").
function stepIsVisible(key) {
  const c = state.sel.choices;
  if (key === 'visa')          return !!c.visa;
  if (key === 'flight')        return !!c.flight;
  if (key === 'accommodation') return !!c.hotel;
  if (key === 'transport')     return !!c.transport;
  if (key === 'ziarah')        return !!c.ziarah;
  if (key === 'moyallem')      return !!c.moyallem;
  if (key === 'meal')          return !!c.meal;
  return true; // travelers, choices, package, duration, review
}
function visibleSteps() { return ALL_STEPS.filter(s => stepIsVisible(s.key)).map((s,i)=>({...s, id:i+1})); }
function getCurrentStepKey() { const vs=visibleSteps(); const found=vs.find(s=>s.id===state.currentStep); return found ? found.key : vs[0].key; }
function totalSteps() { return visibleSteps().length; }
function clampStep() {
  const t = totalSteps();
  if (state.currentStep > t) state.currentStep = t;
  if (state.currentStep < 1) state.currentStep = 1;
  // Positional IDs are recalculated every time the visible step list changes
  // (choices toggled) — drop any visited marks outside the current range so
  // a stale/mismatched position is never clickable.
  state.visited = state.visited.filter(id => id >= 1 && id <= t);
  if (!state.visited.includes(state.currentStep)) state.visited.push(state.currentStep);
}

const STEPS = ALL_STEPS; // kept for any external reference; prefer visibleSteps() internally

const BASE_PRICING = {
  pkg:     { 'Economy':1200,   'Premium':2500,      'Luxury':4500 },
  hotel:   { '3-Star':200,     '4-Star':450,        '5-Star':900 }
};

// ──────────────────────────────────────────
// DATA LOADING
// ──────────────────────────────────────────
async function loadTransportRoutes() {
  try {
    const res  = await fetch(`<?= BASE_URL ?>/api/transport-routes.php`);
    const data = await res.json();
    if (data.success) state.transportRoutes = data.routes || [];
  } catch(e) { console.warn('Transport routes unavailable'); }
}

// Routes only store vehicle_type_sys_id now (a relational reference, not
// a plain name string) — this lookup map resolves a sys_id to a display
// name/icon wherever a route's vehicle_options are shown.
async function loadVehicleTypes() {
  try {
    const res  = await fetch(`<?= BASE_URL ?>/api/vehicle-types.php`);
    const data = await res.json();
    if (data.success) {
      state.vehicleTypesById = {};
      (data.vehicle_types||[]).forEach(v => { state.vehicleTypesById[v.sys_id] = v; });
    }
  } catch(e) { console.warn('Vehicle types unavailable'); }
}

async function loadMoyallemServices() {
  try {
    const res  = await fetch(`<?= BASE_URL ?>/api/moyallem-handler.php`);
    const data = await res.json();
    if (data.success) state.moyallemServices = data.services || [];
  } catch(e) { console.warn('Moyallem services unavailable'); }
}

async function loadVisaTypes() {
  try {
    const res  = await fetch(`<?= BASE_URL ?>/api/visa-types.php`);
    const data = await res.json();
    if (data.success) {
      state.visaTypes = data.visa_types || [];
      ensureVisaAutoSelected();
    }
  } catch(e) { console.warn('Visa types unavailable'); }
}

async function loadZiarahList() {
  try {
    const res  = await fetch(`<?= BASE_URL ?>/api/ziarah.php`);
    const data = await res.json();
    if (data.success) state.ziarahList = data.ziarah || [];
  } catch(e) { console.warn('Ziarah list unavailable'); }
}

async function loadMeals() {
  try {
    const res  = await fetch(`<?= BASE_URL ?>/api/meals.php`);
    const data = await res.json();
    if (data.success) state.meals = data.meals || [];
  } catch(e) { console.warn('Meal plans unavailable'); }
}

async function loadServiceLevels() {
  try {
    const res  = await fetch(`<?= BASE_URL ?>/api/service-levels.php`);
    const data = await res.json();
    if (data.success) state.serviceLevels = data.service_levels || [];
  } catch(e) { console.warn('Service levels unavailable'); }
}

// ── Hotels — fetched on demand from our own database (api/hotels.php),
// only once the traveler actually reaches the Accommodation step, filtered
// by the active city tab. ──
const HOTEL_API_BASE = `<?= BASE_URL ?>/api/hotels.php`;
let hotelFetchController = null;
function fetchHotels(city) {
  // Abort any in-flight request first — avoids a pile-up of duplicate
  // pending requests if this fires again (e.g. Refresh) before the first
  // one resolved.
  if (hotelFetchController) hotelFetchController.abort();
  hotelFetchController = new AbortController();

  state.hotelsLoading = true;
  state.hotelsFetchFailed = false;
  const star = parseInt(state.sel.hotelCategory) || '';
  const params = new URLSearchParams();
  if (city) params.set('city', city);
  if (star) params.set('star', star);
  const qs = params.toString();
  const url = qs ? `${HOTEL_API_BASE}?${qs}` : HOTEL_API_BASE;
  fetch(url, { signal: hotelFetchController.signal })
    .then(r => r.json())
    .then(d => {
      state.hotelsLoading = false;
      state.hotelsFetchAttempted = true; // settled — even 0 results shouldn't re-trigger
      state.hotels = (d && d.success) ? (d.hotels || []) : [];
      if (getCurrentStepKey() === 'accommodation') renderStepContent();
    })
    .catch(err => {
      if (err && err.name === 'AbortError') return; // superseded by a newer request
      state.hotelsLoading = false;
      state.hotelsFetchFailed = true;
      state.hotelsFetchAttempted = true;
      state.hotels = [];
      console.warn('Hotel API unavailable:', err);
      if (getCurrentStepKey() === 'accommodation') renderStepContent();
    });
}

// One hotel's room types — fetched on demand, cached per hotel sys_id.
function fetchRoomTypes(hotelSysId) {
  if (state.roomTypeCache[hotelSysId]) { renderStepContent(); return; }
  fetch(`${HOTEL_API_BASE}?scope=room_types&hotel_sys_id=${encodeURIComponent(hotelSysId)}`)
    .then(r => r.json())
    .then(d => {
      state.roomTypeCache[hotelSysId] = (d && d.success) ? (d.room_types || []) : [];
      if (getCurrentStepKey() === 'accommodation') renderStepContent();
    })
    .catch(() => {
      state.roomTypeCache[hotelSysId] = [];
      if (getCurrentStepKey() === 'accommodation') renderStepContent();
    });
}

// Fetch the global board-type catalog (Room Only / Bed & Breakfast / etc)
// — same list for every room type, so this only needs to run once.
function fetchGlobalBoardTypes() {
  if (state.globalBoardTypes !== undefined) { renderStepContent(); return; }
  fetch(`<?= BASE_URL ?>/api/room-board-types.php`)
    .then(r => r.json())
    .then(d => {
      state.globalBoardTypes = (d && d.success) ? (d.board_types || []) : [];
      if (getCurrentStepKey() === 'accommodation') renderStepContent();
    })
    .catch(() => {
      state.globalBoardTypes = [];
      if (getCurrentStepKey() === 'accommodation') renderStepContent();
    });
}

// One room type's board prices (all board-type prices for that room come
// back together) — cached per room type.
function fetchRoomPrices(roomTypeSysId) {
  if (state.roomPricesCache[roomTypeSysId] !== undefined) { renderStepContent(); return; }
  fetch(`<?= BASE_URL ?>/api/room-prices.php?room_type_sys_id=${encodeURIComponent(roomTypeSysId)}`)
    .then(r => r.json())
    .then(d => {
      const row = (d && d.success) ? d.prices : null;
      state.roomPricesCache[roomTypeSysId] = row ? (row.board_prices || []) : [];
      if (getCurrentStepKey() === 'accommodation') renderStepContent();
    })
    .catch(() => {
      state.roomPricesCache[roomTypeSysId] = [];
      if (getCurrentStepKey() === 'accommodation') renderStepContent();
    });
}

// Picks the price entry for one board type on one room, preferring the one
// whose validity range covers today; falls back to the first match if none
// currently applies (still shows a number rather than none).
function getBoardPriceFor(roomTypeSysId, boardTypeSysId) {
  const prices = (state.roomPricesCache[roomTypeSysId] || []).filter(p => p.board_type_sys_id === boardTypeSysId);
  if (!prices.length) return null;
  const today = new Date().toISOString().slice(0,10);
  return prices.find(p => p.valid_from <= today && today <= p.valid_to) || prices[0];
}



// Visa no longer has its own dedicated step — turning the "Visa" toggle ON
// in "Your Choice" auto-picks the first Umrah-named visa type (or the
// first available one) instead of making the traveler choose. Called both
// right after visa types finish loading and right when the toggle is
// switched on, since either can happen first.
function ensureVisaAutoSelected() {
  if (state.sel.choices.visa && !state.sel.visa && state.visaTypes.length) {
    const umrahVisa = state.visaTypes.find(v => /umrah/i.test(v.name)) || state.visaTypes[0];
    state.sel.visa = umrahVisa.name;
  }
}

async function loadFlights() {
  try {
    const res  = await fetch(`<?= BASE_URL ?>/api/flights.php`);
    const data = await res.json();
    if (data.success) {
      state.flights = data.flights || [];
    }
  } catch(e) { console.warn('Flights unavailable'); }
}

// ── Live price lookups ──
function getVisaPrice() {
  if (!stepIsVisible('visa')) return 0;
  const v = state.visaTypes.find(x => x.name === state.sel.visa);
  return v ? parseFloat(v.price) || 0 : 0;
}

// Transforms this page's own `state.sel` shape into the shape
// api/save-custom-build.php expects (same shape pages/index.php's mini-
// builder submits) — only this page's own state uses flat travelers.adults,
// s.visa as a plain name string, s.packageType, s.connectionType, etc.,
// so those get remapped/nested here rather than changing the shared API.
function buildSubmissionPayload() {
  const visaObj = state.visaTypes.find(x => x.name === state.sel.visa);
  return {
    travelers: {
      name: state.sel.travelers.name, phone: state.sel.travelers.phone, email: state.sel.travelers.email,
      no_of_pax: { adult: state.sel.travelers.adults, child: state.sel.travelers.children, infant: state.sel.travelers.infants }
    },
    choices: state.sel.choices,
    serviceLevel: state.sel.packageType,
    hotelCategory: state.sel.hotelCategory,
    visa: state.sel.visa ? { visa_type: state.sel.visa, visa_type_sys_id: visaObj ? visaObj.sys_id : null, price: getVisaPrice() } : null,
    flight: state.sel.connectionType ? { connection_type: state.sel.connectionType } : null,
    accommodation: state.sel.accommodation || [],
    transport: state.sel.transport || [],
    ziarah: state.sel.ziarah || [],
    moyallem: state.sel.moyallem || [],
    meal: state.sel.meal,
    duration: state.sel.duration,
    makkahStay: state.sel.makkahStay,
    madinahStay: state.sel.madinahStay,
  };
}
// Flight fare is intentionally NOT added to the total — see the notice
// shown in the Flight step; it's confirmed later by the team once
// availability is checked, so it can't be estimated up front.
function getFlightPrice() {
  return 0;
}
// Real per-night room prices × total trip duration (nights) — replaces the
// old flat BASE_PRICING.hotel estimate now that actual hotel/room rates
// come from our own database.
function getAccommodationPrice() {
  if (!stepIsVisible('accommodation')) return 0;
  return (state.sel.accommodation || []).reduce((sum, a) => sum + (parseFloat(a.price) || 0), 0) * state.sel.duration;
}
// Meal price is already per-adult — added to calcTotal() AFTER the
// per-adult multiplication so it isn't multiplied twice.
function getMealPrice() {
  if (!stepIsVisible('meal') || !state.sel.meal) return 0;
  return (parseFloat(state.sel.meal.price) || 0) * Math.max(1, state.sel.travelers.adults);
}

// ──────────────────────────────────────────
// CURRENCY (sync with header selector)
// ──────────────────────────────────────────
document.addEventListener('currencyChanged', e => {
  state.currency = e.detail || localStorage.getItem('travhub_currency') || 'SAR';
  const r = CurrencyHandler.getRates();
  if (r) state.rates = r;
  renderSummary();
  if (getCurrentStepKey() === 'transport') renderStepContent(); // re-render transport prices
});

// ──────────────────────────────────────────
// CALCULATIONS
// ──────────────────────────────────────────
function calcTotal() {
  const s = state.sel;
  // Visa is required for every traveler regardless of age — adults,
  // children, and infants all need one. The service-level package tier
  // is paid per adult only.
  const totalTravelers = (s.travelers.adults||0) + (s.travelers.children||0) + (s.travelers.infants||0);
  let total = getVisaPrice() * Math.max(1, totalTravelers);
  total += parseFloat(state.serviceLevels.find(l => l.name === s.packageType)?.price || 0) * Math.max(1, s.travelers.adults);

  total += getFlightPrice();
  if (stepIsVisible('accommodation')) total += getAccommodationPrice();
  total += (s.transport || []).reduce((a, l) => a + (l.price || 0), 0);
  total += (s.moyallem || []).reduce((a, m) => a + (m.price || 0), 0);
  if (stepIsVisible('ziarah')) total += (s.ziarah || []).reduce((a, z) => a + (z.price || 0) + (z.moyallemPriceSar || 0), 0);
  total += getMealPrice(); // already per-adult, see getMealPrice()
  return total;
}

function fmtPrice(sar) {
  const converted = sar * (state.rates[state.currency] || 1);
  const sym = state.symbols[state.currency] || '';
  if (state.currency === 'BDT') {
    const n = Math.round(converted).toString();
    if (n.length <= 3) return sym + n;
    const last3 = n.slice(-3);
    const rest  = n.slice(0, -3).replace(/(\d)(?=(\d{2})+$)/, '$1,');
    return sym + rest + ',' + last3;
  }
  return sym + Math.round(converted).toLocaleString();
}

// ──────────────────────────────────────────
// RENDER — STEP TRACKER
// ──────────────────────────────────────────
function renderTracker() {
  const container = document.getElementById('step-tracker');
  const fill      = document.getElementById('flight-path-fill');
  const plane     = document.getElementById('flight-plane');
  const steps = visibleSteps();
  clampStep();
  const total = steps.length - 1;
  const fraction = total > 0 ? (state.currentStep - 1) / total : 0;
  const pct = fraction * 100;

  if (fill)  fill.style.width = pct + '%';
  if (plane) plane.style.left = `calc(${pct}% - ${pct/100 * 12}px)`; // nudge to keep plane centered on the fill edge

  // Dots use the exact same fraction formula as the plane/fill above
  // (i / (steps.length - 1)) so they always land perfectly under the
  // flight-path track regardless of how many steps are currently visible.
  container.innerHTML = steps.map((s, i) => {
    const stepFraction = total > 0 ? i / total : 0;
    const stepPct = stepFraction * 100;
    const status = state.currentStep === s.id ? 'active' : (state.currentStep > s.id ? 'completed' : 'pending');
    const dotColor = status === 'pending' ? 'bg-white/15' : 'bg-secondary';
    const clickable = isStepClickable(s.id);
    return `<div class="absolute top-0 flex flex-col items-center gap-1.5 text-center -translate-x-1/2 ${clickable ? 'cursor-pointer group' : 'cursor-default'}"
        style="left:${stepPct}%" onclick="${clickable ? `goToStep(${s.id})` : ''}">
      <span class="w-1.5 h-1.5 rounded-full ${dotColor} ${status==='active' ? 'ring-4 ring-secondary/20' : ''} ${clickable ? 'group-hover:scale-125 transition-transform' : ''}"></span>
      <p class="text-[8.5px] font-bold uppercase tracking-tighter ${state.currentStep >= s.id ? 'text-white/70' : 'text-white/20'} ${clickable ? 'group-hover:text-secondary' : ''} leading-tight whitespace-nowrap transition-colors">${s.title}</p>
    </div>`;
  }).join('');
  lucide.createIcons();
}

// A step's dot is clickable only if it's already been visited, or it's the
// step currently being shown — never a future/unvisited step.
function isStepClickable(stepId) {
  return state.visited.includes(stepId) || stepId === state.currentStep;
}

function goToStep(stepId) {
  if (!isStepClickable(stepId)) return;
  if (stepId === state.currentStep) return;
  state.currentStep = stepId;
  if (!state.visited.includes(stepId)) state.visited.push(stepId);
  renderTracker();
  renderStepContent(true);
  renderSummary();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ──────────────────────────────────────────
// RENDER — SUMMARY
// ──────────────────────────────────────────
function renderSummary() {
  const s = state.sel;
  const container = document.getElementById('summary-details');
  const totalEl   = document.getElementById('total-price');
  const punchContainer = document.getElementById('ticket-punches');

  if (punchContainer) {
    const steps = visibleSteps();
    punchContainer.innerHTML = steps.map(st => {
      const filled = state.currentStep > st.id || (state.currentStep === st.id && stepValid(st.key));
      return `<span class="${filled ? 'filled' : ''}"></span>`;
    }).join('');
  }

  const rows = [
    ['users',        'Travelers',  `${s.travelers.adults}A${s.travelers.children?' +'+s.travelers.children+'C':''}`],
    ...(stepIsVisible('visa') ? [['file-text', 'Visa', s.visa || '—']] : []),
    ['package',      'Package',    s.packageType || '—'],
    ...(stepIsVisible('flight') ? [['plane', 'Flight', s.connectionType ? `${s.connectionType}${s.flightDate?' • '+s.flightDate:''}` : '—']] : []),
    ['calendar',     'Duration',   `${s.duration}d (${s.makkahStay}M/${s.madinahStay}N)`],
    ...(stepIsVisible('transport') ? [['car', 'Transport', (s.transport||[]).length ? s.transport.map(l=>l.vehicle_type).join(', ') : '—']] : []),
    ...(stepIsVisible('ziarah') ? [['landmark', 'Ziarah', (s.ziarah||[]).length ? s.ziarah.map(z=>z.name).join(', ') : '—']] : []),
    ...(stepIsVisible('moyallem') ? [['user-check', 'Moyallem', (s.moyallem||[]).length ? s.moyallem.map(m=>m.name).join(', ') : '—']] : []),
    ...(stepIsVisible('meal') ? [['utensils', 'Meal', s.meal ? s.meal.tier : '—']] : []),
  ];

  container.innerHTML = rows.map(([icon, label, val]) => `
    <div class="flex items-start gap-2">
      <i data-lucide="${icon}" class="w-3.5 h-3.5 text-white/20 mt-0.5 shrink-0"></i>
      <div class="min-w-0">
        <p class="text-[9px] text-white/30 uppercase tracking-wider">${label}</p>
        <p class="text-xs font-medium truncate ${val==='—'?'text-white/20 italic':'text-white'}">${val}</p>
      </div>
    </div>`).join('');

  totalEl.textContent = fmtPrice(calcTotal());
  lucide.createIcons();
}

// ──────────────────────────────────────────
// RENDER — STEP CONTENT
// ──────────────────────────────────────────
function renderStepContent(isNavigation = false) {
  const container = document.getElementById('step-content');
  const s = state.sel;
  let html = '';
  const stepKey = getCurrentStepKey();

  switch (stepKey) {

    // ── Travelers ──
    case 'travelers':
      html = `<div class="space-y-6 animate-slide-up">
        <h2 class="text-2xl font-bold">Traveler Information</h2>
        <div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-2">Full Name *</label>
          <input type="text" id="t-name" value="${s.travelers.name||''}"
            placeholder="As per passport"
            onchange="updTravelerField('name', this.value)"
            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
          <p class="text-[10px] text-white/25 mt-1.5">Contact number and email are collected on the Review step.</p>
        </div>
        <div class="grid grid-cols-3 gap-4 mt-2">
          ${['Adults','Children','Infants'].map(type => {
            const k = type.toLowerCase();
            return `<div class="glass-card p-5 flex flex-col items-center gap-3">
              <div class="text-center"><p class="text-xs text-white/40 uppercase tracking-wider font-bold">${type}</p>
                <p class="text-[9px] text-white/25">${type==='Adults'?'12+':type==='Children'?'2–11':'<2'}</p></div>
              <div class="flex items-center gap-4">
                <button onclick="updTraveler('${k}',-1)" class="w-9 h-9 rounded-full border border-white/10 hover:border-secondary hover:text-secondary transition-all text-lg font-bold">−</button>
                <span id="traveler-count-${k}" class="text-2xl font-bold w-7 text-center">${s.travelers[k]}</span>
                <button onclick="updTraveler('${k}',1)" class="w-9 h-9 rounded-full border border-white/10 hover:border-secondary hover:text-secondary transition-all text-lg font-bold">+</button>
              </div>
            </div>`;
          }).join('')}
        </div>
        <div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-3">Service Level</label>
          ${state.serviceLevels.length === 0
            ? `<div class="glass-card p-8 text-center text-white/30"><i data-lucide="star" class="w-8 h-8 mx-auto mb-2 opacity-30"></i><p class="text-sm">Loading service levels...</p></div>`
            : `<div class="grid md:grid-cols-3 gap-4">
            ${state.serviceLevels.map(lvl => `
              <div onclick="pick('packageType','${lvl.name.replace(/'/g,"\\'")}')" class="ticket-card p-5 ${s.packageType===lvl.name?'selected':''}">
                <h3 class="font-bold text-lg mb-2">${lvl.name}</h3>
                <p class="text-secondary font-bold text-xl mb-3">${fmtPrice(+lvl.price)}</p>
                <ul class="text-xs text-white/40 space-y-1.5">
                  ${(lvl.features||[]).map(f => `<li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3 h-3 text-secondary"></i> ${f}</li>`).join('')}
                </ul>
              </div>`).join('')}
          </div>`}
        </div>
      </div>`;
      break;
    case 'choices':
      const ch = s.choices;
      const mealDef     = state.meals[0];
      const minAdults   = mealDef?.min_adults_required || 10;
      const mealBlocked = s.travelers.adults < minAdults;
      html = `<div class="space-y-8 animate-slide-up">
        <div>
          <h2 class="text-2xl font-bold">What do you need for this journey?</h2>
          <p class="text-white/40 text-sm">Toggle what you want — we'll only show the steps that matter to you.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
          ${[
            ['visa',      'Visa',      'file-text',  'Entry visa processing'],
            ['flight',    'Flight',    'plane',      'Air travel arrangement'],
            ['hotel',     'Hotel',     'hotel',      'Accommodation in Makkah & Madinah'],
            ['transport', 'Transport', 'car',        'Ground transport between cities'],
            ['ziarah',    'Ziarah',    'landmark',   'Guided historical site visits'],
            ['moyallem',  'Moyallem',  'user-check', 'Spiritual guide services'],
            ['meal',      'Meal',      'utensils',   'Daily meal plan'],
          ].map(([key,label,icon,desc]) => {
            const on = !!ch[key];
            return `
            <div onclick="toggleChoice('${key}')" class="ticket-card p-5 ${on?'selected':''}">
              <i data-lucide="${icon}" class="w-6 h-6 text-secondary mb-2"></i>
              <h3 class="font-bold mb-1">${label}</h3>
              <p class="text-xs text-white/40">${desc}</p>
            </div>`;
          }).join('')}
        </div>

        ${ch.hotel ? `<div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-2">Hotel Rating</label>
          <div class="flex gap-3 flex-wrap">
            ${['3-Star','4-Star','5-Star'].map(h => `
              <button onclick="pickHotelCategory('${h}')" class="px-5 py-2.5 rounded-xl border font-semibold text-sm transition-all ${s.hotelCategory===h?'border-secondary bg-secondary/10 text-secondary':'border-white/10 bg-white/5 text-white/60 hover:border-white/30'}">${h}</button>`).join('')}
          </div>
          <p class="text-xs text-white/30 mt-2">Sets which hotels appear in the Accommodation step.</p>
        </div>` : ''}

        ${ch.meal ? `<div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-2">Meal Plan</label>
          <p class="text-white/30 text-xs mb-3">Requires at least ${minAdults} adults to book.</p>
          ${mealBlocked ? `
          <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 text-sm text-amber-300 flex items-start gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
            <span>To avail a meal plan, you need at least ${minAdults} adult travelers. You currently have ${s.travelers.adults}.</span>
          </div>` : (mealDef ? `
          <div class="grid grid-cols-3 gap-4">
            ${(mealDef.price_tiers||[]).map(t => {
              const sel = s.meal && s.meal.tier === t.tier;
              return `
            <div onclick="pickMeal('${mealDef.sys_id}','${(mealDef.name||'').replace(/'/g,"\\'")}','${t.tier.replace(/'/g,"\\'")}',${t.price})" class="ticket-card p-5 text-center ${sel?'selected':''}">
              <h3 class="font-bold mb-2">${t.tier}</h3>
              <p class="text-secondary font-bold text-lg">${fmtPrice(+t.price)}</p>
              <p class="text-[10px] text-white/30 mt-1">per person</p>
            </div>`;
            }).join('')}
          </div>` : `<p class="text-white/20 text-xs">Loading meal plans...</p>`)}
        </div>` : ''}
      </div>`;
      break;

    // ── Flight ──
    case 'flight':
      if (state.flights.length === 0) {
        html = `<div class="space-y-6 animate-slide-up"><h2 class="text-2xl font-bold">Flight Preference</h2>
          <div class="glass-card p-8 text-center text-white/30"><i data-lucide="plane" class="w-10 h-10 mx-auto mb-3 opacity-30"></i><p>Loading flight fares...</p></div></div>`;
        break;
      }
      // Fare Type (Flexible/Fixed) is no longer surfaced to the traveler —
      // that distinction is decided by the team later. The first
      // admin-defined flight fare is used implicitly as the source of
      // Direct/Connecting reference pricing.
      const currentFare = state.flights[0];
      // "Both" is a synthetic third option (not admin-managed, no price)
      // meaning "either works for me" — added client-side only.
      const connOptions = [...(currentFare.type_prices||[]), { type: 'Both', price: null, synthetic: true }];
      const connIcon = t => t === 'Direct' ? 'move-right' : t === 'Connecting' ? 'route' : 'shuffle';
      html = `<div class="space-y-8 animate-slide-up">
        <h2 class="text-2xl font-bold">Flight Preference</h2>
        <div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-3">Connection Type</label>
          <div class="grid grid-cols-3 gap-4">
            ${connOptions.map(p => {
              const sel = s.connectionType === p.type;
              return `
            <div onclick="pick('connectionType','${p.type.replace(/'/g,"\\'")}')" class="ticket-card p-5 text-center relative ${sel?'selected':''}">
              ${sel?'<span class="absolute top-3 right-3 w-6 h-6 rounded-full bg-secondary flex items-center justify-center"><i data-lucide="check" class="w-3.5 h-3.5 text-primary"></i></span>':''}
              <i data-lucide="${connIcon(p.type)}" class="w-7 h-7 ${sel?'text-secondary':'text-white/40'} mx-auto mb-2"></i>
              <span class="block font-semibold ${sel?'text-white':'text-white/70'}">${p.type}</span>
            </div>`;
            }).join('')}
          </div>
        </div>
        <div class="bg-secondary/5 border border-secondary/20 rounded-xl p-4 text-sm text-white/60 flex items-start gap-2">
          <i data-lucide="info" class="w-4 h-4 text-secondary shrink-0 mt-0.5"></i>
          <span>Flight fare will be updated by our team once availability is confirmed — it isn't included in your estimate below yet.</span>
        </div>
        <div>
          <label class="text-sm text-white/60 block mb-2">Preferred Departure Date</label>
          <div class="relative">
            <i data-lucide="calendar-days" class="w-5 h-5 text-secondary absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none z-10"></i>
            <input type="date" id="flight-date" onchange="updFlightDate(this.value)"
              class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-4 text-white focus:border-secondary focus:outline-none"
              min="${new Date(Date.now()+15*86400000).toISOString().split('T')[0]}" value="${s.flightDate||''}">
          </div>
        </div>
      </div>`;
      break;

    // ── Duration ──
    case 'duration':
      html = `<div class="space-y-6 animate-slide-up">
        <h2 class="text-2xl font-bold">Duration & Stay Split</h2>
        <div class="glass-card p-6 space-y-8">
          <div>
            <div class="flex justify-between mb-3"><span class="text-sm font-bold text-white/40 uppercase tracking-wider">Total Duration</span><span id="duration-days-label" class="text-secondary font-bold">${s.duration} Days</span></div>
            <input type="range" id="duration-range" min="7" max="28" value="${s.duration}" oninput="updDuration(this.value)" class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-secondary">
            <div class="flex justify-between text-[10px] text-white/20 mt-1"><span>7 days</span><span>28 days</span></div>
            <div class="flex items-center gap-3 mt-4">
              <label class="text-xs text-white/40 shrink-0">Or enter exact days:</label>
              <input type="number" id="duration-custom-input" min="1" max="90" value="${s.duration}"
                onchange="updDurationCustom(this.value)"
                class="w-24 bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-center focus:border-secondary focus:outline-none">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-6">
            <div class="text-center"><p class="text-xs text-white/40 uppercase mb-3">Makkah</p>
              <div class="flex items-center justify-center gap-4">
                <button onclick="updSplit('makkah',-1)" class="w-9 h-9 rounded-full border border-white/10 hover:border-secondary hover:text-secondary transition-all">−</button>
                <span id="makkah-stay-count" class="text-3xl font-bold">${s.makkahStay}</span>
                <button onclick="updSplit('makkah',1)" class="w-9 h-9 rounded-full border border-white/10 hover:border-secondary hover:text-secondary transition-all">+</button>
              </div><p class="text-[10px] text-white/30 mt-2">nights</p>
            </div>
            <div class="text-center"><p class="text-xs text-white/40 uppercase mb-3">Madinah</p>
              <div class="flex items-center justify-center gap-4">
                <button onclick="updSplit('madinah',-1)" class="w-9 h-9 rounded-full border border-white/10 hover:border-secondary hover:text-secondary transition-all">−</button>
                <span id="madinah-stay-count" class="text-3xl font-bold">${s.madinahStay}</span>
                <button onclick="updSplit('madinah',1)" class="w-9 h-9 rounded-full border border-white/10 hover:border-secondary hover:text-secondary transition-all">+</button>
              </div><p class="text-[10px] text-white/30 mt-2">nights</p>
            </div>
          </div>
        </div>
      </div>`;
      break;

    // ── Accommodation ──
    case 'accommodation':
      if (!state.hotelsFetchAttempted && !state.hotelsLoading) {
        state.hotelsLoading = true;
        setTimeout(() => fetchHotels(state.selectedHotelCity), 0);
      }
      const selectedRoomIdsAcc = (s.accommodation || []).map(a => a.room_type_sys_id);
      const cityTabsAcc = ['Makkah','Madinah','Jeddah'];
      const cityTabsHtmlAcc = `<div class="flex gap-2 mb-2">${cityTabsAcc.map(c => `
          <button onclick="selectHotelCity('${c}')" class="px-4 py-2 rounded-xl text-sm font-medium border transition-all ${state.selectedHotelCity===c?'border-secondary bg-secondary/10 text-secondary':'border-white/10 bg-white/5 text-white/50 hover:border-white/30'}">${c}</button>
      `).join('')}</div>`;

      // Real-time capacity check — total booked room capacity vs. actual
      // travelers (adults + children; infants typically don't need their
      // own bed/seat). Purely informational, doesn't block selection.
      const travelersNeedingSpaceAcc = (s.travelers.adults||0) + (s.travelers.children||0);
      const bookedCapacityAcc = (s.accommodation||[]).reduce((sum,a)=>sum+(a.max_adults||0)+(a.max_children||0),0);
      const capacityWarningHtmlAcc = (s.accommodation||[]).length && bookedCapacityAcc < travelersNeedingSpaceAcc
        ? `<div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-sm text-red-300 flex items-start gap-2 mb-4">
             <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
             <span>You need more rooms — your team has ${travelersNeedingSpaceAcc} member${travelersNeedingSpaceAcc===1?'':'s'}, but the ${s.accommodation.length} room${s.accommodation.length===1?'':'s'} you've booked fit only ${bookedCapacityAcc}.</span>
           </div>`
        : '';

      if (state.hotelsLoading) {
        html = `<div class="space-y-6 animate-slide-up"><h2 class="text-2xl font-bold">Accommodation</h2>${cityTabsHtmlAcc}
          <div class="glass-card p-8 text-center text-white/30"><i data-lucide="hotel" class="w-10 h-10 mx-auto mb-3 opacity-30 animate-pulse"></i><p>Loading hotels...</p></div></div>`;
        break;
      }
      if (state.hotelsFetchFailed) {
        html = `<div class="space-y-6 animate-slide-up"><h2 class="text-2xl font-bold">Accommodation</h2>${cityTabsHtmlAcc}
          <div class="glass-card p-8 text-center">
            <i data-lucide="wifi-off" class="w-10 h-10 mx-auto mb-3 text-white/20"></i>
            <p class="text-white/40 text-sm mb-4">Couldn't load hotels right now. This is usually temporary.</p>
            <button onclick="retryFetchHotels()" class="inline-flex items-center gap-2 bg-secondary hover:bg-emerald text-primary font-semibold px-5 py-2.5 rounded-xl text-sm">
              <i data-lucide="refresh-cw" class="w-4 h-4"></i> Try Again
            </button>
          </div></div>`;
        break;
      }
      html = `<div class="space-y-6 animate-slide-up">
        <h2 class="text-2xl font-bold">Accommodation</h2>
        ${cityTabsHtmlAcc}
        ${capacityWarningHtmlAcc}
        <div class="flex items-center justify-between flex-wrap gap-2">
          <div class="flex items-center gap-2 text-white/50 text-sm"><i data-lucide="hotel" class="w-4 h-4 text-secondary"></i> Hotels in ${state.selectedHotelCity}</div>
          <button onclick="retryFetchHotels()" class="text-xs text-secondary hover:underline flex items-center gap-1"><i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Refresh</button>
        </div>
        ${!state.hotels.length ? `
        <div class="glass-card p-8 text-center text-white/30"><i data-lucide="hotel" class="w-8 h-8 mx-auto mb-2 opacity-30"></i><p class="text-sm">No hotels found in ${state.selectedHotelCity} right now.</p></div>
        ` : `
        <div class="space-y-4">${state.hotels.map(h => {
            const expanded = state.expandedHotelSysId === h.sys_id;
            const roomTypes = state.roomTypeCache[h.sys_id];
            const hasSelectedRoom = (s.accommodation || []).some(a => a.hotel_sys_id === h.sys_id);
            const thumb = (h.images && h.images[0]) ? `${window.BASE_URL}/${h.images[0]}` : (h.image_urls && h.image_urls[0]) || null;
            const dist = h.distance_info;
            return `
        <div class="ticket-card relative ${hasSelectedRoom?'selected':''}">
          <div onclick="toggleHotelExpand('${h.sys_id}')" class="cursor-pointer flex gap-4 p-5">
            <div class="w-24 h-24 rounded-xl bg-white/5 flex items-center justify-center overflow-hidden shrink-0">
              ${thumb ? `<img src="${thumb}" class="w-full h-full object-cover" alt="" referrerpolicy="no-referrer">` : `<i data-lucide="image" class="w-6 h-6 text-white/20"></i>`}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2">
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <p class="font-semibold text-sm ${hasSelectedRoom?'text-white':'text-white/80'}">${h.name||'Hotel'}</p>
                    ${dist ? `<span class="text-[9px] bg-secondary/10 text-secondary px-2 py-0.5 rounded-full flex items-center gap-1"><i data-lucide="map-pin" class="w-2.5 h-2.5"></i>${dist.value} ${dist.unit} from ${dist.landmark}</span>` : ''}
                  </div>
                  <p class="text-xs text-white/40 mt-0.5">${h.city||''}${h.star_rating?` • ${h.star_rating}★`:''}</p>
                </div>
                ${hasSelectedRoom?'<span class="w-5 h-5 rounded-full bg-secondary flex items-center justify-center shrink-0"><i data-lucide="check" class="w-3 h-3 text-primary"></i></span>':''}
              </div>
              ${h.description?`<p class="text-xs text-white/40 mt-1 line-clamp-2">${h.description}</p>`:''}
              ${h.address?`<p class="text-[10px] text-white/25 mt-1 truncate">${h.address}</p>`:''}
            </div>
            <i data-lucide="${expanded?'chevron-up':'chevron-down'}" class="w-4 h-4 text-white/30 shrink-0 mt-1"></i>
          </div>

          ${expanded ? `<div class="px-5 pb-5 pt-4 border-t border-white/10">
              ${!roomTypes ? `<p class="text-xs text-white/30 py-4 text-center">Loading room types...</p>` :
                roomTypes.length === 0 ? `<p class="text-xs text-white/20 py-4 text-center">No room types available for this hotel yet.</p>` :
                `<div class="space-y-3">${roomTypes.map(rt => {
                  const roomSel = selectedRoomIdsAcc.includes(rt.sys_id);
                  const rtExpanded = state.expandedRoomTypeSysId === rt.sys_id;
                  const boardTypes = state.globalBoardTypes;
                  const roomPrices = state.roomPricesCache[rt.sys_id];
                  const sizeLabel = (rt.size && rt.size.value) ? `${rt.size.value} ${({'sqr-m':'m²','sqr-cm':'cm²','sqr-ft':'ft²','sqr-in':'in²'})[rt.size.unit]||rt.size.unit}` : '';
                  return `
                <div class="rounded-xl border ${roomSel?'border-secondary bg-secondary/10':'border-white/10 bg-white/5'}">
                  <button onclick="event.stopPropagation(); toggleRoomTypeExpand('${rt.sys_id}')" class="w-full text-left p-3">
                    <div class="flex items-start justify-between gap-2">
                      <span class="font-semibold text-xs ${roomSel?'text-white':'text-white/80'}">${rt.name}</span>
                      ${roomSel?'<i data-lucide="check-circle-2" class="w-4 h-4 text-secondary shrink-0"></i>':''}
                    </div>
                    <p class="text-[10px] text-white/40 mt-1">Max ${(rt.person_capacity&&rt.person_capacity.adults)||2} Adults${(rt.person_capacity&&rt.person_capacity.children)?' + '+rt.person_capacity.children+' Children':''}${sizeLabel?' · '+sizeLabel:''}</p>
                    ${rt.description?`<p class="text-[10px] text-white/30 mt-1">${rt.description}</p>`:''}
                    ${(rt.bed_config&&rt.bed_config.length) ? `<div class="flex flex-wrap gap-1 mt-1.5">${rt.bed_config.map(b=>`<span class="text-[9px] bg-dark border border-white/10 px-2 py-0.5 rounded-full text-white/50">${b.bed_type} ×${b.quantity}</span>`).join('')}</div>` : ''}
                    ${(rt.amenities&&rt.amenities.length) ? `<div class="flex flex-wrap gap-1 mt-1.5">${rt.amenities.map(a=>`<span class="text-[9px] bg-dark border border-white/10 px-2 py-0.5 rounded-full text-white/50">${a}</span>`).join('')}</div>` : ''}
                    ${(rt.view_info&&Number(rt.view_info.enabled)) ? `<p class="text-[10px] text-secondary mt-1.5 flex items-center gap-1"><i data-lucide="eye" class="w-3 h-3"></i> ${rt.view_info.description||'View available'}</p>` : ''}
                    ${(rt.add_on&&Number(rt.add_on.bed)) ? `<p class="text-[10px] text-white/40 mt-1">Extra bed available — SR ${rt.add_on.charge}</p>` : ''}
                  </button>
                  ${rtExpanded ? `<div class="px-3 pb-3">
                    ${(!boardTypes || roomPrices===undefined) ? `<p class="text-[10px] text-white/30 py-2 text-center">Loading options...</p>` :
                      (() => {
                        const priced = boardTypes.filter(bt => getBoardPriceFor(rt.sys_id, bt.sys_id));
                        if (!priced.length) return `<p class="text-[10px] text-white/20 py-2 text-center">No prices set for this room yet.</p>`;
                        return `<div class="grid grid-cols-2 gap-2">${priced.map(bt => {
                          const price = getBoardPriceFor(rt.sys_id, bt.sys_id);
                          const boardSel = (s.accommodation||[]).some(a=>a.board_type_sys_id===bt.sys_id && a.room_type_sys_id===rt.sys_id);
                          return `
                          <button onclick="event.stopPropagation(); selectRoomType('${h.sys_id}','${(h.name||'').replace(/'/g,"\\'")}','${rt.sys_id}','${(rt.name||'').replace(/'/g,"\\'")}','${bt.sys_id}','${(bt.name||'').replace(/'/g,"\\'")}',${(rt.person_capacity&&rt.person_capacity.adults)||2},${(rt.person_capacity&&rt.person_capacity.children)||0})"
                            class="text-left rounded-lg border p-2.5 transition-all ${boardSel?'border-secondary bg-secondary/20':'border-white/10 bg-dark hover:border-white/30'}">
                            <span class="text-[11px] font-medium block ${boardSel?'text-white':'text-white/70'}">${bt.name}</span>
                            <span class="text-secondary font-bold text-xs">SR ${parseFloat(price.price).toFixed(2)}</span>
                          </button>`;}).join('')}</div>`;
                      })()}
                  </div>` : ''}
                </div>`;}).join('')}</div>`}
          </div>` : ''}
        </div>`;}).join('')}</div>`}
        <div class="glass-card p-4 flex items-start gap-3 bg-secondary/5 border-secondary/20">
          <i data-lucide="info" class="w-4 h-4 text-secondary shrink-0 mt-0.5"></i>
          <p class="text-xs text-white/60">Tap a hotel, then a room type, then a service category to add it to your journey.</p>
        </div>
      </div>`;
      break;

    // ── Transport (multi-leg — e.g. Airport→Makkah by GMC, then Makkah→Madinah by Bus) ──
    case 'transport':
      const vehicleIcons = {Car:'car',HiAce:'truck',Coaster:'bus',Bus:'bus',Minibus:'bus','GMC/JMC':'car',H1:'car',Staria:'car',Train:'train-front'};
      const routeLabel = r => [r.origin, ...(r.destinations||[])].join(' → ');
      if (state.transportRoutes.length === 0) {
        html = `<div class="space-y-6 animate-slide-up"><h2 class="text-2xl font-bold">Ground Transportation</h2>
          <div class="glass-card p-8 text-center text-white/30"><i data-lucide="map" class="w-10 h-10 mx-auto mb-3 opacity-30"></i><p>Transport routes loading...</p></div></div>`;
        break;
      }
      const legs = s.transport || [];
      // Route ids the traveler said "yes, I need transport" for while
      // picking Ziarahs, that haven't been turned into an actual leg yet.
      const ziarahRouteIds = new Set((s.ziarah||[]).filter(z => z.wantsTransport && z.routeId).map(z => z.routeId));
      const unresolvedZiarahRouteId = [...ziarahRouteIds].find(rid => !legs.some(l => l.route_sys_id === rid));
      // Auto-open the vehicle picker for that route the first time this
      // step is rendered after it became relevant — only once, so closing
      // it manually (picking a different route, or the vehicle itself)
      // doesn't keep forcing it back open on every re-render.
      if (unresolvedZiarahRouteId && !state.transportPickerRouteId && !state.ziarahAutoOpenedFor.has(unresolvedZiarahRouteId)) {
        state.transportPickerRouteId = unresolvedZiarahRouteId;
        state.ziarahAutoOpenedFor.add(unresolvedZiarahRouteId);
      }
      const activeRouteId = state.transportPickerRouteId || null;
      const fromZiarah = ziarahRouteIds.has(activeRouteId);
      html = `<div class="space-y-8 animate-slide-up">
        <h2 class="text-2xl font-bold">Ground Transportation</h2>
        ${fromZiarah ? `<div class="bg-secondary/5 border border-secondary/20 rounded-xl p-4 text-sm text-secondary flex items-start gap-2">
          <i data-lucide="landmark" class="w-4 h-4 shrink-0 mt-0.5"></i>
          <span>This route was pre-selected from your Ziarah choice — just pick a vehicle below to add it.</span>
        </div>` : ''}

        ${legs.length ? `
        <div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-2">Your Transport Legs</label>
          <div class="space-y-2">${legs.map((leg,i) => `
            <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-secondary/30 bg-secondary/5">
              <span class="text-sm flex items-center gap-2">
                <i data-lucide="${vehicleIcons[leg.vehicle_type]||'car'}" class="w-4 h-4 text-secondary"></i>
                <strong class="text-white">${leg.route_name}</strong>
                <span class="text-white/40">— ${leg.vehicle_type}</span>
              </span>
              <span class="flex items-center gap-3">
                <span class="text-secondary text-xs font-bold">${fmtPrice(leg.price)}</span>
                <button onclick="removeTransportLeg(${i})" class="text-white/30 hover:text-red-400"><i data-lucide="x" class="w-4 h-4"></i></button>
              </span>
            </div>`).join('')}</div>
        </div>` : `
        <div class="glass-card p-4 text-sm text-white/50 flex items-start gap-2">
          <i data-lucide="info" class="w-4 h-4 text-secondary shrink-0 mt-0.5"></i>
          <span>You can add multiple legs — e.g. Airport → Makkah by GMC, then Makkah → Madinah by Bus.</span>
        </div>`}

        <div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-2">${legs.length ? 'Add Another Leg' : 'Select a Route'}</label>
          <div class="grid gap-2">
            ${state.transportRoutes.map(r => {
              const alreadyAdded = legs.some(l => l.route_sys_id === r.sys_id);
              const isPicking = activeRouteId === r.sys_id;
              const isFromZiarah = ziarahRouteIds.has(r.sys_id);
              return `
              <div onclick="pickTransportRoute('${r.sys_id}')"
                   class="flex items-center justify-between px-4 py-3 rounded-xl border cursor-pointer transition-all
                          ${isPicking?'border-secondary bg-secondary/10':isFromZiarah?'border-secondary/40 bg-secondary/5':'border-white/10 bg-white/5 hover:border-white/30'} ${alreadyAdded?'opacity-50':''}">
                <div class="flex items-center gap-3">
                  <i data-lucide="map-pin" class="w-4 h-4 ${isPicking||isFromZiarah?'text-secondary':'text-white/30'}"></i>
                  <span class="font-semibold text-sm">${routeLabel(r)}${alreadyAdded?' <span class="text-[10px] text-secondary ml-1">(added)</span>':''}</span>
                  ${isFromZiarah && !alreadyAdded?'<span class="text-[9px] bg-secondary/15 text-secondary px-2 py-0.5 rounded-full flex items-center gap-1"><i data-lucide="landmark" class="w-2.5 h-2.5"></i>From Ziarah</span>':''}
                </div>
                ${isPicking?'<i data-lucide="chevron-down" class="w-4 h-4 text-secondary"></i>':''}
              </div>`;}).join('')}
          </div>
        </div>

        ${activeRouteId ? `
        <div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-2">Select Vehicle for this Leg</label>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            ${(() => {
              const route = state.transportRoutes.find(r => r.sys_id === activeRouteId);
              const vinfo = (route && route.vehicle_options) ? route.vehicle_options : [];
              if (!vinfo.length) return `<p class="col-span-full text-center text-white/30 text-sm py-4">No vehicles priced for this route yet.</p>`;
              return vinfo.map(v => {
                const vt = state.vehicleTypesById[v.vehicle_type_sys_id];
                const vName = vt ? vt.name : v.vehicle_type_sys_id;
                const vIcon = vt ? (vt.icon || vehicleIcons[vName] || 'car') : 'car';
                const priceSar = v.price || 0;
                const available = priceSar > 0;
                return `<div onclick="${available?`addTransportLeg('${activeRouteId}','${v.vehicle_type_sys_id}','${vName.replace(/'/g,"\\'")}',${priceSar})`:'void(0)'}"
                     class="ticket-card p-4 text-center ${available?'cursor-pointer hover:border-secondary/50':'opacity-30 cursor-not-allowed'}">
                  <i data-lucide="${vIcon}" class="w-6 h-6 mx-auto mb-2 text-white/50"></i>
                  <p class="text-xs font-bold mb-1">${vName}</p>
                  <p class="text-xs font-bold text-white/40">${available?fmtPrice(priceSar):'N/A'}</p>
                </div>`;
              }).join('');
            })()}
          </div>
        </div>` : ''}
      </div>`;
      break;

    // ── Ziarah — selecting a tour asks: need transport? need Moyallem? ──
    case 'ziarah':
      if (state.ziarahList.length === 0) {
        html = `<div class="space-y-6 animate-slide-up"><h2 class="text-2xl font-bold">Ziarah</h2>
           <div class="glass-card p-8 text-center text-white/30"><i data-lucide="landmark" class="w-10 h-10 mx-auto mb-3 opacity-30"></i><p>Loading Ziarah tours...</p></div></div>`;
        break;
      }
      const zSel = s.ziarah || [];
      html = `<div class="space-y-6 animate-slide-up">
        <h2 class="text-2xl font-bold">Ziarah — Guided Site Visits</h2>
        <p class="text-white/40 text-sm">Select the guided site visits you'd like to include.</p>
        <div class="space-y-4">
        ${state.ziarahList.map(z => {
            const picked = zSel.find(x => x.id === z.id);
            const isOn = !!picked;
            return `
        <div class="ticket-card relative ${isOn?'selected':''}">
          <div onclick="toggleZiarah(${z.id},'${z.name.replace(/'/g,"\\'")}',${z.price})" class="cursor-pointer flex items-start justify-between gap-3 p-5">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <h3 class="font-bold ${isOn?'text-white':'text-white/80'}">${z.name}</h3>
                ${isOn?'<span class="w-5 h-5 rounded-full bg-secondary flex items-center justify-center shrink-0"><i data-lucide="check" class="w-3 h-3 text-primary"></i></span>':''}
              </div>
              <p class="text-xs text-white/40 mt-1">${z.description||''}</p>
              <p class="text-[11px] text-white/30 mt-1">${z.possible_duration||''} · <span class="text-secondary font-semibold">${fmtPrice(+z.price)}</span></p>
            </div>
          </div>

          ${isOn ? `<div class="px-5 pb-5 pt-4 border-t border-white/10 space-y-4">
            <div>
              <p class="text-xs text-white/60 font-medium mb-2">Need transport for this Ziarah?</p>
              <div class="grid grid-cols-2 gap-2">
                <button onclick="event.stopPropagation(); setZiarahWantsTransport(${z.id}, true)" class="py-2 rounded-lg border text-sm font-medium transition-all ${picked.wantsTransport===true?'border-secondary bg-secondary/10 text-secondary':'border-white/10 bg-white/5 text-white/60 hover:border-white/30'}">Yes</button>
                <button onclick="event.stopPropagation(); setZiarahWantsTransport(${z.id}, false)" class="py-2 rounded-lg border text-sm font-medium transition-all ${picked.wantsTransport===false?'border-secondary bg-secondary/10 text-secondary':'border-white/10 bg-white/5 text-white/60 hover:border-white/30'}">No</button>
              </div>
              ${picked.wantsTransport ? `
              <div class="mt-3">
                <p class="text-[11px] text-white/40 mb-2">Which route would you like to start from?</p>
                <div class="grid gap-1.5">
                ${(z.routes||[]).map(r => `
                  <button onclick="event.stopPropagation(); pickZiarahRoute(${z.id},'${r.sys_id}','${r.label.replace(/'/g,"\\'")}')"
                    class="text-left px-3 py-2 rounded-lg border text-xs transition-all ${picked.routeId===r.sys_id?'border-secondary bg-secondary/10 text-secondary':'border-white/10 bg-white/5 text-white/60 hover:border-white/30'}">
                    <i data-lucide="map-pin" class="w-3 h-3 inline mr-1"></i> ${r.label}
                  </button>`).join('') || '<p class="text-[11px] text-white/20">No routes linked to this Ziarah yet.</p>'}
                </div>
              </div>` : ''}
            </div>

            ${z.moyallem_enabled ? `<div>
              <p class="text-xs text-white/60 font-medium mb-2">Need a Moyallem for this Ziarah?</p>
              <div class="grid grid-cols-2 gap-2">
                <button onclick="event.stopPropagation(); setZiarahWantsMoyallem(${z.id}, true)" class="py-2 rounded-lg border text-sm font-medium transition-all ${picked.wantsMoyallem===true?'border-secondary bg-secondary/10 text-secondary':'border-white/10 bg-white/5 text-white/60 hover:border-white/30'}">Yes</button>
                <button onclick="event.stopPropagation(); setZiarahWantsMoyallem(${z.id}, false)" class="py-2 rounded-lg border text-sm font-medium transition-all ${picked.wantsMoyallem===false?'border-secondary bg-secondary/10 text-secondary':'border-white/10 bg-white/5 text-white/60 hover:border-white/30'}">No</button>
              </div>
              ${picked.wantsMoyallem ? `
              <div class="mt-3 grid grid-cols-3 gap-2">
              ${(state.moyallemServices[0]?.category_prices||[]).map(cp => `
                <button onclick="event.stopPropagation(); pickZiarahMoyallemCategory(${z.id},'${cp.category.replace(/'/g,"\\'")}',${cp.price})"
                  class="py-2 rounded-lg border text-center transition-all ${picked.moyallemCategory===cp.category?'border-secondary bg-secondary/10':'border-white/10 bg-white/5 hover:border-white/30'}">
                  <span class="block text-xs font-semibold ${picked.moyallemCategory===cp.category?'text-secondary':'text-white/70'}">${cp.category}</span>
                  <span class="block text-[10px] mt-0.5 ${picked.moyallemCategory===cp.category?'text-secondary':'text-white/30'}">${fmtPrice(+cp.price)}</span>
                </button>`).join('') || '<p class="text-[11px] text-white/20 col-span-3">No Moyallem categories configured yet.</p>'}
              </div>` : ''}
            </div>` : ''}
          </div>` : ''}
        </div>`;
          }).join('')}
        </div>
        <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
          <span class="text-sm text-white/40">Ziarah Total</span>
          <span class="font-bold text-secondary">${fmtPrice(zSel.reduce((a,z)=>a+(z.price||0)+(z.moyallemPriceSar||0),0))}</span>
        </div>
      </div>`;
      break;

    // ── Moyallem ──
    case 'moyallem':
      html = `<div class="space-y-6 animate-slide-up">
        <h2 class="text-2xl font-bold">Moyallem / Spiritual Guide</h2>
        <p class="text-white/40 text-sm">Select one or more services, then choose a category (General/Expert/VIP) for each.</p>
        ${state.moyallemServices.length === 0
          ? `<div class="glass-card p-8 text-center text-white/30"><i data-lucide="users" class="w-10 h-10 mx-auto mb-3 opacity-30"></i><p>Loading services...</p></div>`
          : `<div class="grid md:grid-cols-2 gap-4">
            ${state.moyallemServices.map(m => {
              const picked = s.moyallem.find(x => x.sys_id === m.sys_id);
              const selected = !!picked;
              return `<div class="ticket-card p-5 ${selected?'selected':''}">
                <div onclick="toggleMoyallemService('${m.sys_id}','${m.name.replace(/'/g,"\\'")}')" class="flex items-start gap-3 cursor-pointer">
                  <div class="w-9 h-9 bg-secondary/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="${m.icon||'star'}" class="w-4 h-4 text-secondary"></i></div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                      <h3 class="font-bold text-sm leading-tight">${m.name}</h3>
                      <div class="shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all ${selected?'border-secondary bg-secondary':'border-white/20'}">
                        ${selected?'<i data-lucide="check" class="w-3 h-3 text-primary"></i>':''}</div>
                    </div>
                    <p class="text-white/40 text-xs mt-1 leading-relaxed">${m.description||''}</p>
                  </div>
                </div>
                ${selected ? `<div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-white/10">
                  ${(m.category_prices||[]).map(cp => {
                    const catSelected = picked.category === cp.category;
                    return `<button onclick="event.stopPropagation(); pickMoyallemCategory('${m.sys_id}','${m.name.replace(/'/g,"\\'")}','${cp.category.replace(/'/g,"\\'")}',${cp.price})"
                        class="px-2 py-2 rounded-lg border text-center transition-all ${catSelected?'border-secondary bg-secondary/10':'border-white/10 bg-white/5 hover:border-white/30'}">
                      <span class="block text-[11px] font-semibold ${catSelected?'text-secondary':'text-white/70'}">${cp.category}</span>
                      <span class="block text-[10px] mt-0.5 ${catSelected?'text-secondary':'text-white/30'}">${fmtPrice(parseFloat(cp.price))}</span>
                    </button>`;
                  }).join('')}
                </div>` : ''}
              </div>`;
            }).join('')}
          </div>
          <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
            <span class="text-sm text-white/40">Moyallem Total</span>
            <span class="font-bold text-secondary">${fmtPrice(s.moyallem.reduce((a,m)=>a+(m.price||0),0))}</span>
          </div>`}
        <button onclick="skipMoyallem()" class="text-xs text-white/30 hover:text-white/60 transition-colors mx-auto block">Skip — No Moyallem Needed</button>
      </div>`;
      break;

    // ── Review ──
    case 'review':
      const moy = s.moyallem.length ? s.moyallem.map(m=>m.name).join(', ') : 'None';
      html = `<div class="space-y-6 animate-slide-up">
        <h2 class="text-2xl font-bold">Review Your Journey</h2>
        <div class="glass-card p-5">
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-3">Contact Details</label>
          <div class="grid md:grid-cols-2 gap-4">
            ${['phone','email'].map(f => `<div>
              <label class="text-xs text-white/40 block mb-1.5">${f==='phone'?'WhatsApp Number *':'Email'}</label>
              <input type="${f==='email'?'email':'tel'}" id="r-${f}" value="${s.travelers[f]||''}"
                placeholder="${f==='phone'?'+880 1X-XXXXXXXX':'your@email.com'}"
                onchange="updTravelerField('${f}', this.value)"
                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
            </div>`).join('')}
          </div>
        </div>
        <div class="glass-card overflow-hidden">
          <div class="bg-secondary/10 p-5 border-b border-white/10">
            <p class="text-secondary font-bold uppercase tracking-widest text-xs mb-1">Custom Package</p>
            <h3 class="text-xl font-bold">${s.packageType||'—'} · ${s.duration} Days</h3>
          </div>
          <div class="p-6 grid md:grid-cols-2 gap-5">
            ${[
              ['Traveler',`${s.travelers.name||'—'} · ${s.travelers.adults}A${s.travelers.children?' +'+s.travelers.children+'C':''}${s.travelers.infants?' +'+s.travelers.infants+'I':''}`,  'users'],
              ...(stepIsVisible('visa') ? [['Visa', s.visa||'—','file-text']] : []),
              ...(stepIsVisible('flight') ? [['Flight', s.connectionType ? `${s.connectionType}${s.flightDate?' • '+s.flightDate:''}` : '—','plane']] : []),
              ...(stepIsVisible('accommodation') ? [['Hotels', (s.accommodation||[]).length ? s.accommodation.map(a=>`${a.hotel_name} (${a.room_name})`).join(', ') : `${s.hotelCategory} — none selected yet`,'hotel']] : [['Stay', `${s.makkahStay}n Makkah + ${s.madinahStay}n Madinah`,'moon']]),
              ...(stepIsVisible('transport') ? [['Transport', (s.transport||[]).length ? s.transport.map(l=>`${l.route_name} (${l.vehicle_type})`).join(', ') : 'None selected','car']] : []),
              ...(stepIsVisible('ziarah') ? [['Ziarah', (s.ziarah||[]).length ? s.ziarah.map(z=>z.name).join(', ') : 'None','landmark']] : []),
              ...(stepIsVisible('moyallem') ? [['Moyallem', moy,'user-check']] : []),
              ...(stepIsVisible('meal') ? [['Meal', s.meal ? s.meal.tier : 'None','utensils']] : []),
            ].map(([l,v,i])=>`<div class="flex items-start gap-3">
              <i data-lucide="${i}" class="w-4 h-4 text-white/20 mt-0.5 shrink-0"></i>
              <div><p class="text-[10px] text-white/30 uppercase tracking-wider">${l}</p>
              <p class="text-sm font-medium">${v}</p></div>
            </div>`).join('')}
          </div>
          <div class="p-5 border-t border-white/10 bg-white/5">
            <div class="flex justify-between items-center">
              <span class="text-white/40 text-sm">Estimated Total (${state.currency})</span>
              <span class="text-2xl font-bold text-secondary">${fmtPrice(calcTotal())}</span>
            </div>
          </div>
        </div>
        <div class="p-4 flex items-start gap-3 bg-teal/5 border border-teal/20 rounded-xl">
          <i data-lucide="info" class="w-5 h-5 text-teal shrink-0 mt-0.5"></i>
          <p class="text-xs text-teal/80">By submitting, our consultants will contact you within 2 hours with a confirmed quote.</p>
        </div>
        <div id="submit-msg" class="hidden px-4 py-3 rounded-xl text-sm font-medium"></div>
      </div>`;
      break;
  }

  // The slide-up entry animation should only play when the traveler
  // actually moves to a different step. In-step updates (selecting a card,
  // an input losing focus, a toggle) call this same renderer to rebuild
  // the step's HTML, but replaying the animation on every one of those
  // reads as the page "refreshing" — so it's stripped out here unless
  // this render was triggered by real navigation.
  if (!isNavigation) {
    html = html.replace(/\s*animate-slide-up/g, '');
  }

  container.innerHTML = html;
  lucide.createIcons();

  // Update nav buttons
  document.getElementById('prev-btn').disabled = state.currentStep === 1;
  const nextBtn = document.getElementById('next-btn');
  if (stepKey === 'review') {
    nextBtn.innerHTML = '<i data-lucide="send" class="w-5 h-5"></i> Submit Journey';
  } else {
    nextBtn.innerHTML = 'Next <i data-lucide="arrow-right" class="w-5 h-5"></i>';
  }
  renderValidationState();
  lucide.createIcons();
}

// ──────────────────────────────────────────
// VALIDATION — per-step required fields
// Phone is REQUIRED here (Full Package Builder) — unlike the homepage
// mini-builder, where it's intentionally optional. This is a deliberate
// difference between the two flows, not an inconsistency to "fix" later.
// Keyed by step `key`, not numeric position — positions shift as
// Package Mode (Land/Flight/Both) hides/shows steps.
// ──────────────────────────────────────────
function stepValid(key) {
  const s = state.sel;
  switch (key) {
    case 'travelers':     return !!(s.travelers.name && s.travelers.name.trim().length >= 2 && s.packageType);
    case 'choices':       return true; // optional toggles, never blocks
    case 'flight':        return !!s.connectionType;
    case 'accommodation': return true; // placeholder step, never blocks
    case 'duration':      return s.duration >= 7; // slider always valid, kept for completeness
    case 'transport':     return true; // optional — legs can be added freely, never blocks navigation
    case 'ziarah':        return true; // optional
    case 'moyallem':      return true; // optional (has its own Skip button)
    case 'review':        return !!(s.travelers.phone && s.travelers.phone.trim().length >= 6); // WhatsApp collected here now
    default: return true;
  }
}

function stepHint(key) {
  switch (key) {
    case 'travelers': return 'Enter your full name and select a service level to continue.';
    case 'flight':    return 'Select a connection type to continue.';
    case 'review':    return 'Enter your WhatsApp number to submit.';
    default: return '';
  }
}

function renderValidationState() {
  const nextBtn = document.getElementById('next-btn');
  const ok = stepValid(getCurrentStepKey());
  nextBtn.disabled = !ok;
  nextBtn.classList.toggle('opacity-40', !ok);
  nextBtn.classList.toggle('cursor-not-allowed', !ok);

  const banner = document.getElementById('step-hint-banner');
  const text   = document.getElementById('step-hint-text');
  banner.classList.toggle('hidden', ok);
  text.textContent = ok ? '' : stepHint(getCurrentStepKey());
  if (!ok) lucide.createIcons();
}

// ──────────────────────────────────────────
// ACTIONS
// ──────────────────────────────────────────
// ──────────────────────────────────────────
// SMOOTH (in-place) UPDATES — avoid a full renderStepContent() rebuild for
// high-frequency taps (+/- counters, slider drags). A full rebuild tears
// down and recreates every node in the step (including re-running
// lucide.createIcons()), which reads as a "flash"/refresh on every click.
// These instead patch just the specific DOM node's text, persist to
// localStorage, and refresh the sidebar summary — the step layout itself
// doesn't change shape from these actions, so nothing else needs to move.
// ──────────────────────────────────────────
function persistSel() {
  localStorage.setItem('umrah_journey_v2', JSON.stringify(state.sel));
}

window.updTraveler = (k, d) => {
  state.sel.travelers[k] = Math.max(k==='adults'?1:0, state.sel.travelers[k]+d);
  persistSel();
  const el = document.getElementById(`traveler-count-${k}`);
  if (el) el.textContent = state.sel.travelers[k];
  renderSummary();
};

// Name/Phone/Email fields — the input already shows what was typed, so
// there's nothing to redraw in the step itself. Just persist + refresh the
// sidebar summary and the Next-button validity (name/phone are required).
window.updTravelerField = (f, val) => {
  state.sel.travelers[f] = val;
  persistSel();
  renderSummary();
  renderValidationState();
};
window.pick = (k, v) => { state.sel[k] = v; save(); };

// Departure date — just persist + refresh summary, same as text inputs.
window.updFlightDate = (val) => {
  state.sel.flightDate = val;
  persistSel();
  renderSummary();
};

window.updDuration = val => {
  state.sel.duration = parseInt(val);
  state.sel.makkahStay  = Math.ceil(state.sel.duration * 0.6);
  state.sel.madinahStay = state.sel.duration - state.sel.makkahStay;
  persistSel();
  patchDurationDOM();
  renderSummary();
};
// Direct number entry — same effect as the slider, just a wider range
// (1-90 days) for trips longer than the slider's 7-28 day sweep.
window.updDurationCustom = val => {
  const days = Math.max(1, Math.min(90, parseInt(val) || 1));
  state.sel.duration = days;
  state.sel.makkahStay  = Math.ceil(days * 0.6);
  state.sel.madinahStay = days - state.sel.makkahStay;
  persistSel();
  patchDurationDOM();
  renderSummary();
};
window.updSplit = (type, d) => {
  if (type === 'makkah') {
    state.sel.makkahStay  = Math.max(1, Math.min(state.sel.duration-1, state.sel.makkahStay+d));
    state.sel.madinahStay = state.sel.duration - state.sel.makkahStay;
  } else {
    state.sel.madinahStay = Math.max(1, Math.min(state.sel.duration-1, state.sel.madinahStay+d));
    state.sel.makkahStay  = state.sel.duration - state.sel.madinahStay;
  }
  persistSel();
  patchDurationDOM();
  renderSummary();
};
function patchDurationDOM() {
  const s = state.sel;
  const durLabel = document.getElementById('duration-days-label');
  if (durLabel) durLabel.textContent = `${s.duration} Days`;
  const durRange = document.getElementById('duration-range');
  if (durRange) durRange.value = Math.min(28, s.duration);
  const durCustom = document.getElementById('duration-custom-input');
  if (durCustom) durCustom.value = s.duration;
  const mkStay = document.getElementById('makkah-stay-count');
  if (mkStay) mkStay.textContent = s.makkahStay;
  const mdStay = document.getElementById('madinah-stay-count');
  if (mdStay) mdStay.textContent = s.madinahStay;
}

// Clicking a route just opens its vehicle picker — clicking again on the
// same route (or picking a vehicle) closes/resolves it. Toggling the
// picker never touches the already-added legs list.
window.pickTransportRoute = (routeId) => {
  state.transportPickerRouteId = state.transportPickerRouteId === routeId ? null : routeId;
  renderStepContent();
};
window.addTransportLeg = (routeSysId, vehicleTypeSysId, vehicleName, priceSar) => {
  const route = state.transportRoutes.find(r => r.sys_id === routeSysId);
  if (!route) return;
  const routeName = [route.origin, ...(route.destinations||[])].join(' → ');
  state.sel.transport.push({
    route_sys_id: routeSysId, route_name: routeName,
    vehicle_type: vehicleName, vehicle_type_sys_id: vehicleTypeSysId, price: parseFloat(priceSar) || 0
  });
  state.transportPickerRouteId = null;
  save();
};
window.removeTransportLeg = (idx) => {
  state.sel.transport.splice(idx, 1);
  save();
};

// Clicking a service card toggles it on/off. Turning it on doesn't pick a
// category yet — the user must tap one of the General/Expert/VIP buttons
// that appear once selected (pickMoyallemCategory below).
window.toggleMoyallemService = (sysId, name) => {
  const idx = state.sel.moyallem.findIndex(m => m.sys_id === sysId);
  if (idx >= 0) state.sel.moyallem.splice(idx, 1);
  else state.sel.moyallem.push({ sys_id: sysId, name, category: null, price: 0 });
  save();
};
window.pickMoyallemCategory = (sysId, name, category, priceSar) => {
  const idx = state.sel.moyallem.findIndex(m => m.sys_id === sysId);
  const entry = { sys_id: sysId, name, category, price: parseFloat(priceSar) || 0 };
  if (idx >= 0) state.sel.moyallem[idx] = entry;
  else state.sel.moyallem.push(entry);
  save();
};
window.skipMoyallem = () => {
  state.sel.moyallem = [];
  save();
  goNext();
};

// "Your Choice" toggle — flips a module on/off; visa gets its auto-select
// resolved right here so it works whether visa types finished loading
// before or after the traveler flips the toggle. Turning "Meal" ON while
// under the minimum adult threshold shows a blocking modal instead of
// silently enabling it (matches index.php's mini-builder).
window.toggleChoice = (key) => {
  const turningOn = !state.sel.choices[key];
  if (key === 'meal' && turningOn) {
    const minAdults = state.meals[0]?.min_adults_required || 10;
    if (state.sel.travelers.adults < minAdults) {
      openMealBlockedModal(minAdults);
      return;
    }
  }
  state.sel.choices[key] = turningOn;
  if (key === 'visa' && turningOn) ensureVisaAutoSelected();
  save();
};

function openMealBlockedModal(minAdults) {
  document.getElementById('meal-blocked-modal-text').textContent =
    `To avail this, you need at least ${minAdults} adult travelers.`;
  const overlay = document.getElementById('meal-blocked-modal-overlay');
  overlay.classList.remove('hidden');
  overlay.classList.add('flex');
  lucide.createIcons();
}
window.closeMealBlockedModal = () => {
  const overlay = document.getElementById('meal-blocked-modal-overlay');
  overlay.classList.add('hidden');
  overlay.classList.remove('flex');
};

// Changing the star-rating preference means the previous hotel list no
// longer applies — force a fresh fetch (and clear any prior failure state
// so the new preference gets its own attempt).
window.pickHotelCategory = (h) => {
  state.sel.hotelCategory = h;
  state.hotels = [];
  state.hotelsFetchFailed = false;
  state.hotelsFetchAttempted = false;
  state.expandedHotelSysId = null;
  save();
};

window.selectHotelCity = (city) => {
  if (state.selectedHotelCity === city) return;
  state.selectedHotelCity = city;
  state.hotels = [];
  state.hotelsFetchAttempted = false;
  state.hotelsFetchFailed = false;
  state.expandedHotelSysId = null;
  state.expandedRoomTypeSysId = null;
  renderStepContent();
};

window.retryFetchHotels = () => {
  state.hotels = [];
  state.hotelsFetchFailed = false;
  state.hotelsLoading = true;
  renderStepContent();
  setTimeout(() => fetchHotels(state.selectedHotelCity), 0);
};

window.toggleHotelExpand = (hotelSysId) => {
  state.expandedHotelSysId = state.expandedHotelSysId === hotelSysId ? null : hotelSysId;
  state.expandedRoomTypeSysId = null; // collapse any open room type when switching hotels
  if (state.expandedHotelSysId && !state.roomTypeCache[hotelSysId]) {
    fetchRoomTypes(hotelSysId); // triggers its own re-render once loaded
  }
  renderStepContent();
};

window.toggleRoomTypeExpand = (roomTypeSysId) => {
  state.expandedRoomTypeSysId = state.expandedRoomTypeSysId === roomTypeSysId ? null : roomTypeSysId;
  if (state.expandedRoomTypeSysId) {
    if (state.globalBoardTypes === undefined) fetchGlobalBoardTypes(); // triggers its own re-render once loaded
    if (state.roomPricesCache[roomTypeSysId] === undefined) fetchRoomPrices(roomTypeSysId); // triggers its own re-render once loaded
  }
  renderStepContent();
};

window.selectRoomType = (hotelSysId, hotelName, roomTypeSysId, roomName, boardTypeSysId, boardName, maxAdults, maxChildren) => {
  const idx = state.sel.accommodation.findIndex(a => a.room_type_sys_id === roomTypeSysId && a.board_type_sys_id === boardTypeSysId);
  if (idx >= 0) {
    state.sel.accommodation.splice(idx, 1); // tap the same one again to deselect
  } else {
    // Only one board type per room type — picking a new one replaces
    // whatever was previously selected for this same room.
    state.sel.accommodation = state.sel.accommodation.filter(a => a.room_type_sys_id !== roomTypeSysId);
    const priceEntry = getBoardPriceFor(roomTypeSysId, boardTypeSysId);
    state.sel.accommodation.push({
      hotel_sys_id: hotelSysId, hotel_name: hotelName,
      room_type_sys_id: roomTypeSysId, room_name: roomName,
      board_type_sys_id: boardTypeSysId, board_name: boardName,
      price: priceEntry ? (parseFloat(priceEntry.price) || 0) : 0,
      max_adults: maxAdults || 2, max_children: maxChildren || 0
    });
  }
  save();
};

window.toggleZiarah = (id, name, priceSar) => {
  const idx = state.sel.ziarah.findIndex(z => z.id === id);
  if (idx >= 0) {
    state.sel.ziarah.splice(idx, 1); // tap again to deselect
  } else {
    state.sel.ziarah.push({
      id, name, price: parseFloat(priceSar) || 0,
      wantsTransport: null, routeId: null, routeName: '',
      wantsMoyallem: null, moyallemCategory: null, moyallemPriceSar: 0
    });
  }
  save();
};
window.setZiarahWantsTransport = (id, wants) => {
  const z = state.sel.ziarah.find(x => x.id === id);
  if (!z) return;
  z.wantsTransport = wants;
  if (!wants) { z.routeId = null; z.routeName = ''; }
  save();
};
window.pickZiarahRoute = (id, routeId, routeName) => {
  const z = state.sel.ziarah.find(x => x.id === id);
  if (!z) return;
  z.routeId = routeId;
  z.routeName = routeName;
  save();
};
window.setZiarahWantsMoyallem = (id, wants) => {
  const z = state.sel.ziarah.find(x => x.id === id);
  if (!z) return;
  z.wantsMoyallem = wants;
  if (!wants) { z.moyallemCategory = null; z.moyallemPriceSar = 0; }
  save();
};
window.pickZiarahMoyallemCategory = (id, category, priceSar) => {
  const z = state.sel.ziarah.find(x => x.id === id);
  if (!z) return;
  z.moyallemCategory = category;
  z.moyallemPriceSar = parseFloat(priceSar) || 0;
  save();
};

window.pickMeal = (mealSysId, mealName, tier, priceSar) => {
  state.sel.meal = { meal_sys_id: mealSysId, meal_name: mealName, tier, price: parseFloat(priceSar) || 0 };
  save();
};

function save() {
  localStorage.setItem('umrah_journey_v2', JSON.stringify(state.sel));
  renderTracker();
  renderStepContent();
  renderSummary();
}

function goNext() {
  if (!stepValid(getCurrentStepKey())) { renderValidationState(); return; }
  if (state.currentStep < totalSteps()) {
    state.currentStep++;
    if (!state.visited.includes(state.currentStep)) state.visited.push(state.currentStep);
    renderTracker();
    renderStepContent(true);
    renderSummary();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

// ──────────────────────────────────────────
// SUBMIT
// ──────────────────────────────────────────
async function submitJourney() {
  const s   = state.sel;
  const btn = document.getElementById('next-btn');
  const msg = document.getElementById('submit-msg');

  // Final guard — re-validate every visible step before sending
  for (const st of visibleSteps()) {
    if (!stepValid(st.key)) {
      state.currentStep = st.id;
      renderTracker(); renderStepContent(true); renderSummary();
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }
  }

  btn.disabled = true;
  btn.innerHTML = '<i data-lucide="loader" class="w-5 h-5 animate-spin"></i> Submitting...';
  lucide.createIcons();

  const rates  = CurrencyHandler.getRates() || state.rates;
  const total  = calcTotal();

  const fd = new FormData();
  fd.append('csrf_token',      '<?= htmlspecialchars($csrf) ?>');
  fd.append('build_data_json', JSON.stringify(buildSubmissionPayload()));

  try {
    const res  = await fetch(`<?= BASE_URL ?>/api/save-custom-build.php`, { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) {
      localStorage.removeItem('umrah_journey_v2');
      window.location.href = data.redirect;
    } else {
      msg.textContent = data.message;
      msg.className = 'px-4 py-3 rounded-xl text-sm font-medium bg-red-500/10 text-red-400';
      msg.classList.remove('hidden');
      // Fallback WhatsApp
      const wa = '<?= htmlspecialchars(getSetting('whatsapp_number','8801000000000')) ?>';
      const waMsg = encodeURIComponent(`Custom Umrah Journey\nName: ${s.travelers.name||'—'}\nPhone: ${s.travelers.phone||'—'}\nPackage: ${s.packageType||'—'}${s.connectionType?' | Flight: '+s.connectionType:''}\nDuration: ${s.duration} days\nTransport: ${(s.transport||[]).length ? s.transport.map(l=>l.vehicle_type).join(', ') : 'None'}\nTotal: SR ${total.toFixed(0)}`);
      window.open(`https://wa.me/${wa.replace('+','')}?text=${waMsg}`, '_blank');
    }
  } catch(e) {
    msg.textContent = 'Submission failed. Redirecting to WhatsApp...';
    msg.className = 'px-4 py-3 rounded-xl text-sm font-medium bg-yellow-500/10 text-yellow-400';
    msg.classList.remove('hidden');
    const wa = '<?= htmlspecialchars(getSetting('whatsapp_number','8801000000000')) ?>';
    const waMsg = encodeURIComponent(`Custom Umrah: ${s.travelers.name||'—'} | ${s.duration}d | SR ${total.toFixed(0)}`);
    window.open(`https://wa.me/${wa.replace('+','')}?text=${waMsg}`, '_blank');
  }
  btn.disabled = false;
  btn.innerHTML = '<i data-lucide="send" class="w-5 h-5"></i> Submit Journey';
  lucide.createIcons();
}

// ──────────────────────────────────────────
// NAVIGATION
// ──────────────────────────────────────────
document.getElementById('next-btn').addEventListener('click', () => {
  if (getCurrentStepKey() === 'review') { submitJourney(); return; }
  goNext();
});
document.getElementById('prev-btn').addEventListener('click', () => {
  if (state.currentStep > 1) {
    state.currentStep--;
    renderTracker();
    renderStepContent(true);
    renderSummary();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
});

// ──────────────────────────────────────────
// INIT
// ──────────────────────────────────────────
(async () => {
  await Promise.all([loadTransportRoutes(), loadVehicleTypes(), loadMoyallemServices(), loadVisaTypes(), loadFlights(), loadZiarahList(), loadMeals(), loadServiceLevels()]);
  // Sync currency/rates from CurrencyHandler
  const r = CurrencyHandler.getRates();
  if (r) state.rates = r;
  state.currency = CurrencyHandler.getCurrency() || state.currency;
  renderTracker();
  renderStepContent(true);
  renderSummary();
})();
</script>
</body></html>