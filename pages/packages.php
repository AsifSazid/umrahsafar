<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle       = 'Umrah Packages 2026 | TravHub Bangladesh';
$pageDescription = 'Browse all Umrah packages from Bangladesh. Filter by duration, budget, hotel stars. Book Economy, Premium or Luxury Umrah.';
include dirname(__DIR__) . '/includes/header.php';
?>
<style type="text/tailwindcss">
@layer components {
    .glass       { @apply bg-white/5 backdrop-blur-lg border border-white/10; }
    .glass-card  { @apply bg-white/5 backdrop-blur-lg border border/white/10 rounded-2xl shadow-2xl; }
    .btn-primary { @apply bg-secondary hover:bg-emerald text-primary font-semibold py-3 px-8 rounded-xl transition-all duration-300; }
    .btn-outline { @apply border-2 border-secondary text-secondary hover:bg-secondary hover:text-primary font-semibold py-3 px-8 rounded-xl transition-all duration-300; }
}
</style>

<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<!-- ══ HERO ══ -->
<section class="relative min-h-[60vh] flex items-center pt-20 overflow-hidden">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-b from-primary/80 via-primary/60 to-dark z-10"></div>
        <img src="https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?auto=format&fit=crop&q=80&w=2000" alt="Masjid al-Haram" class="w-full h-full object-cover" referrerpolicy="no-referrer">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-secondary/20 blur-[120px] rounded-full animate-pulse"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-teal/10 blur-[120px] rounded-full animate-pulse delay-1000"></div>
    </div>
    <div class="max-w-7xl mx-auto px-6 w-full relative z-20 text-center">
        <span class="inline-block text-secondary bg-secondary/10 border border-secondary/20 px-4 py-1 rounded-full text-xs font-bold uppercase tracking-widest mb-4">Spiritual Excellence</span>
        <h1 class="text-5xl lg:text-7xl font-bold leading-tight mb-6">
            Umrah Packages <span class="bg-gradient-to-r from-teal to-secondary bg-clip-text text-transparent">2026</span>
        </h1>
        <p class="text-white/60 text-lg max-w-2xl mx-auto mb-8">Choose from our carefully curated packages — Economy, Premium or Luxury. All inclusive from Bangladesh.</p>
        <div class="flex flex-wrap gap-6 justify-center text-sm text-white/60">
            <span class="flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4 text-secondary"></i> Visa Included</span>
            <span class="flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4 text-secondary"></i> Direct Flights</span>
            <span class="flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4 text-secondary"></i> Hotel + Transport</span>
            <span class="flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4 text-secondary"></i> 24/7 Support</span>
        </div>
    </div>
</section>

<!-- ══ FILTERS ══ -->
<section class="py-8 bg-navy/60 border-b border-white/5 sticky top-[72px] z-40 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
            <div class="flex flex-wrap gap-6">
                <!-- Duration -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-white/40 uppercase tracking-wider">Duration:</span>
                    <?php foreach (['all'=>'All','7'=>'7 Days','10'=>'10 Days','14'=>'14 Days','21'=>'21 Days'] as $v=>$l): ?>
                    <button class="filter-btn <?= $v==='all'?'bg-secondary text-primary':'bg-white/5 text-white/60 hover:text-white' ?> px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" data-filter="duration" data-value="<?= $v ?>"><?= $l ?></button>
                    <?php endforeach; ?>
                </div>
                <!-- Budget -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-white/40 uppercase tracking-wider">Budget:</span>
                    <?php foreach (['all'=>'All','economy'=>'Economy','premium'=>'Premium','luxury'=>'Luxury'] as $v=>$l): ?>
                    <button class="filter-btn <?= $v==='all'?'bg-secondary text-primary':'bg-white/5 text-white/60 hover:text-white' ?> px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" data-filter="budget" data-value="<?= $v ?>"><?= $l ?></button>
                    <?php endforeach; ?>
                </div>
                <!-- Stars -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-white/40 uppercase tracking-wider">Stars:</span>
                    <?php foreach (['all'=>'All','3'=>'3★','4'=>'4★','5'=>'5★'] as $v=>$l): ?>
                    <button class="filter-btn <?= $v==='all'?'bg-secondary text-primary':'bg-white/5 text-white/60 hover:text-white' ?> px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" data-filter="stars" data-value="<?= $v ?>"><?= $l ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Sort -->
            <div class="flex items-center gap-3">
                <span class="text-xs text-white/40 uppercase tracking-wider shrink-0">Sort:</span>
                <select id="sort-select" class="bg-navy border border-white/10 text-white text-sm rounded-lg px-3 py-1.5 focus:border-secondary focus:outline-none">
                    <option value="default">Featured</option>
                    <option value="price-asc">Price: Low → High</option>
                    <option value="price-desc">Price: High → Low</option>
                    <option value="duration-asc">Duration: Shortest</option>
                    <option value="duration-desc">Duration: Longest</option>
                    <option value="rating">Highest Rated</option>
                </select>
                <span id="results-count" class="text-xs text-white/40 shrink-0"></span>
            </div>
        </div>
    </div>
</section>

<!-- ══ PACKAGE GRID ══ -->
<section class="py-16 bg-dark relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6">
        <div id="package-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            // Load packages from DB
            $packages = [];
            try {
                require_once dirname(__DIR__) . '/data/server/db_connection.php';
                $db = getDB();
                $rows = $db->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $r) {
                    $r['price']      = $r['price_sar'];
                    $r['itinerary']  = json_decode($r['itinerary_json'] ?? '[]', true);
                    $r['img']        = 'https://images.unsplash.com/photo-1542810634-71277d95dcbb?auto=format&fit=crop&q=80&w=800';
                    $r['badgeColor'] = $r['budget'] === 'luxury' ? 'bg-gradient-to-r from-teal to-secondary text-primary'
                                     : ($r['budget'] === 'premium' ? 'bg-teal text-primary' : 'bg-secondary text-primary');
                    $packages[] = $r;
                }
            } catch (Exception $e) { }
            if (empty($packages)) { ?>
              <div class="col-span-3 text-center py-20 text-white/30">
                <i data-lucide="package" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
                <p>No packages available yet. Check back soon.</p>
              </div>
            <?php } else { foreach ($packages as $pkg):
                $stars = str_repeat('★', $pkg['stars'] ?? 3) . str_repeat('☆', 5 - ($pkg['stars'] ?? 3));
                $itineraryJson = htmlspecialchars(json_encode($pkg['itinerary'] ?? []), ENT_QUOTES);
                $stars = str_repeat('★', $pkg['stars']) . str_repeat('☆', 5 - $pkg['stars']);
                $itineraryJson = htmlspecialchars(json_encode($pkg['itinerary']), ENT_QUOTES);
            ?>
            <div class="glass-card group hover:-translate-y-2 transition-all duration-500 overflow-hidden flex flex-col"
                 data-duration="<?= $pkg['duration'] ?>"
                 data-budget="<?= $pkg['budget'] ?>"
                 data-stars="<?= $pkg['stars'] ?>"
                 data-price="<?= $pkg['price'] ?>"
                 data-rating="<?= $pkg['rating'] ?>"
                 data-id="<?= $pkg['id'] ?>">
                <div class="relative h-64 overflow-hidden">
                    <img src="<?= $pkg['img'] ?>" alt="<?= htmlspecialchars($pkg['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" referrerpolicy="no-referrer">
                    <?php if ($pkg['badge']): ?>
                    <div class="absolute top-4 left-4">
                        <span class="<?= $pkg['badgeColor'] ?> text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full shadow-lg"><?= $pkg['badge'] ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="absolute bottom-0 left-0 right-0 p-5 bg-gradient-to-t from-dark to-transparent">
                        <div class="flex items-center gap-1">
                            <span class="text-secondary text-xs tracking-wider"><?= str_repeat('★', $pkg['stars']) ?></span>
                            <span class="text-white text-xs font-bold ml-2"><?= $pkg['rating'] ?> (<?= $pkg['reviews'] ?> Reviews)</span>
                        </div>
                    </div>
                </div>
                <div class="p-8 flex flex-col flex-1">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-bold"><?= htmlspecialchars($pkg['name']) ?></h3>
                        <div class="text-right">
                            <p class="text-[10px] text-white/40 uppercase tracking-widest">Starting From</p>
                            <p class="text-2xl font-bold text-secondary price-tag" data-price-sar="<?= $pkg['price'] ?>">SR <?= number_format($pkg['price']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mb-6 text-white/60 text-sm">
                        <div class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4 text-secondary"></i><span><?= $pkg['duration'] ?> Days</span></div>
                        <div class="flex items-center gap-1.5"><i data-lucide="map-pin" class="w-4 h-4 text-secondary"></i><span><?= htmlspecialchars($pkg['city']) ?></span></div>
                    </div>
                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <?php foreach ([[$pkg['flight'],'plane'],[$pkg['hotel'],'bed'],[$pkg['transport'],'car']] as [$label,$icon]): ?>
                        <div class="flex flex-col items-center gap-1.5 p-3 bg-white/5 rounded-xl border border-white/5">
                            <i data-lucide="<?= $icon ?>" class="w-5 h-5 text-teal"></i>
                            <span class="text-[10px] uppercase font-bold text-white/40 text-center leading-tight"><?= htmlspecialchars($label) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Ziyarah Itinerary -->
                    <div class="mb-6">
                        <button class="itinerary-toggle text-secondary text-xs font-semibold flex items-center gap-1 hover:underline" data-itinerary='<?= $itineraryJson ?>'>
                            <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i> View Day-by-Day Itinerary
                        </button>
                    </div>
                    <!-- Compare checkbox -->
                    <label class="flex items-center gap-2 cursor-pointer mb-2 group">
                        <input type="checkbox" class="compare-check accent-secondary w-4 h-4"
                            data-id="<?= $pkg['id'] ?>" data-name="<?= addslashes(htmlspecialchars($pkg['name'])) ?>"
                            data-price="<?= $pkg['price'] ?>" data-stars="<?= $pkg['stars'] ?>"
                            data-duration="<?= $pkg['duration'] ?>" data-hotel="<?= htmlspecialchars($pkg['hotel']) ?>"
                            data-transport="<?= htmlspecialchars($pkg['transport']) ?>" data-rating="<?= $pkg['rating'] ?>"
                            onchange="toggleCompare(this)">
                        <span class="text-xs text-white/40 group-hover:text-secondary transition-colors">Add to Compare</span>
                    </label>
                    <div class="mt-auto flex gap-3">
                        <button onclick="openBookingModal(<?= $pkg['id'] ?>, '<?= addslashes($pkg['name']) ?>', <?= $pkg['price'] ?>)" class="btn-outline flex-1 py-2.5 text-sm">Inquire</button>
                        <button onclick="openBookingModal(<?= $pkg['id'] ?>, '<?= addslashes($pkg['name']) ?>', <?= $pkg['price'] ?>)" class="btn-primary flex-1 py-2.5 text-sm">Book Now</button>
                    </div>
                </div>
            </div>
            <?php endforeach; } ?>
        </div>
        <!-- No Results -->
        <div id="no-results" class="hidden py-20 text-center">
            <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="search-x" class="w-10 h-10 text-white/20"></i>
            </div>
            <h3 class="text-2xl font-bold mb-2">No Packages Found</h3>
            <p class="text-white/40">Try adjusting your filters.</p>
            <button id="reset-filters" class="mt-6 text-secondary font-bold hover:underline">Reset All Filters</button>
        </div>
    </div>
</section>

<!-- ══ ITINERARY MODAL ══ -->
<div id="itinerary-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-dark/90 backdrop-blur-sm" id="itinerary-overlay"></div>
    <div class="relative bg-navy border border-white/10 rounded-2xl w-full max-w-lg max-h-[80vh] overflow-hidden z-10 shadow-2xl">
        <div class="flex justify-between items-center px-6 py-5 border-b border-white/10">
            <h3 class="font-bold text-lg">Day-by-Day Itinerary</h3>
            <button id="close-itinerary" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center transition-all">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div id="itinerary-content" class="overflow-y-auto max-h-[60vh] p-6 space-y-4"></div>
    </div>
</div>

<!-- ══ BOOKING INQUIRY MODAL ══ -->
<div id="booking-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-dark/90 backdrop-blur-sm" id="booking-overlay"></div>
    <div class="relative bg-navy border border-white/10 rounded-2xl w-full max-w-md z-10 shadow-2xl">
        <div class="flex justify-between items-center px-6 py-5 border-b border-white/10">
            <h3 class="font-bold text-lg">Quick Booking Inquiry</h3>
            <button id="close-booking" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center transition-all">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-6">
            <div id="booking-pkg-info" class="bg-secondary/5 border border-secondary/20 rounded-xl p-4 mb-5">
                <p class="text-xs text-white/40 uppercase tracking-wider mb-1">Selected Package</p>
                <p id="modal-pkg-name" class="font-bold text-secondary"></p>
                <p id="modal-pkg-price" class="text-sm text-white/60 mt-0.5"></p>
            </div>
            <div class="space-y-4">
                <input type="text" id="modal-name" placeholder="Full name" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
                <input type="tel" id="modal-phone" placeholder="Phone / WhatsApp" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
                <input type="number" id="modal-travelers" placeholder="Number of travelers" min="1" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
            </div>
            <div class="mt-5 space-y-3">
                <button id="modal-whatsapp" class="w-full flex items-center justify-center gap-2 bg-[#25D366] hover:bg-[#1ebe5c] text-white font-semibold py-3 rounded-xl transition-all text-sm">
                    <i data-lucide="message-circle" class="w-4 h-4"></i> Continue on WhatsApp
                </button>
                <a href="<?= BASE_URL ?>/pages/contact.php" class="w-full flex items-center justify-center gap-2 border border-white/10 hover:border-secondary text-white/70 hover:text-secondary font-medium py-3 rounded-xl transition-all text-sm">
                    <i data-lucide="mail" class="w-4 h-4"></i> Send Email Inquiry
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ══ CTA ══ -->
<section class="py-16 bg-navy/30 border-t border-white/5">
    <div class="max-w-3xl mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold mb-4">Can't find what you're looking for?</h2>
        <p class="text-white/50 mb-8">Use our Custom Package Builder to design your own Umrah journey — choose your own dates, hotel, transport and duration.</p>
        <a href="<?= BASE_URL ?>/pages/package-builder.php" class="bg-secondary hover:bg-emerald text-primary font-semibold py-3 px-10 rounded-xl transition-all duration-300 inline-flex items-center gap-2">
            <i data-lucide="settings-2" class="w-5 h-5"></i> Build Custom Package
        </a>
    </div>
</section>

<!-- ══ PRAYER TIMES ══ -->
<section class="py-12 bg-dark border-t border-white/5 no-print">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center gap-3 mb-6">
            <i data-lucide="clock" class="w-5 h-5 text-secondary"></i>
            <h2 class="text-lg font-bold">Today's Prayer Times</h2>
            <span class="text-xs text-white/30">(Makkah, Madinah & Dhaka — live)</span>
        </div>
        <div class="grid md:grid-cols-3 gap-4">
            <div id="prayer-makkah"></div>
            <div id="prayer-madinah"></div>
            <div id="prayer-dhaka"></div>
        </div>
    </div>
</section>

<!-- ══ COMPARISON FLOATING BAR ══ -->
<div id="compare-bar" class="hidden fixed bottom-0 left-0 right-0 z-50 bg-navy/95 backdrop-blur-xl border-t border-secondary/30 py-4 shadow-2xl">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-secondary font-bold text-sm">Compare:</span>
            <div id="compare-chips" class="flex gap-2 flex-wrap"></div>
        </div>
        <div class="flex gap-3">
            <button onclick="openComparison()" id="compare-now-btn"
                class="bg-secondary hover:bg-emerald text-primary font-semibold py-2 px-6 rounded-xl text-sm transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                Compare Now
            </button>
            <button onclick="clearComparison()" class="bg-white/10 hover:bg-white/20 text-white text-sm font-medium py-2 px-4 rounded-xl transition-all">Clear</button>
        </div>
    </div>
</div>

<!-- ══ COMPARISON MODAL ══ -->
<div id="comparison-modal" class="hidden fixed inset-0 z-50 flex items-start justify-center p-4 pt-10 overflow-y-auto">
    <div class="absolute inset-0 bg-dark/95 backdrop-blur-sm" onclick="document.getElementById('comparison-modal').classList.add('hidden')"></div>
    <div class="relative bg-navy border border-white/10 rounded-2xl w-full max-w-5xl z-10 shadow-2xl overflow-hidden">
        <div class="flex justify-between items-center px-6 py-5 border-b border-white/10 sticky top-0 bg-navy z-10">
            <h3 class="font-bold text-lg">Package Comparison</h3>
            <button onclick="document.getElementById('comparison-modal').classList.add('hidden')"
                class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 flex items-center justify-center transition-all">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div id="comparison-table" class="overflow-x-auto p-6"></div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/currency-handler.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/prayer-times.js?v=<?= ASSET_VERSION ?>"></script>
<script>
const state = { filters: { duration:'all', budget:'all', stars:'all' }, sort:'default' };

function getCards() { return [...document.querySelectorAll('#package-grid > div[data-id]')]; }

function applyFilters() {
    const cards = getCards();
    let visible = 0;
    cards.forEach(card => {
        const dur    = card.getAttribute('data-duration');
        const bud    = card.getAttribute('data-budget');
        const stars  = card.getAttribute('data-stars');
        const show   =
            (state.filters.duration === 'all' || dur   === state.filters.duration) &&
            (state.filters.budget   === 'all' || bud   === state.filters.budget)   &&
            (state.filters.stars    === 'all' || stars  === state.filters.stars);
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('no-results').classList.toggle('hidden', visible > 0);
    document.getElementById('results-count').textContent = `${visible} package${visible !== 1 ? 's' : ''}`;
}

function applySort() {
    const grid  = document.getElementById('package-grid');
    const cards = getCards().filter(c => c.style.display !== 'none');
    cards.sort((a, b) => {
        const val  = state.sort;
        const pa   = +a.getAttribute('data-price'),    pb   = +b.getAttribute('data-price');
        const da   = +a.getAttribute('data-duration'), db   = +b.getAttribute('data-duration');
        const ra   = +a.getAttribute('data-rating'),   rb   = +b.getAttribute('data-rating');
        if (val === 'price-asc')      return pa - pb;
        if (val === 'price-desc')     return pb - pa;
        if (val === 'duration-asc')   return da - db;
        if (val === 'duration-desc')  return db - da;
        if (val === 'rating')         return rb - ra;
        return 0;
    });
    cards.forEach(c => grid.appendChild(c));
}

// Filter buttons
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const type = btn.getAttribute('data-filter');
        const val  = btn.getAttribute('data-value');
        state.filters[type] = val;
        document.querySelectorAll(`.filter-btn[data-filter="${type}"]`).forEach(b => {
            const active = b.getAttribute('data-value') === val;
            b.className = `filter-btn ${active ? 'bg-secondary text-primary' : 'bg-white/5 text-white/60 hover:text-white'} px-3 py-1.5 rounded-lg text-xs font-semibold transition-all`;
        });
        applyFilters();
    });
});

document.getElementById('sort-select').addEventListener('change', e => {
    state.sort = e.target.value;
    applySort();
});

document.getElementById('reset-filters')?.addEventListener('click', () => {
    state.filters = { duration:'all', budget:'all', stars:'all' };
    document.querySelectorAll('.filter-btn').forEach(b => {
        const active = b.getAttribute('data-value') === 'all';
        b.className = `filter-btn ${active ? 'bg-secondary text-primary' : 'bg-white/5 text-white/60 hover:text-white'} px-3 py-1.5 rounded-lg text-xs font-semibold transition-all`;
    });
    applyFilters();
});

// Itinerary modal
document.querySelectorAll('.itinerary-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const data = JSON.parse(btn.getAttribute('data-itinerary'));
        const content = document.getElementById('itinerary-content');
        content.innerHTML = data.map(d => `
            <div class="flex gap-4">
                <div class="shrink-0 w-10 h-10 rounded-full bg-secondary/10 border border-secondary/20 flex items-center justify-center text-secondary font-bold text-sm">${d.day}</div>
                <div class="flex-1 pb-4 border-b border-white/5 last:border-0">
                    <p class="font-semibold">${d.title}</p>
                    <p class="text-white/50 text-sm mt-1">${d.desc}</p>
                </div>
            </div>`).join('');
        document.getElementById('itinerary-modal').classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
});
document.getElementById('close-itinerary').addEventListener('click',  () => document.getElementById('itinerary-modal').classList.add('hidden'));
document.getElementById('itinerary-overlay').addEventListener('click', () => document.getElementById('itinerary-modal').classList.add('hidden'));

// Booking modal
let currentPkg = {};
function openBookingModal(id, name, priceSar) {
    currentPkg = { id, name, priceSar };
    document.getElementById('modal-pkg-name').textContent  = name;
    document.getElementById('modal-pkg-price').textContent = `Starting from SR ${priceSar.toLocaleString()}`;
    document.getElementById('booking-modal').classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
document.getElementById('close-booking').addEventListener('click',  () => document.getElementById('booking-modal').classList.add('hidden'));
document.getElementById('booking-overlay').addEventListener('click', () => document.getElementById('booking-modal').classList.add('hidden'));

document.getElementById('modal-whatsapp').addEventListener('click', () => {
    const name      = document.getElementById('modal-name').value.trim()     || 'Not provided';
    const phone     = document.getElementById('modal-phone').value.trim()    || 'Not provided';
    const travelers = document.getElementById('modal-travelers').value.trim()|| '1';
    const msg = encodeURIComponent(`Assalamu Alaikum! I'd like to book the *${currentPkg.name}* package.\n\nName: ${name}\nPhone: ${phone}\nTravelers: ${travelers}\nBudget: SR ${currentPkg.priceSar.toLocaleString()} (starting)\n\nPlease contact me to confirm.`);
    window.open(`https://wa.me/8801000000000?text=${msg}`, '_blank');
});

applyFilters();

// ── Package Comparison ──
let compareList = [];

function toggleCompare(checkbox) {
    const pkg = {
        id:        checkbox.getAttribute('data-id'),
        name:      checkbox.getAttribute('data-name'),
        price:     parseFloat(checkbox.getAttribute('data-price')),
        stars:     checkbox.getAttribute('data-stars'),
        duration:  checkbox.getAttribute('data-duration'),
        hotel:     checkbox.getAttribute('data-hotel'),
        transport: checkbox.getAttribute('data-transport'),
        rating:    checkbox.getAttribute('data-rating'),
    };
    if (checkbox.checked) {
        if (compareList.length >= 3) {
            checkbox.checked = false;
            alert('You can compare up to 3 packages at a time.');
            return;
        }
        compareList.push(pkg);
    } else {
        compareList = compareList.filter(p => p.id !== pkg.id);
    }
    updateCompareBar();
}

function updateCompareBar() {
    const bar   = document.getElementById('compare-bar');
    const chips = document.getElementById('compare-chips');
    const btn   = document.getElementById('compare-now-btn');
    if (compareList.length === 0) {
        bar.classList.add('hidden');
        return;
    }
    bar.classList.remove('hidden');
    btn.disabled = compareList.length < 2;
    chips.innerHTML = compareList.map(p => `
        <span class="bg-secondary/10 border border-secondary/20 text-secondary text-xs font-medium px-3 py-1.5 rounded-full flex items-center gap-1.5">
            ${p.name}
            <button onclick="removeFromCompare('${p.id}')" class="hover:text-white transition-colors">×</button>
        </span>`).join('');
}

function removeFromCompare(id) {
    compareList = compareList.filter(p => p.id !== id);
    // Uncheck the checkbox
    const cb = document.querySelector(`.compare-check[data-id="${id}"]`);
    if (cb) cb.checked = false;
    updateCompareBar();
}

function clearComparison() {
    compareList = [];
    document.querySelectorAll('.compare-check').forEach(cb => cb.checked = false);
    updateCompareBar();
}

function openComparison() {
    if (compareList.length < 2) return;
    const rows = [
        ['Price (SAR)',   p => `<span class="text-secondary font-bold">SR ${p.price.toLocaleString()}</span>`],
        ['Duration',      p => `${p.duration} days`],
        ['Hotel Stars',   p => '★'.repeat(parseInt(p.stars)) + '☆'.repeat(5-parseInt(p.stars))],
        ['Hotel',         p => p.hotel],
        ['Transport',     p => p.transport],
        ['Rating',        p => `⭐ ${p.rating}`],
    ];

    const headers = compareList.map(p => `<th class="px-5 py-4 text-left font-bold text-secondary">${p.name}</th>`).join('');
    const body = rows.map(([label, fn]) => {
        const cells = compareList.map(p => `<td class="px-5 py-4 text-sm text-white/70">${fn(p)}</td>`).join('');
        return `<tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
            <td class="px-5 py-4 text-xs text-white/40 uppercase tracking-wider font-medium">${label}</td>
            ${cells}
        </tr>`;
    }).join('');

    document.getElementById('comparison-table').innerHTML = `
        <table class="w-full">
            <thead class="border-b border-white/10">
                <tr>
                    <th class="px-5 py-4 text-left text-xs text-white/40 uppercase tracking-wider">Feature</th>
                    ${headers}
                </tr>
            </thead>
            <tbody>${body}</tbody>
        </table>
        <div class="flex gap-3 mt-6 flex-wrap">
            ${compareList.map(p => `
            <button onclick="openBookingModal(${p.id},'${p.name}',${p.price})"
                class="flex-1 bg-secondary hover:bg-emerald text-primary font-semibold py-2.5 px-4 rounded-xl transition-all text-sm min-w-[140px]">
                Book ${p.name}
            </button>`).join('')}
        </div>`;

    document.getElementById('comparison-modal').classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// Prayer times
PrayerWidget.init('prayer-makkah', 'prayer-madinah', 'prayer-dhaka');

</script>
</body>
</html>