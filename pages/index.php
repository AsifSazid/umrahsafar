<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle       = 'TravHub | Premium Umrah & Hajj Packages from Bangladesh';
$pageDescription = 'Customize your Umrah journey with TravHub. Best packages, hotels & transport from Bangladesh.';
$csrf = csrfToken();
// WhatsApp number — pulled from site_settings (Admin → Settings → Contact
// Information → WhatsApp Number), same source as includes/footer.php.
$miniBuilderWa = getSetting('whatsapp_number', '');
$miniBuilderWa = $miniBuilderWa !== '' ? ltrim($miniBuilderWa, '+') : '8801000000000';
include dirname(__DIR__) . '/includes/header.php';
?>

<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main>

<!-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ -->
<section class="relative min-h-screen flex items-center pt-20 overflow-hidden">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-b from-primary/80 via-primary/60 to-dark z-10"></div>
        <img src="https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?auto=format&fit=crop&q=80&w=2000" alt="Masjid al-Haram" class="w-full h-full object-cover blur-[2px]" referrerpolicy="no-referrer">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-secondary/20 blur-[120px] rounded-full animate-pulse"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-teal/10 blur-[120px] rounded-full animate-pulse delay-1000"></div>
    </div>
    <div class="max-w-7xl mx-auto px-6 w-full grid lg:grid-cols-2 gap-12 items-center relative z-20">
        <div class="hero-content">
            <span class="inline-block text-secondary font-bold tracking-widest uppercase text-xs mb-4 px-3 py-1 bg-secondary/10 rounded-full border border-secondary/20">Spiritual Journey</span>
            <h1 class="text-5xl lg:text-7xl font-bold leading-tight mb-6">Customize Your <br><span class="bg-gradient-to-r from-teal to-secondary bg-clip-text text-transparent">Umrah Journey</span></h1>
            <p class="text-white/60 text-lg max-w-lg mb-8 leading-relaxed">Design a pilgrimage that resonates with your soul. Every detail, from accommodation to flights, tailored for your peace of mind.</p>
            <div class="flex flex-wrap gap-4">
                <a href="<?= BASE_URL ?>/pages/package-builder.php" class="bg-secondary hover:bg-emerald text-primary font-semibold py-3 px-8 rounded-xl transition-all duration-300 shadow-lg shadow-secondary/20">Start Building</a>
                <a href="<?= BASE_URL ?>/pages/packages.php"        class="border-2 border-secondary text-secondary hover:bg-secondary hover:text-primary font-semibold py-3 px-8 rounded-xl transition-all duration-300">View Packages</a>
            </div>
            <!-- Trust badges -->
            <div class="flex items-center gap-6 mt-10 pt-8 border-t border-white/10">
                <div class="text-center"><p class="text-2xl font-bold text-secondary">2,400+</p><p class="text-xs text-white/40 uppercase tracking-widest">Pilgrims Served</p></div>
                <div class="w-px h-10 bg-white/10"></div>
                <div class="text-center"><p class="text-2xl font-bold text-secondary">98%</p><p class="text-xs text-white/40 uppercase tracking-widest">Satisfaction Rate</p></div>
                <div class="w-px h-10 bg-white/10"></div>
                <div class="text-center"><p class="text-2xl font-bold text-secondary">10+</p><p class="text-xs text-white/40 uppercase tracking-widest">Years Experience</p></div>
            </div>
        </div>
        <div class="relative hidden lg:flex justify-end">
            <div class="relative">
                <div class="absolute -inset-10 bg-secondary/10 blur-3xl rounded-full"></div>
                <div class="text-right">
                    <p class="text-6xl lg:text-8xl font-bold text-white/90 drop-shadow-[0_0_30px_rgba(80,188,129,0.3)]" style="font-family:serif">عُمْرَةٌ مَبَارَكَة</p>
                    <p class="text-secondary font-medium tracking-widest uppercase text-sm mt-4">Umrah Mubarak</p>
                </div>
            </div>
        </div>
    </div>
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 z-20 animate-bounce">
        <div class="w-px h-12 bg-gradient-to-b from-secondary to-transparent"></div>
        <span class="text-[10px] uppercase tracking-widest text-white/40">Scroll</span>
    </div>
</section>

<!-- ══════════════════════════════════════════
     STEP BUILDER (mini — homepage) — "Live Constellation"
══════════════════════════════════════════ -->
<section id="builder" class="py-24 bg-dark relative overflow-hidden">
    <!-- particle/star field canvas -->
    <canvas id="constellation-bg" class="absolute inset-0 w-full h-full pointer-events-none opacity-60"></canvas>
    <!-- ambient glow, echoes hero -->
    <div class="absolute top-1/3 -left-24 w-96 h-96 bg-secondary/10 blur-[140px] rounded-full pointer-events-none"></div>
    <div class="absolute bottom-0 -right-24 w-96 h-96 bg-teal/10 blur-[140px] rounded-full pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-6 relative">
        <div class="text-center mb-14">
            <span class="text-secondary font-bold tracking-widest uppercase text-xs">Interactive Planner</span>
            <h2 class="text-4xl font-bold mt-2">Build Your Journey</h2>
            <p class="text-white/40 mt-4">Every step lights up your path — chart your perfect Umrah.</p>
        </div>

        <!-- Radial Node Map -->
        <div class="mb-8">
            <svg id="node-map" class="w-full" viewBox="0 0 1000 140" preserveAspectRatio="xMidYMid meet"></svg>
        </div>

        <div class="grid lg:grid-cols-[1fr_260px] gap-8 items-start">
            <!-- Step Content -->
            <div class="min-w-0">
                <div class="hex-panel p-8 lg:p-10 relative overflow-hidden min-h-[420px]">
                    <div id="step-container"></div>
                </div>
            </div>

            <!-- Right column: Prev/Next + Radial Total Gauge.
                 Order flips responsively: on mobile the nav buttons come first
                 (above the gauge), on desktop (lg+) they come after (below it). -->
            <div class="flex flex-col gap-6 lg:sticky lg:top-28">
                <div class="order-2 hex-panel p-6 flex flex-col items-center">
                    <p class="text-[10px] text-white/30 uppercase tracking-wider mb-3">Est. Total</p>
                    <div class="relative w-40 h-40">
                        <svg viewBox="0 0 120 120" class="w-full h-full -rotate-90">
                            <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="10"/>
                            <circle id="gauge-arc" cx="60" cy="60" r="52" fill="none" stroke="url(#gaugeGrad)" stroke-width="10" stroke-linecap="round" stroke-dasharray="326.7" stroke-dashoffset="326.7" style="transition:stroke-dashoffset 0.6s cubic-bezier(0.22,1,0.36,1)"/>
                            <defs>
                                <linearGradient id="gaugeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#50BC81"/>
                                    <stop offset="100%" stop-color="#02CCFE"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <p id="mini-total" class="text-xl font-bold text-secondary tabular-nums">SR 0</p>
                            <p id="gauge-step-label" class="text-[9px] text-white/30 uppercase tracking-wider mt-1">Step 1</p>
                        </div>
                    </div>
                    <p class="text-[10px] text-white/20 text-center mt-4 leading-relaxed">Your journey map lights up as you go — each star is a step closer.</p>
                </div>

                <div class="order-1 lg:order-3 flex justify-between items-center gap-3">
                    <button id="prev-step" class="flex items-center gap-2 font-bold transition-colors text-white/10 cursor-not-allowed" disabled>
                        <i data-lucide="chevron-left" class="w-5 h-5"></i> Previous
                    </button>
                    <button id="next-step" class="bg-secondary hover:bg-emerald text-primary font-semibold py-3 px-8 rounded-xl transition-all duration-300 flex items-center gap-2 hover:shadow-lg hover:shadow-secondary/20 hover:-translate-y-0.5">
                        Next <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Meal-plan minimum-group-size modal -->
<div id="meal-blocked-modal-overlay" class="fixed inset-0 z-[60] bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-sm p-6 text-center">
    <div class="w-12 h-12 rounded-full bg-amber-500/10 flex items-center justify-center mx-auto mb-4">
      <i data-lucide="users" class="w-6 h-6 text-amber-400"></i>
    </div>
    <h3 class="font-bold text-lg mb-2">Group Size Required</h3>
    <p id="meal-blocked-modal-text" class="text-white/50 text-sm mb-6">To avail this, you need at least 10 adult travelers.</p>
    <button onclick="closeMealBlockedModal()" class="w-full bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm">Got It</button>
  </div>
</div>

<!-- ══════════════════════════════════════════
     FEATURED PACKAGES
══════════════════════════════════════════ -->
<section class="py-24 bg-navy/30">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-6">
            <div class="max-w-2xl">
                <span class="text-secondary font-bold tracking-widest uppercase text-xs">Curated Experiences</span>
                <h2 class="text-4xl font-bold mt-2">Featured Umrah Packages</h2>
                <p class="text-white/40 mt-4">Choose from our most popular pre-designed packages, crafted for different needs and budgets.</p>
            </div>
            <a href="<?= BASE_URL ?>/pages/packages.php" class="text-secondary font-bold flex items-center gap-2 group">
                View All Packages <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Economy -->
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl overflow-hidden group fade-on-scroll">
                <div class="relative h-64 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1564769625905-50e93615e769?auto=format&fit=crop&q=80&w=800" alt="Economy Package" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute top-4 left-4 bg-primary/80 backdrop-blur-md px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">Economy</div>
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-bold mb-4">Silver Journey</h3>
                    <ul class="space-y-3 mb-8 text-white/60 text-sm">
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-secondary"></i> 10 Days (6 Makkah, 4 Madinah)</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-secondary"></i> 3-Star Hotels (600m from Haram)</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-secondary"></i> Shared Transport</li>
                    </ul>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs text-white/40 block uppercase">Starting from</span>
                            <span class="text-2xl font-bold text-secondary" data-price-sar="3500">SR 3,500</span>
                        </div>
                        <a href="<?= BASE_URL ?>/pages/packages.php" class="w-12 h-12 bg-white/5 rounded-xl flex items-center justify-center hover:bg-secondary hover:text-primary transition-all">
                            <i data-lucide="arrow-right" class="w-6 h-6"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Premium -->
            <div class="bg-white/5 backdrop-blur-lg border border-secondary/30 rounded-2xl shadow-2xl overflow-hidden group relative fade-on-scroll">
                <div class="absolute top-0 left-0 right-0 h-1 bg-secondary"></div>
                <div class="relative h-64 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?auto=format&fit=crop&q=80&w=800" alt="Premium Package" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute top-4 left-4 bg-secondary text-primary px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">Most Popular</div>
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-bold mb-4">Golden Spiritual</h3>
                    <ul class="space-y-3 mb-8 text-white/60 text-sm">
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-secondary"></i> 14 Days (8 Makkah, 6 Madinah)</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-secondary"></i> 4-Star Hotels (200m from Haram)</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-secondary"></i> Private Transport</li>
                    </ul>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs text-white/40 block uppercase">Starting from</span>
                            <span class="text-2xl font-bold text-secondary" data-price-sar="5800">SR 5,800</span>
                        </div>
                        <a href="<?= BASE_URL ?>/pages/packages.php" class="w-12 h-12 bg-secondary rounded-xl flex items-center justify-center text-primary hover:bg-emerald transition-all shadow-lg shadow-secondary/20">
                            <i data-lucide="arrow-right" class="w-6 h-6"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Luxury -->
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl overflow-hidden group fade-on-scroll">
                <div class="relative h-64 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1580418827493-f2b22c0a76cb?auto=format&fit=crop&q=80&w=800" alt="Luxury Package" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute top-4 left-4 bg-primary/80 backdrop-blur-md px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">Luxury</div>
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-bold mb-4">Royal Platinum</h3>
                    <ul class="space-y-3 mb-8 text-white/60 text-sm">
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-secondary"></i> 10 Days (5 Makkah, 5 Madinah)</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-secondary"></i> 5-Star Hotels (Front Row)</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-secondary"></i> VIP Private Transport</li>
                    </ul>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs text-white/40 block uppercase">Starting from</span>
                            <span class="text-2xl font-bold text-secondary" data-price-sar="9200">SR 9,200</span>
                        </div>
                        <a href="<?= BASE_URL ?>/pages/packages.php" class="w-12 h-12 bg-white/5 rounded-xl flex items-center justify-center hover:bg-secondary hover:text-primary transition-all">
                            <i data-lucide="arrow-right" class="w-6 h-6"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     WHY CHOOSE US
══════════════════════════════════════════ -->
<section class="relative py-24 overflow-hidden">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-primary/90 z-10"></div>
        <img src="https://images.unsplash.com/photo-1565035010268-a3816f98589a?auto=format&fit=crop&q=80&w=2000" alt="Masjid al-Nabawi" class="w-full h-full object-cover blur-sm">
    </div>
    <div class="max-w-7xl mx-auto px-6 relative z-20">
        <div class="text-center mb-16">
            <span class="text-secondary font-bold tracking-widest uppercase text-xs">Our Commitment</span>
            <h2 class="text-4xl font-bold mt-2">Why Choose TravHub</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php
            $features = [
                ['icon'=>'shield-check','title'=>'Trusted Guidance','desc'=>'Expert support throughout your spiritual journey from certified guides.'],
                ['icon'=>'heart','title'=>'Spiritual Focus','desc'=>'We handle the logistics, so you can focus entirely on your Ibadah.'],
                ['icon'=>'clock','title'=>'24/7 Support','desc'=>'Our team is available round the clock in Makkah & Madinah for any assistance.'],
                ['icon'=>'star','title'=>'Premium Quality','desc'=>'Handpicked hotels and transport services for maximum comfort and peace.'],
            ];
            foreach ($features as $f): ?>
            <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl p-8 group hover:-translate-y-2 transition-all duration-500 fade-on-scroll">
                <div class="w-14 h-14 bg-secondary/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-secondary group-hover:text-primary transition-all duration-500">
                    <i data-lucide="<?= $f['icon'] ?>" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold mb-3"><?= $f['title'] ?></h3>
                <p class="text-white/40 text-sm leading-relaxed"><?= $f['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     FAQ
══════════════════════════════════════════ -->
<section class="py-24 bg-dark">
    <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-16">
            <span class="text-secondary font-bold tracking-widest uppercase text-xs">Common Questions</span>
            <h2 class="text-4xl font-bold mt-2">Frequently Asked Questions</h2>
        </div>
        <div class="space-y-4" id="faq-container"></div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     PRAYER TIMES WIDGET
══════════════════════════════════════════ -->
<section class="py-16 bg-navy/20 border-t border-white/5">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-8 flex-wrap gap-4">
            <div>
                <span class="text-secondary font-bold tracking-widest uppercase text-xs">Live</span>
                <h2 class="text-2xl font-bold mt-1 flex items-center gap-3">
                    <i data-lucide="clock" class="w-6 h-6 text-secondary"></i> Today's Prayer Times
                </h2>
            </div>
            <p class="text-white/30 text-xs">Makkah, Madinah & Dhaka · Updates daily via Aladhan API</p>
        </div>
        <div class="grid md:grid-cols-3 gap-5">
            <div id="prayer-makkah"></div>
            <div id="prayer-madinah"></div>
            <div id="prayer-dhaka"></div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     CTA
══════════════════════════════════════════ -->
<section class="py-24 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6">
        <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl p-12 lg:p-20 relative overflow-hidden flex flex-col lg:flex-row items-center gap-12">
            <div class="flex-1 relative">
                <div class="relative z-10 animate-bounce-slow">
                    <img src="https://images.unsplash.com/photo-1542810634-71277d95dcbb?auto=format&fit=crop&q=80&w=800" alt="Umrah Planning" class="rounded-2xl shadow-2xl border border-white/10">
                </div>
            </div>
            <div class="flex-1 text-center lg:text-left">
                <span class="text-secondary font-bold tracking-widest uppercase text-xs">Ready to begin?</span>
                <h2 class="text-4xl lg:text-5xl font-bold mt-4 mb-6">Start Your Spiritual <br><span class="bg-gradient-to-r from-teal to-secondary bg-clip-text text-transparent">Journey Today</span></h2>
                <p class="text-white/60 text-lg mb-8 max-w-md mx-auto lg:mx-0">Join thousands of pilgrims who have trusted us with their most meaningful travel experience.</p>
                <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
                    <a href="<?= BASE_URL ?>/pages/package-builder.php" class="bg-secondary hover:bg-emerald text-primary font-semibold py-3 px-10 rounded-xl transition-all duration-300 text-lg">Start Your Journey</a>
                    <a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? ltrim(WHATSAPP_NUMBER,'+') : '8801000000000' ?>" target="_blank" class="flex items-center gap-2 border border-secondary text-secondary hover:bg-secondary hover:text-primary font-semibold py-3 px-8 rounded-xl transition-all duration-300 text-lg">
                        <i data-lucide="message-circle" class="w-5 h-5"></i> WhatsApp Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

<script src="<?= BASE_URL ?>/assets/js/currency-handler.js?<?php echo time(); ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js?<?php echo time(); ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/prayer-times.js?v=<?= ASSET_VERSION ?>"></script>
<script>
// ── Homepage Builder State — "Your Choice" architecture ──
// Visa/Flight/Moyallem/Meal prices come live from admin-managed APIs.
// Hotel data comes from the external TravHub master-data API. Transport
// and Ziarah come from their own admin-managed tables.
const BASE_PRICING = {
    pkg: { 'Economy':1200, 'Premium':2500, 'Luxury':4500 },
    // hotel pricing removed — real per-night room rates now come from
    // api/hotels.php (getAccommodationPrice()) instead of a flat estimate.
};

const state = {
    currency: localStorage.getItem('travhub_currency') || 'SAR',
    exchangeRates: { SAR:1, USD:0.2664, BDT:32.5 },
    currencySymbols: { SAR:'SR ', USD:'$', BDT:'৳' },
    step: 1,
    visited: [1],               // step positions the user has actually reached — only these + current are clickable
    openFaqIndex: 0,

    // Live module data, fetched once on load
    transportRoutes: [],
    vehicleTypesById: {},
    visaTypes: [],
    flights: [],
    ziarahList: [],
    moyallemServices: [],
    meals: [],
    serviceLevels: [],
    hotels: [],
    hotelsLoading: false,
    hotelsFetchFailed: false,     // true once a fetch attempt has errored (e.g. CORS/network) — stops auto-retry until user asks
    hotelsFetchAttempted: false,  // true once a fetch attempt has SETTLED (success or failure), even with 0 results — distinguishes "never fetched" from "fetched, found nothing"
    expandedHotelSysId: null,     // which hotel's room types are currently shown (fetched on demand)
    expandedRoomTypeSysId: null,  // which room type's service categories are currently shown
    selectedHotelCity: 'Makkah',  // active city tab (Makkah | Madinah | Jeddah)
    roomTypeCache: {},            // hotelSysId -> [room_types]
    globalBoardTypes: undefined,  // [board_types] — fetched once, shared by every room type
    roomPricesCache: {},          // roomTypeSysId -> [board_prices] (once fetch has settled)

    // Transient UI-only state (not saved to localStorage)
    transportPickerRouteId: null, // which route's vehicle list is currently expanded in the Transport step
    mealBlockedModalOpen: false,  // "you need at least N adults" modal

    formData: {
        id: '',
        currency: 'SAR',
        travelers: { name:'', phone:'', email:'', no_of_pax: { adult:1, child:0, infant:0 } },

        // "Your Choice" (Step 2) — what the traveler wants included
        choices: {
            visa: false,
            flight: false,
            hotel: false,
            transport: false,
            ziarah: false,
            moyallem: false,
            meal: false,
        },
        hotelCategory: '4-Star',   // only meaningful if choices.hotel
        serviceLevel: 'Economy',   // Economy | Premium | Luxury — overall package tier, always shown

        visa: null,                // { visa_type, price }
        flight: null,              // { connection_type, flight_date } — NO price shown/charged yet ("Fare Type" is no longer a traveler-facing choice)
        accommodation: [],         // array of { hotel_sys_id, hotel_name, room_type_sys_id, room_name, board_type_sys_id, board_name, price, max_adults, max_children }
        transport: [],             // array of { route_sys_id, route_name, vehicle_type, vehicle_type_sys_id, price, ziarah_id? }
        ziarah: [],                // array of { id, name, price, wantsTransport, routeId, wantsMoyallem, moyallemCategory, moyallemPriceSar }
        moyallem: [],              // array of { sys_id, name, category, price } — general Moyallem step selections
        meal: null,                // { meal_sys_id, meal_name, tier, price } | null

        duration: 10,
        makkahStay: 6,
        madinahStay: 4,
    }
};

function newBuildId(){
    return 'MB-' + Date.now().toString(36).toUpperCase() + '-' + Math.random().toString(36).slice(2,6).toUpperCase();
}

// ── Fetch live exchange rates ──
fetch(`<?= BASE_URL ?>/api/exchange-rates.php`).then(r=>r.json()).then(d=>{
    state.exchangeRates = { SAR:d.SAR, USD:d.USD, BDT:d.BDT };
    if(getCurrentStepKey()==='summary') renderStepContent();
}).catch(()=>{});

// ── Fetch real transport routes ──
fetch(`<?= BASE_URL ?>/api/transport-routes.php`).then(r=>r.json()).then(d=>{
    if(d.success) state.transportRoutes = d.routes || [];
    if(getCurrentStepKey()==='transport') renderStepContent();
}).catch(()=>{ console.warn('Transport routes unavailable'); });

// ── Fetch vehicle types — routes only store vehicle_type_sys_id now (a
// relational reference, not a plain name string), so this lookup map is
// needed to resolve a sys_id to a display name/icon wherever a route's
// vehicle_options are shown. ──
fetch(`<?= BASE_URL ?>/api/vehicle-types.php`).then(r=>r.json()).then(d=>{
    if(d.success){
        state.vehicleTypesById = {};
        (d.vehicle_types||[]).forEach(v => { state.vehicleTypesById[v.sys_id] = v; });
    }
    if(getCurrentStepKey()==='transport') renderStepContent();
}).catch(()=>{ console.warn('Vehicle types unavailable'); });

// ── Fetch live visa types ──
// ── Fetch live visa types — the Mini Builder no longer has a dedicated Visa
// step; instead the "Visa" Yes/No card in "Your Choice" adds the Umrah
// Visa price directly when turned on. ──
fetch(`<?= BASE_URL ?>/api/visa-types.php`).then(r=>r.json()).then(d=>{
    if(d.success){
        state.visaTypes = d.visa_types || [];
        const umrahVisa = state.visaTypes.find(v => /umrah/i.test(v.name)) || state.visaTypes[0];
        if(umrahVisa){
            state.formData.visa = { visa_type: umrahVisa.name, visa_type_sys_id: umrahVisa.sys_id, price: parseFloat(umrahVisa.price)||0 };
        }
    }
    if(getCurrentStepKey()==='summary') { renderStepContent(); updateMiniTotal(); }
}).catch(()=>{ console.warn('Visa types unavailable'); });

// ── Fetch live flight fares — used only as a source of Direct/Connecting
// reference data for the Connection Type step; no fare price is ever
// surfaced or added to the estimate. ──
fetch(`<?= BASE_URL ?>/api/flights.php`).then(r=>r.json()).then(d=>{
    if(d.success){
        state.flights = d.flights || [];
        if(!state.formData.flight && state.flights.length){
            const f = state.flights[0];
            const firstConn = (f.type_prices||[])[0];
            state.formData.flight = {
                connection_type: firstConn ? firstConn.type : null,
                flight_date: ''
            };
        }
    }
    if(getCurrentStepKey()==='flight' || getCurrentStepKey()==='summary') { renderStepContent(); updateMiniTotal(); }
}).catch(()=>{ console.warn('Flights unavailable'); });

// ── Fetch Ziarah tours ──
fetch(`<?= BASE_URL ?>/api/ziarah.php`).then(r=>r.json()).then(d=>{
    if(d.success) state.ziarahList = d.ziarah || [];
    if(getCurrentStepKey()==='ziarah') { renderStepContent(); updateMiniTotal(); }
}).catch(()=>{ console.warn('Ziarah list unavailable'); });

// ── Fetch Moyallem services (category-priced: General/Expert/VIP) ──
fetch(`<?= BASE_URL ?>/api/moyallem-handler.php`).then(r=>r.json()).then(d=>{
    if(d.success) state.moyallemServices = d.services || [];
    if(getCurrentStepKey()==='moyallem' || getCurrentStepKey()==='ziarah') { renderStepContent(); updateMiniTotal(); }
}).catch(()=>{ console.warn('Moyallem services unavailable'); });

// ── Fetch meal plans (tier-priced: Economy/Premium/Luxury) ──
fetch(`<?= BASE_URL ?>/api/meals.php`).then(r=>r.json()).then(d=>{
    if(d.success) state.meals = d.meals || [];
    if(getCurrentStepKey()==='package') { renderStepContent(); updateMiniTotal(); }
}).catch(()=>{ console.warn('Meals unavailable'); });

// ── Fetch Service Levels (Economy/Premium/Luxury — admin-managed pricing) ──
fetch(`<?= BASE_URL ?>/api/service-levels.php`).then(r=>r.json()).then(d=>{
    if(d.success) state.serviceLevels = d.service_levels || [];
    if(getCurrentStepKey()==='package') { renderStepContent(); updateMiniTotal(); }
}).catch(()=>{ console.warn('Service levels unavailable'); });

// ── Fetch hotels from our OWN database (api/hotels.php), filtered by city
// (Makkah/Madinah/Jeddah tabs). ──
const HOTEL_API_BASE = '<?= BASE_URL ?>/api/hotels.php';
let __hotelFetchController = null;
function fetchHotels(city){
    // Abort any in-flight hotel request before starting a new one — prevents
    // a pile-up of duplicate pending requests if this gets called again
    // (e.g. re-render, Refresh click) before the first one has resolved.
    if (__hotelFetchController) __hotelFetchController.abort();
    __hotelFetchController = new AbortController();

    state.hotelsLoading = true;
    state.hotelsFetchFailed = false;
    const star = parseInt(state.formData.hotelCategory) || '';
    const params = new URLSearchParams();
    if (city) params.set('city', city);
    if (star) params.set('star', star);
    const qs = params.toString();
    const url = qs ? `${HOTEL_API_BASE}?${qs}` : HOTEL_API_BASE;
    fetch(url, { signal: __hotelFetchController.signal })
        .then(r=>r.json())
        .then(d=>{
            state.hotelsLoading = false;
            state.hotelsFetchAttempted = true; // request settled — even a 0-result success must not re-trigger a fetch
            state.hotels = (d && d.success) ? (d.hotels || []) : [];
            if (getCurrentStepKey()==='accommodation') renderStepContent();
        })
        .catch(err=>{
            if (err && err.name === 'AbortError') return; // superseded by a newer request — ignore
            state.hotelsLoading = false;
            state.hotelsFetchFailed = true;
            state.hotelsFetchAttempted = true;
            state.hotels = [];
            console.warn('Hotel API unavailable (network error or CORS block):', err);
            if (getCurrentStepKey()==='accommodation') renderStepContent();
        });
}

// Fetch one hotel's room types (on-demand, cached per hotel sys_id so
// re-expanding the same hotel doesn't re-fetch).
function fetchRoomTypes(hotelSysId){
    if (state.roomTypeCache[hotelSysId]) { renderStepContent(); return; }
    fetch(`${HOTEL_API_BASE}?scope=room_types&hotel_sys_id=${encodeURIComponent(hotelSysId)}`)
        .then(r=>r.json())
        .then(d=>{
            state.roomTypeCache[hotelSysId] = (d && d.success) ? (d.room_types || []) : [];
            if (getCurrentStepKey()==='accommodation') renderStepContent();
        })
        .catch(()=>{
            state.roomTypeCache[hotelSysId] = [];
            if (getCurrentStepKey()==='accommodation') renderStepContent();
        });
}

// Fetch the global board-type catalog (Room Only / Bed & Breakfast / etc)
// — same list for every room type, so this only needs to run once.
function fetchGlobalBoardTypes(){
    if (state.globalBoardTypes !== undefined) { renderStepContent(); return; }
    fetch(`<?= BASE_URL ?>/api/room-board-types.php`)
        .then(r=>r.json())
        .then(d=>{
            state.globalBoardTypes = (d && d.success) ? (d.board_types || []) : [];
            if (getCurrentStepKey()==='accommodation') renderStepContent();
        })
        .catch(()=>{
            state.globalBoardTypes = [];
            if (getCurrentStepKey()==='accommodation') renderStepContent();
        });
}

// Fetch one room type's board prices (all board-type prices for that room
// come back together) — cached per room type.
function fetchRoomPrices(roomTypeSysId){
    if (state.roomPricesCache[roomTypeSysId] !== undefined) { renderStepContent(); return; }
    fetch(`<?= BASE_URL ?>/api/room-prices.php?room_type_sys_id=${encodeURIComponent(roomTypeSysId)}`)
        .then(r=>r.json())
        .then(d=>{
            const row = (d && d.success) ? d.prices : null;
            state.roomPricesCache[roomTypeSysId] = row ? (row.board_prices || []) : [];
            if (getCurrentStepKey()==='accommodation') renderStepContent();
        })
        .catch(()=>{
            state.roomPricesCache[roomTypeSysId] = [];
            if (getCurrentStepKey()==='accommodation') renderStepContent();
        });
}

// Picks the price entry for one board type on one room, preferring the one
// whose validity range covers today; falls back to the first match if none
// currently applies (still shows a number rather than none).
function getBoardPriceFor(roomTypeSysId, boardTypeSysId){
    const prices = (state.roomPricesCache[roomTypeSysId] || []).filter(p => p.board_type_sys_id === boardTypeSysId);
    if (!prices.length) return null;
    const today = new Date().toISOString().slice(0,10);
    return prices.find(p => p.valid_from <= today && today <= p.valid_to) || prices[0];
}

// ── Persist builder state — mini builder uses its OWN localStorage key
// and its own JSON shape, intentionally separate from
// /pages/package-builder.php's shape. ──
const MINI_STORAGE_KEY = 'umrah_mini_journey_v1';
function saveState(){
    try{
        if (!state.formData.id) state.formData.id = newBuildId();
        state.formData.currency = state.currency;
        localStorage.setItem(MINI_STORAGE_KEY, JSON.stringify(state.formData));
    }catch(e){}
}
function loadState(){
    try {
        const raw = localStorage.getItem(MINI_STORAGE_KEY);
        if (!raw) return;
        const saved = JSON.parse(raw) || {};
        state.formData = {
            ...state.formData,
            ...saved,
            travelers: {
                ...state.formData.travelers,
                ...(saved.travelers || {}),
                no_of_pax: { ...state.formData.travelers.no_of_pax, ...((saved.travelers||{}).no_of_pax || {}) }
            },
            choices: { ...state.formData.choices, ...(saved.choices || {}) },
            accommodation: Array.isArray(saved.accommodation) ? saved.accommodation : [],
            transport: Array.isArray(saved.transport) ? saved.transport : [],
            ziarah: Array.isArray(saved.ziarah) ? saved.ziarah : [],
            moyallem: Array.isArray(saved.moyallem) ? saved.moyallem : [],
        };
        if (saved.currency) state.currency = saved.currency;
    } catch(e) { /* keep defaults on any parse error */ }
}
loadState();

// ── Dynamic step list — depends on Step 2 "Your Choice" toggles ──
// Every step has a stable `key` (used everywhere in logic) and a display
// `id` (1-based position among currently-visible steps, recalculated live).
const ALL_STEPS = [
    {key:'travelers',     title:'Travelers',     icon:'user'},
    {key:'package',       title:'Your Choice',   icon:'list-checks'},
    {key:'flight',        title:'Flight',        icon:'plane'},
    {key:'accommodation', title:'Accommodation', icon:'hotel'},
    {key:'ziarah',        title:'Ziarah',        icon:'landmark'},
    {key:'transport',     title:'Transport',     icon:'car'},
    {key:'moyallem',      title:'Moyallem',      icon:'users'},
    {key:'summary',       title:'Summary',       icon:'check-circle-2'},
];

function stepIsVisible(key){
    const c = state.formData.choices;
    if (key === 'flight')        return !!c.flight;
    if (key === 'accommodation') return !!c.hotel;
    if (key === 'ziarah')        return !!c.ziarah;
    if (key === 'transport')     return !!c.transport;
    if (key === 'moyallem')      return !!c.moyallem;
    return true; // travelers, package, summary always show
}
function visibleSteps(){ return ALL_STEPS.filter(s => stepIsVisible(s.key)).map((s,i)=>({...s, id:i+1})); }
function getCurrentStepKey(){ const vs=visibleSteps(); const found=vs.find(s=>s.id===state.step); return found ? found.key : vs[0].key; }
function totalSteps(){ return visibleSteps().length; }

// When "Your Choice" toggles change and shrink/grow the visible step list,
// make sure state.step still points at a valid position.
function clampStep(){
    const t=totalSteps();
    if(state.step>t) state.step=t;
    if(state.step<1) state.step=1;
    // Positional IDs are recalculated every time the visible step list changes
    // — drop any visited marks that fall outside the current range so
    // goToStep() never targets a stale/mismatched position.
    state.visited = state.visited.filter(id => id >= 1 && id <= t);
    if (!state.visited.includes(state.step)) state.visited.push(state.step);
}


const faqs=[
    {q:'What is included in the Land Package?',a:'The Land Package includes accommodation in Makkah and Madinah, ground transportation, and visa processing. It does not include international airfare.'},
    {q:'How far in advance should I book my Umrah?',a:'We recommend booking at least 30-45 days in advance to ensure availability of your preferred hotels and sufficient time for visa processing.'},
    {q:'Can I customize the duration of my stay?',a:'Yes! Our interactive step builder allows you to choose between 7, 10, 14, or 21 days, and further customize the split between Makkah and Madinah.'},
    {q:'Is travel insurance included?',a:'Basic medical insurance is included as part of the Umrah visa fee. However, we recommend purchasing additional comprehensive travel insurance for peace of mind.'},
    {q:'What are the payment options?',a:'We accept bKash, bank transfer, and cash at our Dhaka office. Full payment is required at least 21 days before departure.'},
];

// ── "Live Constellation" — subtle animated star field behind the builder ──
function initConstellationBg(){
    const canvas = document.getElementById('constellation-bg');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let w, h, stars = [];
    const STAR_COUNT = 60;

    function resize(){
        const rect = canvas.parentElement.getBoundingClientRect();
        w = canvas.width = rect.width;
        h = canvas.height = rect.height;
    }
    function initStars(){
        stars = Array.from({length: STAR_COUNT}, () => ({
            x: Math.random() * w,
            y: Math.random() * h,
            r: Math.random() * 1.3 + 0.3,
            baseAlpha: Math.random() * 0.4 + 0.15,
            phase: Math.random() * Math.PI * 2,
            speed: 0.4 + Math.random() * 0.6,
        }));
    }
    resize(); initStars();
    window.addEventListener('resize', () => { resize(); initStars(); });

    let t = 0;
    function frame(){
        t += 0.015;
        ctx.clearRect(0, 0, w, h);
        stars.forEach(s => {
            const twinkle = Math.sin(t * s.speed + s.phase) * 0.5 + 0.5;
            ctx.beginPath();
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(80,188,129,${(s.baseAlpha * twinkle).toFixed(3)})`;
            ctx.fill();
        });
        requestAnimationFrame(frame);
    }
    // Respect reduced-motion preference — render a static frame only.
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        stars.forEach(s => { ctx.beginPath(); ctx.arc(s.x, s.y, s.r, 0, Math.PI*2); ctx.fillStyle = `rgba(80,188,129,${s.baseAlpha})`; ctx.fill(); });
        return;
    }
    requestAnimationFrame(frame);
}

// ── "Live Constellation" node map — SVG radial layout, connected by glowing lines ──
function isStepClickable(stepId){
    return state.visited.includes(stepId) || stepId === state.step;
}

function goToStep(stepId){
    if (!isStepClickable(stepId)) return;
    if (stepId === state.step) return;
    state.step = stepId;
    if (!state.visited.includes(stepId)) state.visited.push(stepId);
    renderNodeMap();
    renderStepContent(true);
    scrollToStepCard();
}

function renderNodeMap(){
    const svg = document.getElementById('node-map');
    if (!svg) return;
    const steps = visibleSteps();
    clampStep();

    const n = steps.length;
    const W = 1000, H = 140;
    const marginX = 60;
    const usable = W - marginX * 2;
    // Slight vertical wave so nodes don't sit on a flat line — reads as a "constellation path" not a bar.
    const pts = steps.map((s, i) => {
        const x = n > 1 ? marginX + (usable * i) / (n - 1) : W / 2;
        const wave = Math.sin(i * 1.15) * 18;
        const y = H / 2 + wave;
        return { ...s, x, y };
    });

    let svgHtml = '';

    // Connecting lines (segments), glowing green→teal once traversed
    for (let i = 0; i < pts.length - 1; i++) {
        const a = pts[i], b = pts[i+1];
        const traversed = state.step > a.id;
        svgHtml += `<line x1="${a.x}" y1="${a.y}" x2="${b.x}" y2="${b.y}"
            stroke="${traversed ? 'url(#lineGrad)' : 'rgba(255,255,255,0.08)'}"
            stroke-width="${traversed ? 2.5 : 1.5}" ${traversed ? 'class="animate-pulse"' : ''} />`;
    }

    svgHtml += `<defs>
        <linearGradient id="lineGrad" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#50BC81"/>
            <stop offset="100%" stop-color="#02CCFE"/>
        </linearGradient>
        <radialGradient id="nodeGlow"><stop offset="0%" stop-color="#50BC81" stop-opacity="0.8"/><stop offset="100%" stop-color="#50BC81" stop-opacity="0"/></radialGradient>
    </defs>`;

    // Background scatter stars (purely decorative, deterministic per render)
    for (let i = 0; i < 24; i++) {
        const sx = (i * 137.5) % W;
        const sy = 15 + ((i * 47) % (H - 30));
        const r = 0.6 + (i % 3) * 0.3;
        svgHtml += `<circle cx="${sx}" cy="${sy}" r="${r}" fill="rgba(255,255,255,0.15)"/>`;
    }

    // Nodes — visited (or current) steps are clickable, non-visited are inert
    pts.forEach(p => {
        const status = state.step === p.id ? 'active' : (state.step > p.id ? 'completed' : 'pending');
        const clickable = isStepClickable(p.id);
        const fill = status === 'pending' ? 'rgba(255,255,255,0.08)' : (status === 'active' ? '#50BC81' : 'rgba(80,188,129,0.7)');
        const r = status === 'active' ? 11 : 8;
        svgHtml += `<g class="${clickable?'node-clickable':''}" ${clickable?`data-step-id="${p.id}" style="cursor:pointer"`:''}>`;
        svgHtml += `<circle cx="${p.x}" cy="${p.y}" r="24" fill="transparent"/>`; // generous invisible hit-area
        if (status === 'active') {
            svgHtml += `<circle cx="${p.x}" cy="${p.y}" r="22" fill="url(#nodeGlow)" class="animate-pulse"/>`;
        }
        svgHtml += `<circle cx="${p.x}" cy="${p.y}" r="${r}" fill="${fill}" stroke="${status==='pending'?'rgba(255,255,255,0.15)':(clickable&&status!=='active'?'rgba(80,188,129,0.9)':'none')}" stroke-width="1.5"/>`;
        if (status === 'completed') {
            svgHtml += `<path d="M${p.x-3.5} ${p.y} l2.5 2.5 l4.5 -5" stroke="#111625" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>`;
        } else if (status === 'active') {
            svgHtml += `<text x="${p.x}" y="${p.y+3.5}" text-anchor="middle" font-size="10" font-weight="700" fill="#1A2039">${p.id}</text>`;
        }
        const labelColor = status === 'pending' ? 'rgba(255,255,255,0.25)' : 'rgba(255,255,255,0.85)';
        svgHtml += `<text x="${p.x}" y="${p.y - 20 < 12 ? p.y + 24 : p.y - 18}" text-anchor="middle" font-size="10" font-weight="700" letter-spacing="0.5" fill="${labelColor}" style="text-transform:uppercase">${p.title}</text>`;
        svgHtml += `</g>`;
    });

    svg.innerHTML = svgHtml;
    svg.querySelectorAll('[data-step-id]').forEach(g => {
        g.onclick = () => goToStep(parseInt(g.getAttribute('data-step-id')));
    });
}

let __lastTotalSar = 0;
function updateMiniTotal(){
    const el = document.getElementById('mini-total');
    if (!el) return;
    const newSar = getPriceSar();
    el.textContent = getPrice();
    if (newSar !== __lastTotalSar) {
        el.classList.remove('animate-count-pop');
        void el.offsetWidth; // restart animation
        el.classList.add('animate-count-pop');
        __lastTotalSar = newSar;
    }
    // Drive the radial gauge — dasharray is fixed at 326.7 (2πr, r=52), offset shrinks as progress grows.
    const gaugeArc = document.getElementById('gauge-arc');
    const stepLabel = document.getElementById('gauge-step-label');
    if (gaugeArc) {
        const total = totalSteps();
        const frac = total > 1 ? (state.step - 1) / (total - 1) : 0;
        const circumference = 326.7;
        gaugeArc.style.strokeDashoffset = String(circumference * (1 - frac));
    }
    if (stepLabel) stepLabel.textContent = `Step ${state.step} of ${totalSteps()}`;
}

// ── Live price lookups ──
function getVisaPrice(){
    if (!state.formData.choices.visa) return 0;
    return state.formData.visa ? (parseFloat(state.formData.visa.price) || 0) : 0;
}
function getTransportPrice(){
    if (!state.formData.choices.transport) return 0;
    return (state.formData.transport || []).reduce((sum, leg) => sum + (parseFloat(leg.price) || 0), 0);
}
function getZiarahPrice(){
    if (!state.formData.choices.ziarah) return 0;
    return (state.formData.ziarah || []).reduce((sum, z) => sum + (parseFloat(z.price) || 0) + (parseFloat(z.moyallemPriceSar) || 0), 0);
}
function getMoyallemPrice(){
    if (!state.formData.choices.moyallem) return 0;
    return (state.formData.moyallem || []).reduce((sum, m) => sum + (parseFloat(m.price) || 0), 0);
}
function getMealPrice(){
    if (!state.formData.choices.meal || !state.formData.meal) return 0;
    return (parseFloat(state.formData.meal.price) || 0) * state.formData.travelers.no_of_pax.adult;
}
function getAccommodationPrice(){
    if (!state.formData.choices.hotel) return 0;
    // Real per-night room prices × total trip duration (nights) — replaces
    // the old flat BASE_PRICING.hotel estimate now that actual hotel/room
    // rates come from our own database.
    return (state.formData.accommodation || []).reduce((sum, a) => sum + (parseFloat(a.price) || 0), 0) * state.formData.duration;
}

function getPrice(){
    const r=state.exchangeRates[state.currency]||1;
    const sym=state.currencySymbols[state.currency]||'';
    const base = getPriceSar();
    return sym+' '+(base*r).toLocaleString(undefined,{maximumFractionDigits:0});
}
function getPriceSar(){
    const f=state.formData;
    // Visa is required for every traveler regardless of age — adults,
    // children, and infants all need one. The service-level package tier
    // is paid per adult only.
    const pax = f.travelers.no_of_pax;
    const totalTravelers = (pax.adult||0) + (pax.child||0) + (pax.infant||0);
    let base = getVisaPrice() * Math.max(1, totalTravelers);
    base += parseFloat(state.serviceLevels.find(l => l.name === f.serviceLevel)?.price || 0) * Math.max(1, pax.adult);

    // NOTE: Flight fare is intentionally NOT added — it's confirmed later
    // by the team, per the "Flight fare will be updated" note shown in
    // the Flight step.
    if (f.choices.hotel) base += getAccommodationPrice();
    base += getTransportPrice();
    base += getZiarahPrice();
    base += getMoyallemPrice();
    // Meal price is already per-adult (see getMealPrice).
    base += getMealPrice();
    return base;
}

function renderStepContent(isStepChange = false){
    const c=document.getElementById('step-container');
    const prev=document.getElementById('prev-step');
    const next=document.getElementById('next-step');
    const stepKey = getCurrentStepKey();
    const stepPos = state.step;
    const stepCount = totalSteps();
    prev.disabled=stepPos===1;
    prev.className=stepPos===1?'flex items-center gap-2 font-bold text-white/10 cursor-not-allowed':'flex items-center gap-2 font-bold text-white/60 hover:text-white transition-colors';
    // Summary step has its own inline "Confirm & Preview" button — hide the generic Next button there.
    next.style.display = stepKey==='summary' ? 'none' : '';
    next.innerHTML=`Next <i data-lucide="chevron-right" class="w-5 h-5"></i>`;

    let html='';
    switch(stepKey){
        case 'travelers': html=`<div class="space-y-8">
            <div><label class="text-sm font-medium text-white/60 mb-2 block">Lead Traveler Name</label>
            <input type="text" id="traveler-name" value="${state.formData.travelers.name}" placeholder="Full name as per passport" class="w-full bg-white/5 border border-white/10 rounded-xl px-6 py-4 text-lg focus:border-secondary focus:outline-none transition-colors"></div>
            <div class="grid md:grid-cols-3 gap-6">${[['Adults','adult'],['Children','child'],['Infants','infant']].map(([l,key])=>`
            <div class="bg-white/5 rounded-xl p-6 text-center border border-white/10">
                <span class="text-sm text-white/60 uppercase tracking-wider block mb-3">${l}</span>
                <div class="flex justify-center items-center gap-6">
                    <button class="traveler-dec w-9 h-9 rounded-full border border-white/20 hover:border-secondary flex items-center justify-center transition-colors" data-field="${key}"><i data-lucide="minus" class="w-4 h-4"></i></button>
                    <span class="text-2xl font-bold w-8 text-center">${state.formData.travelers.no_of_pax[key]}</span>
                    <button class="traveler-inc w-9 h-9 rounded-full border border-white/20 hover:border-secondary flex items-center justify-center transition-colors" data-field="${key}"><i data-lucide="plus" class="w-4 h-4"></i></button>
                </div>
                <p class="text-xs text-white/30 mt-2">${l==='Adults'?'12+ years':l==='Children'?'2-11 years':'Under 2'}</p>
            </div>`).join('')}</div>
            <div class="border-t border-white/10 pt-6">
              <h3 class="font-semibold mb-4 text-white/80">Trip Duration</h3>
              <div class="grid grid-cols-4 gap-3 mb-3">${[7,10,14,21].map(d=>{
                  const sel = state.formData.duration===d;
                  return `
              <button class="duration-btn hex-card text-center relative ${sel?'selected':''}" data-duration="${d}">
                  ${sel?'<span class="absolute top-2 right-2 w-4 h-4 rounded-full bg-secondary flex items-center justify-center"><i data-lucide="check" class="w-2.5 h-2.5 text-primary"></i></span>':''}
                  <span class="text-xl font-bold block ${sel?'text-white':'text-white/70'}">${d}</span><span class="text-xs ${sel?'text-secondary':'text-white/40'}">Days</span>
              </button>`;}).join('')}</div>
              <div class="flex items-center gap-3">
                <span class="text-xs text-white/30 uppercase tracking-wider">Or enter custom</span>
                <div class="h-px flex-1 bg-white/10"></div>
              </div>
              <div class="mt-3 flex items-center gap-3">
                <input type="number" id="custom-duration" min="3" max="60" placeholder="e.g. 12" value="${![7,10,14,21].includes(state.formData.duration)?state.formData.duration:''}"
                       class="w-28 bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-center focus:border-secondary focus:outline-none">
                <span class="text-sm text-white/40">days total</span>
              </div>
              <div class="grid md:grid-cols-2 gap-6 mt-6">
                  <div class="bg-white/5 rounded-xl p-5 border border-white/10">
                      <div class="flex justify-between mb-3"><span class="text-sm text-white/60">Makkah Nights</span><span class="text-secondary font-bold">${state.formData.makkahStay} nights</span></div>
                      <input type="range" id="makkah-range" min="1" max="${state.formData.duration-1}" value="${state.formData.makkahStay}" class="w-full accent-secondary">
                  </div>
                  <div class="bg-white/5 rounded-xl p-5 border border-white/10">
                      <div class="flex justify-between mb-3"><span class="text-sm text-white/60">Madinah Nights</span><span class="text-secondary font-bold">${state.formData.madinahStay} nights</span></div>
                      <input type="range" id="madinah-range" min="1" max="${state.formData.duration-1}" value="${state.formData.madinahStay}" class="w-full accent-secondary">
                  </div>
              </div>
            </div></div>`; break;

        // ── "Your Choice" — Step 2: what does this traveler want included? ──
        case 'package':
            const pax = state.formData.travelers.no_of_pax;
            const totalAdults = pax.adult;
            const c = state.formData.choices;
            const mealBlocked = totalAdults < (state.meals[0]?.min_adults_required || 10);
            html = `<div class="space-y-8">
              <div>
                <h3 class="font-bold text-white mb-1">What do you need for this journey?</h3>
                <p class="text-white/40 text-xs mb-4">Toggle what you want — we'll only show the steps that matter to you.</p>
                <div class="grid md:grid-cols-2 gap-4">
                  ${[
                    ['visa','Visa','file-text','Entry visa processing'],
                    ['flight','Flight','plane','Air travel arrangement'],
                    ['hotel','Hotel','hotel','Accommodation in Makkah & Madinah'],
                    ['transport','Transport','car','Ground transport between cities'],
                    ['ziarah','Ziarah','landmark','Guided historical site visits'],
                    ['moyallem','Moyallem','users','Spiritual guide services'],
                    ['meal','Meal','utensils','Daily meal plan'],
                  ].map(([key,label,icon,desc])=>{
                    const on = !!c[key];
                    return `
                  <button class="choice-toggle-btn hex-card text-left relative ${on?'selected':''}" data-choice="${key}">
                    ${on?'<span class="absolute top-3 right-3 w-5 h-5 rounded-full bg-secondary flex items-center justify-center"><i data-lucide="check" class="w-3 h-3 text-primary"></i></span>':''}
                    <i data-lucide="${icon}" class="w-6 h-6 ${on?'text-secondary':'text-white/40'} mb-2"></i>
                    <span class="font-bold block ${on?'text-white':'text-white/70'}">${label}</span>
                    <span class="text-xs text-white/40">${desc}</span>
                  </button>`;}).join('')}
                </div>
              </div>

              ${c.hotel ? `<div>
                <h3 class="font-bold text-white mb-1">Hotel Rating</h3>
                <p class="text-white/40 text-xs mb-4">Sets which hotels appear in the Accommodation step.</p>
                <div class="grid grid-cols-3 gap-4">${['3-Star','4-Star','5-Star'].map(h=>{
                    const sel = state.formData.hotelCategory===h;
                    return `
                <button class="distance-btn hex-card text-center relative ${sel?'selected':''}" data-hotel="${h}">
                    ${sel?'<span class="absolute top-3 right-3 w-5 h-5 rounded-full bg-secondary flex items-center justify-center"><i data-lucide="check" class="w-3 h-3 text-primary"></i></span>':''}
                    <i data-lucide="star" class="w-6 h-6 ${sel?'text-secondary':'text-white/40'} mx-auto mb-2"></i>
                    <span class="font-semibold block ${sel?'text-white':'text-white/70'}">${h}</span>
                </button>`;}).join('')}</div>
              </div>` : ''}

              ${c.meal ? `<div>
                <h3 class="font-bold text-white mb-1">Meal Plan</h3>
                <p class="text-white/40 text-xs mb-4">Requires at least ${state.meals[0]?.min_adults_required || 10} adults to book.</p>
                ${mealBlocked ? `
                <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 text-sm text-amber-300 flex items-start gap-2">
                  <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                  <span>To avail a meal plan, you need at least ${state.meals[0]?.min_adults_required || 10} adult travelers. You currently have ${totalAdults}.</span>
                </div>` : `
                <div class="grid grid-cols-3 gap-4">${(state.meals[0]?.price_tiers||[]).map(t=>{
                    const sel = state.formData.meal && state.formData.meal.tier === t.tier;
                    return `
                <button class="meal-tier-btn hex-card text-center relative ${sel?'selected':''}" data-tier="${t.tier}" data-price="${t.price}" data-mealsysid="${state.meals[0]?.sys_id||''}" data-mealname="${(state.meals[0]?.name||'').replace(/"/g,'&quot;')}">
                    ${sel?'<span class="absolute top-3 right-3 w-5 h-5 rounded-full bg-secondary flex items-center justify-center"><i data-lucide="check" class="w-3 h-3 text-primary"></i></span>':''}
                    <span class="font-semibold block ${sel?'text-white':'text-white/70'}">${t.tier}</span>
                    <span class="text-xs ${sel?'text-secondary':'text-white/40'} block mt-1">SR ${t.price}/person</span>
                </button>`;}).join('')}</div>`}
              </div>` : ''}

              <div>
                <h3 class="font-bold text-white mb-1">Service Level</h3>
                <p class="text-white/40 text-xs mb-4">Overall package tier.</p>
                ${state.serviceLevels.length === 0 ? `<p class="text-white/20 text-xs">Loading service levels...</p>` : `
                <div class="grid md:grid-cols-3 gap-4">${state.serviceLevels.map(lvl=>{
                    const p = lvl.name, price = parseFloat(lvl.price)||0;
                    const sel = state.formData.serviceLevel===p;
                    return `
                <button class="service-level-btn hex-card text-center relative ${sel?'selected':''}" data-level="${p}">
                    ${sel?'<span class="absolute top-3 right-3 w-5 h-5 rounded-full bg-secondary flex items-center justify-center"><i data-lucide="check" class="w-3 h-3 text-primary"></i></span>':''}
                    <i data-lucide="${p==='Luxury'?'gem':p==='Premium'?'star':'package'}" class="w-6 h-6 ${sel?'text-secondary':'text-white/40'} mx-auto mb-2"></i>
                    <span class="font-semibold block ${sel?'text-white':'text-white/70'}">${p}</span>
                    <span class="text-xs ${sel?'text-secondary':'text-white/40'} block mt-1">SR ${price}</span>
                </button>`;}).join('')}</div>`}
              </div>
            </div>`; break;


        case 'flight':
            if(!state.flights.length){
                html=`<div class="text-center py-12 text-white/30"><i data-lucide="plane" class="w-10 h-10 mx-auto mb-3 opacity-30"></i><p>Loading flight fares...</p></div>`;
                break;
            }
            // Fare Type (Flexible/Fixed) is no longer surfaced to the traveler —
            // that distinction is decided by the team later. We just need a
            // source of Direct/Connecting reference data, so the first
            // admin-defined flight fare is used implicitly.
            const selConnType = state.formData.flight ? state.formData.flight.connection_type : null;
            const currentFare = state.flights[0];
            // "Both" is a synthetic third option (not admin-managed, no price)
            // meaning "either works for me" — added client-side only.
            const connOptions = [...(currentFare.type_prices||[]), { type: 'Both', price: null, synthetic: true }];
            html=`<div class="space-y-8">
            <div><h3 class="font-semibold mb-4 text-white/80">Connection Type</h3>
            <div class="grid grid-cols-3 gap-4">${connOptions.map(p=>{
                const sel = selConnType===p.type;
                return `
            <button class="conn-type-btn hex-card text-center relative ${sel?'selected':''}" data-conn="${p.type}">
                ${sel?'<span class="absolute top-3 right-3 w-6 h-6 rounded-full bg-secondary flex items-center justify-center"><i data-lucide="check" class="w-3.5 h-3.5 text-primary"></i></span>':''}
                <i data-lucide="${p.type==='Direct'?'move-right':p.type==='Connecting'?'route':'shuffle'}" class="w-7 h-7 ${sel?'text-secondary':'text-white/40'} mx-auto mb-2"></i>
                <span class="block font-semibold ${sel?'text-white':'text-white/70'}">${p.type}</span>
            </button>`;}).join('')}</div>
            <div class="bg-secondary/5 border border-secondary/20 rounded-xl p-4 mt-3 text-sm text-white/60 flex items-start gap-2">
                <i data-lucide="info" class="w-4 h-4 text-secondary shrink-0 mt-0.5"></i>
                <span>Flight fare will be updated by our team once availability is confirmed — it isn't included in your estimate below yet.</span>
            </div></div>

            <div>
              <label class="text-sm text-white/60 block mb-2">Preferred Departure Date</label>
              <div class="relative">
                <i data-lucide="calendar-days" class="w-5 h-5 text-secondary absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none z-10"></i>
                <input type="date" id="flight-date" class="date-input-clean w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-4 text-white focus:border-secondary focus:outline-none" min="${new Date(Date.now()+15*86400000).toISOString().split('T')[0]}" value="${state.formData.flight&&state.formData.flight.flight_date||''}">
              </div>
            </div>
            </div>`; break;

        case 'accommodation':
            if (!state.hotelsFetchAttempted && !state.hotelsLoading) {
                // Kick off the fetch, but defer it to the next tick so it never
                // runs inside this synchronous render call (avoids any
                // re-entrant render loop) — the spinner below shows immediately
                // in the meantime since hotelsLoading flips true right here.
                state.hotelsLoading = true;
                setTimeout(() => fetchHotels(state.selectedHotelCity), 0);
            }
            const selectedRoomIds = (state.formData.accommodation||[]).map(a=>a.room_type_sys_id);
            const cityTabs = ['Makkah','Madinah','Jeddah'];
            const cityTabsHtml = `<div class="flex gap-2 mb-4">${cityTabs.map(c => `
                <button onclick="selectHotelCity('${c}')" class="px-4 py-2 rounded-xl text-sm font-medium border transition-all ${state.selectedHotelCity===c?'border-secondary bg-secondary/10 text-secondary':'border-white/10 bg-white/5 text-white/50 hover:border-white/30'}">${c}</button>
            `).join('')}</div>`;

            // Real-time capacity check — total booked room capacity vs. actual
            // travelers (adults + children; infants typically don't need
            // their own bed/seat). Purely informational, doesn't block selection.
            const accPax = state.formData.travelers.no_of_pax;
            const travelersNeedingSpace = (accPax.adult||0) + (accPax.child||0);
            const bookedCapacity = (state.formData.accommodation||[]).reduce((sum,a)=>sum+(a.max_adults||0)+(a.max_children||0),0);
            const capacityWarningHtml = (state.formData.accommodation||[]).length && bookedCapacity < travelersNeedingSpace
                ? `<div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-sm text-red-300 flex items-start gap-2">
                     <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                     <span>You need more rooms — your team has ${travelersNeedingSpace} member${travelersNeedingSpace===1?'':'s'}, but the ${state.formData.accommodation.length} room${state.formData.accommodation.length===1?'':'s'} you've booked fit only ${bookedCapacity}.</span>
                   </div>`
                : '';

            if (state.hotelsLoading) {
                html = `${cityTabsHtml}<div class="text-center py-12 text-white/30"><i data-lucide="hotel" class="w-10 h-10 mx-auto mb-3 opacity-30 animate-pulse"></i><p>Loading hotels...</p></div>`;
                break;
            }
            if (state.hotelsFetchFailed) {
                // Don't auto-retry on every render — the API call already failed once
                // (often a CORS/network issue), so retrying silently just re-fires the
                // same failing request forever. Show a clear error and a manual retry.
                html = `${cityTabsHtml}<div class="text-center py-12">
                    <i data-lucide="wifi-off" class="w-10 h-10 mx-auto mb-3 text-white/20"></i>
                    <p class="text-white/40 text-sm mb-4">Couldn't load hotels right now. This is usually temporary.</p>
                    <button class="hotel-retry-btn inline-flex items-center gap-2 bg-secondary hover:bg-emerald text-primary font-semibold px-5 py-2.5 rounded-xl text-sm">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i> Try Again
                    </button>
                </div>`;
                break;
            }
            html=`<div class="space-y-6">
            ${cityTabsHtml}
            ${capacityWarningHtml}
            <div class="flex items-center justify-between flex-wrap gap-2">
              <div class="flex items-center gap-2 text-white/50 text-sm"><i data-lucide="hotel" class="w-4 h-4 text-secondary"></i> Hotels in ${state.selectedHotelCity}</div>
              <button class="hotel-refresh-btn text-xs text-secondary hover:underline flex items-center gap-1"><i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Refresh</button>
            </div>
            ${!state.hotels.length ? `
            <div class="glass-card p-8 text-center text-white/30"><i data-lucide="hotel-off" class="w-8 h-8 mx-auto mb-2 opacity-30"></i><p class="text-sm">No hotels found in ${state.selectedHotelCity} right now.</p></div>
            ` : `
            <div class="space-y-4">${state.hotels.map(h=>{
                const expanded = state.expandedHotelSysId === h.sys_id;
                const roomTypes = state.roomTypeCache[h.sys_id];
                const hasSelectedRoom = (state.formData.accommodation||[]).some(a=>a.hotel_sys_id===h.sys_id);
                const thumb = (h.images && h.images[0]) ? `${window.BASE_URL}/${h.images[0]}` : (h.image_urls && h.image_urls[0]) || null;
                const dist = h.distance_info;
                return `
            <div class="hex-card relative ${hasSelectedRoom?'selected':''}">
                <div onclick="toggleHotelExpand('${h.sys_id}')" class="cursor-pointer flex gap-4">
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

                ${expanded ? `<div class="mt-4 pt-4 border-t border-white/10">
                    ${!roomTypes ? `<p class="text-xs text-white/30 py-4 text-center">Loading room types...</p>` :
                      roomTypes.length === 0 ? `<p class="text-xs text-white/20 py-4 text-center">No room types available for this hotel yet.</p>` :
                      `<div class="space-y-3">${roomTypes.map(rt => {
                        const roomSel = selectedRoomIds.includes(rt.sys_id);
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
                                const boardSel = (state.formData.accommodation||[]).some(a=>a.board_type_sys_id===bt.sys_id && a.room_type_sys_id===rt.sys_id);
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
            <div class="bg-secondary/5 border border-secondary/20 rounded-xl p-4 text-sm text-white/60">
                <i data-lucide="info" class="w-4 h-4 text-secondary inline mr-2"></i>
                Tap a hotel, then a room type, then a service category to add it to your journey.
            </div>
            </div>`; break;

        // ── Ziarah — guided site visits. Selecting one asks: need transport? need Moyallem? ──
        case 'ziarah':
            if (!state.ziarahList.length) {
                html = `<div class="text-center py-12 text-white/30"><i data-lucide="landmark" class="w-10 h-10 mx-auto mb-3 opacity-30"></i><p>Loading Ziarah tours...</p></div>`;
                break;
            }
            const zSel = state.formData.ziarah || [];
            html = `<div class="space-y-6">
              <p class="text-white/40 text-sm">Select the guided site visits you'd like to include.</p>
              <div class="space-y-4">
              ${state.ziarahList.map(z => {
                  const picked = zSel.find(x => x.id === z.id);
                  const isOn = !!picked;
                  return `
              <div class="hex-card relative ${isOn?'selected':''}">
                <div onclick="toggleZiarahPick(${z.id},'${z.name.replace(/'/g,"\\'")}',${z.price})" class="cursor-pointer flex items-start justify-between gap-3">
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                      <h3 class="font-bold ${isOn?'text-white':'text-white/80'}">${z.name}</h3>
                      ${isOn?'<span class="w-5 h-5 rounded-full bg-secondary flex items-center justify-center shrink-0"><i data-lucide="check" class="w-3 h-3 text-primary"></i></span>':''}
                    </div>
                    <p class="text-xs text-white/40 mt-1">${z.description||''}</p>
                    <p class="text-[11px] text-white/30 mt-1">${z.possible_duration||''} · <span class="text-secondary font-semibold">SR ${z.price}</span></p>
                    ${(z.routes&&z.routes.length) ? `<p class="text-[11px] text-white/25 mt-1 flex items-center gap-1"><i data-lucide="route" class="w-3 h-3"></i> ${z.routes.map(r=>r.label).join(' · ')}</p>` : ''}
                  </div>
                </div>

                ${isOn ? `<div class="mt-4 pt-4 border-t border-white/10 space-y-4">
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
                        <button onclick="event.stopPropagation(); pickZiarahRoute(${z.id},'${z.name.replace(/'/g,"\\'")}','${r.sys_id}','${r.label.replace(/'/g,"\\'")}')"
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
                      <button onclick="event.stopPropagation(); pickZiarahMoyallemCategory(${z.id},'${cp.category}',${cp.price})"
                        class="py-2 rounded-lg border text-center transition-all ${picked.moyallemCategory===cp.category?'border-secondary bg-secondary/10':'border-white/10 bg-white/5 hover:border-white/30'}">
                        <span class="block text-xs font-semibold ${picked.moyallemCategory===cp.category?'text-secondary':'text-white/70'}">${cp.category}</span>
                        <span class="block text-[10px] mt-0.5 ${picked.moyallemCategory===cp.category?'text-secondary':'text-white/30'}">SR ${cp.price}</span>
                      </button>`).join('') || '<p class="text-[11px] text-white/20 col-span-3">No Moyallem categories configured yet.</p>'}
                    </div>` : ''}
                  </div>` : ''}
                </div>` : ''}
              </div>`;
              }).join('')}
              </div>
            </div>`; break;

        case 'transport':
            const vehicleIcons={Car:'car',HiAce:'truck',Coaster:'bus',Bus:'bus',Minibus:'bus','GMC/JMC':'car',H1:'car',Staria:'car',Train:'train-front'};
            const routeLabel=r=>[r.origin, ...(r.destinations||[])].join(' → ');
            if(!state.transportRoutes.length){
                html=`<div class="text-center py-12 text-white/30"><i data-lucide="map" class="w-10 h-10 mx-auto mb-3 opacity-30"></i><p>Loading transport routes...</p></div>`;
                break;
            }
            const legs = state.formData.transport || [];
            const activeRouteId = state.transportPickerRouteId || null;
            const fromZiarah = state.formData.ziarah.some(z => z.wantsTransport && z.routeId === activeRouteId);
            html=`<div class="space-y-8">
            ${fromZiarah ? `<div class="bg-secondary/5 border border-secondary/20 rounded-xl p-4 text-sm text-secondary flex items-start gap-2">
              <i data-lucide="landmark" class="w-4 h-4 shrink-0 mt-0.5"></i>
              <span>This route was pre-selected from your Ziarah choice — just pick a vehicle below to add it.</span>
            </div>` : ''}
            ${legs.length ? `
            <div>
              <h3 class="font-semibold mb-3 text-white/80">Your Transport Legs</h3>
              <div class="space-y-2">${legs.map((leg,i)=>`
                <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-secondary/30 bg-secondary/5">
                  <span class="text-sm flex items-center gap-2">
                    <i data-lucide="${vehicleIcons[leg.vehicle_type]||'car'}" class="w-4 h-4 text-secondary"></i>
                    <strong class="text-white">${leg.route_name}</strong>
                    <span class="text-white/40">— ${leg.vehicle_type}</span>
                  </span>
                  <span class="flex items-center gap-3">
                    <span class="text-secondary text-xs font-bold">SR ${leg.price}</span>
                    <button class="remove-leg-btn text-white/30 hover:text-red-400" data-idx="${i}"><i data-lucide="x" class="w-4 h-4"></i></button>
                  </span>
                </div>`).join('')}</div>
            </div>` : `
            <div class="bg-secondary/5 border border-secondary/20 rounded-xl p-4 text-sm text-white/50">
              <i data-lucide="info" class="w-4 h-4 text-secondary inline mr-2"></i>
              You can add multiple legs — e.g. Airport → Makkah by GMC, then Makkah → Madinah by Bus.
            </div>`}

            <div>
              <h3 class="font-semibold mb-4 text-white/80">${legs.length ? 'Add Another Leg' : 'Select a Route'}</h3>
              <div class="grid gap-2">${state.transportRoutes.map(r=>{
                const alreadyAdded = legs.some(l=>l.route_sys_id===r.sys_id);
                const isPicking = activeRouteId===r.sys_id;
                return `
              <button class="route-btn flex items-center justify-between px-5 py-3 rounded-xl border text-left transition-all duration-200 hover:scale-[1.015] active:scale-[0.985] ${isPicking?'border-secondary bg-secondary/5 scale-[1.015] shadow-lg shadow-secondary/10':'border-white/10 bg-white/5 hover:border-secondary/50'} ${alreadyAdded?'opacity-50':''}" data-id="${r.sys_id}" data-name="${routeLabel(r).replace(/"/g,'&quot;')}">
                <span class="font-medium flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-secondary"></i> ${routeLabel(r)} ${alreadyAdded?'<span class=\"text-[10px] text-secondary ml-1\">(added)</span>':''}</span>
                ${isPicking?'<i data-lucide="chevron-down" class="w-4 h-4 text-secondary"></i>':''}
              </button>`;}).join('')}</div>
            </div>

            ${activeRouteId?`
            <div><h3 class="font-semibold mb-4 text-white/80">Select Vehicle for this Leg</h3>
            <div class="grid grid-cols-3 gap-3">${(()=>{
                const route=state.transportRoutes.find(r=>r.sys_id===activeRouteId);
                const vinfo=(route&&route.vehicle_options)?route.vehicle_options:[];
                if(!vinfo.length) return `<p class="col-span-full text-center text-white/30 text-sm py-4">No vehicles priced for this route yet.</p>`;
                return vinfo.map(v=>{
                    const vt = state.vehicleTypesById[v.vehicle_type_sys_id];
                    const vName = vt ? vt.name : v.vehicle_type_sys_id;
                    const vIcon = vt ? (vt.icon || vehicleIcons[vName] || 'car') : 'car';
                    const cap = vt && vt.capacities;
                    return `
                <button class="car-btn hex-card text-center" data-car="${vName}" data-carsysid="${v.vehicle_type_sys_id}" data-price="${v.price}">
                    <i data-lucide="${vIcon}" class="w-5 h-5 mx-auto mb-1 text-white/50"></i>
                    <span class="text-sm block text-white/70">${vName}</span>
                    ${cap ? `<span class="text-[10px] block mt-0.5 text-white/30">${cap.seat||0} seats · ${cap.luggage||0} luggage</span>` : ''}
                    <span class="text-xs block mt-1 text-secondary">SR ${v.price}</span>
                </button>`;}).join('');
            })()}</div></div>`:''}
            </div>`; break;

        // ── Moyallem (general spiritual guide services, separate from any
        // Ziarah-specific Moyallem picked during the Ziarah step) ──
        case 'moyallem':
            if (!state.moyallemServices.length) {
                html = `<div class="text-center py-12 text-white/30"><i data-lucide="users" class="w-10 h-10 mx-auto mb-3 opacity-30"></i><p>Loading Moyallem services...</p></div>`;
                break;
            }
            html = `<div class="space-y-6">
              <p class="text-white/40 text-sm">Select one or more services, then choose a category for each.</p>
              <div class="grid md:grid-cols-2 gap-4">
              ${state.moyallemServices.map(m => {
                  const picked = state.formData.moyallem.find(x => x.sys_id === m.sys_id);
                  const isOn = !!picked;
                  return `
              <div class="hex-card relative ${isOn?'selected':''}">
                <div onclick="document.getElementById('moy-toggle-${m.sys_id}').click()" class="cursor-pointer flex items-start gap-3">
                  <div class="w-9 h-9 bg-secondary/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="${m.icon||'star'}" class="w-4 h-4 text-secondary"></i></div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                      <h3 class="font-bold text-sm leading-tight">${m.name}</h3>
                      <div class="shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all ${isOn?'border-secondary bg-secondary':'border-white/20'}">
                        ${isOn?'<i data-lucide="check" class="w-3 h-3 text-primary"></i>':''}</div>
                    </div>
                    <p class="text-white/40 text-xs mt-1 leading-relaxed">${m.description||''}</p>
                  </div>
                </div>
                <button id="moy-toggle-${m.sys_id}" class="moyallem-service-btn hidden" data-id="${m.sys_id}" data-name="${m.name.replace(/"/g,'&quot;')}"></button>
                ${isOn ? `<div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-white/10">
                  ${(m.category_prices||[]).map(cp => {
                      const catSel = picked.category === cp.category;
                      return `
                  <button class="moyallem-category-btn px-2 py-2 rounded-lg border text-center transition-all ${catSel?'border-secondary bg-secondary/10':'border-white/10 bg-white/5 hover:border-white/30'}" data-id="${m.sys_id}" data-category="${cp.category}" data-price="${cp.price}">
                    <span class="block text-[11px] font-semibold ${catSel?'text-secondary':'text-white/70'}">${cp.category}</span>
                    <span class="block text-[10px] mt-0.5 ${catSel?'text-secondary':'text-white/30'}">SR ${cp.price}</span>
                  </button>`;}).join('')}
                </div>` : ''}
              </div>`;
              }).join('')}
              </div>
              <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                <span class="text-sm text-white/40">Moyallem Total</span>
                <span class="font-bold text-secondary">SR ${state.formData.moyallem.reduce((a,m)=>a+(m.price||0),0)}</span>
              </div>
            </div>`; break;

        case 'summary':
            const legsSummary = (state.formData.transport||[]).map(l=>`${l.route_name} (${l.vehicle_type})`).join(', ') || 'Not selected';
            const hotelsSummary = (state.formData.accommodation||[]).map(a=>`${a.hotel_name} (${a.room_name})`).join(', ') || (stepIsVisible('accommodation') ? 'Not selected' : null);
            const ziarahSummary = (state.formData.ziarah||[]).map(z=>z.name).join(', ') || (stepIsVisible('ziarah') ? 'Not selected' : null);
            const moyallemSummary = (state.formData.moyallem||[]).map(m=>`${m.name}${m.category?' ('+m.category+')':''}`).join(', ') || (stepIsVisible('moyallem') ? 'Not selected' : null);
            const mealSummary = state.formData.meal ? `${state.formData.meal.tier}` : (state.formData.choices.meal ? 'Not selected' : null);
            const waText=encodeURIComponent(`Assalamu Alaikum! I would like to book an Umrah package.\n\nName: ${state.formData.travelers.name}\nService Level: ${state.formData.serviceLevel}\nDuration: ${state.formData.duration} days (Makkah ${state.formData.makkahStay} + Madinah ${state.formData.madinahStay})\nTravelers: ${state.formData.travelers.no_of_pax.adult} adult(s), ${state.formData.travelers.no_of_pax.child} children\nTransport: ${legsSummary}\nVisa: ${state.formData.visa?state.formData.visa.visa_type:'—'}\nEstimated Price: ${getPrice()}`);
            html=`<div class="space-y-6">
            <div class="bg-white/5 border border-secondary/30 rounded-2xl overflow-hidden">
                <div class="p-6 bg-secondary/10 border-b border-secondary/20 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold">Booking Summary</h2>
                        <p class="text-white/40 text-sm mt-1">Review your selections before confirming</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-white/40 block uppercase">Estimated Total</span>
                        <p class="text-3xl font-bold text-secondary">${getPrice()}</p>
                    </div>
                </div>
                <div class="p-6 grid md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div class="flex gap-3 items-start"><i data-lucide="user" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Lead Traveler</p><p class="font-medium">${state.formData.travelers.name||'Not set'}</p></div></div>
                    <div class="flex gap-3 items-start"><i data-lucide="users" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Travelers</p><p class="font-medium">${state.formData.travelers.no_of_pax.adult} Adult(s), ${state.formData.travelers.no_of_pax.child} Child(ren), ${state.formData.travelers.no_of_pax.infant} Infant(s)</p></div></div>
                    ${state.formData.choices.visa?`<div class="flex gap-3 items-start"><i data-lucide="file-text" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Visa</p><p class="font-medium">${state.formData.visa?state.formData.visa.visa_type:'Not set'}</p></div></div>`:''}
                    <div class="flex gap-3 items-start"><i data-lucide="package" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Service Level</p><p class="font-medium">${state.formData.serviceLevel}</p></div></div>
                    ${stepIsVisible('flight')?`<div class="flex gap-3 items-start"><i data-lucide="plane" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Flight</p><p class="font-medium">${state.formData.flight?state.formData.flight.connection_type:'—'}</p></div></div>`:''}
                    <div class="flex gap-3 items-start"><i data-lucide="clock" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Duration</p><p class="font-medium">${state.formData.duration} Days</p></div></div>
                    <div class="flex gap-3 items-start"><i data-lucide="moon" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Stay Split</p><p class="font-medium">Makkah ${state.formData.makkahStay}n + Madinah ${state.formData.madinahStay}n</p></div></div>
                    ${hotelsSummary!==null?`<div class="flex gap-3 items-start"><i data-lucide="hotel" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Hotels</p><p class="font-medium">${hotelsSummary}</p></div></div>`:''}
                    ${stepIsVisible('transport')?`<div class="flex gap-3 items-start"><i data-lucide="car" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Transport</p><p class="font-medium">${legsSummary}</p></div></div>`:''}
                    ${ziarahSummary!==null?`<div class="flex gap-3 items-start"><i data-lucide="landmark" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Ziarah</p><p class="font-medium">${ziarahSummary}</p></div></div>`:''}
                    ${moyallemSummary!==null?`<div class="flex gap-3 items-start"><i data-lucide="users" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Moyallem</p><p class="font-medium">${moyallemSummary}</p></div></div>`:''}
                    ${mealSummary!==null?`<div class="flex gap-3 items-start"><i data-lucide="utensils" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i><div><p class="text-[10px] text-white/30 uppercase tracking-wider">Meal</p><p class="font-medium">${mealSummary}</p></div></div>`:''}
                </div>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-4">
              <h3 class="font-bold text-sm text-white/80">Contact Details</h3>
              <p class="text-xs text-white/40">Please provide at least one — phone or email — so we can share your booking preview.</p>
              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">WhatsApp Number</label>
                  <input type="tel" id="summary-phone" value="${state.formData.travelers.phone||''}" placeholder="+8801XXXXXXXXX" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none">
                </div>
                <div>
                  <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Email</label>
                  <input type="email" id="summary-email" value="${state.formData.travelers.email||''}" placeholder="you@example.com" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none">
                </div>
              </div>
            </div>

            <div class="bg-secondary/5 border border-secondary/20 rounded-xl p-4 text-sm text-white/60">
                <i data-lucide="info" class="w-4 h-4 text-secondary inline mr-2"></i>
                This is an estimate. Final price confirmed after agent review.
            </div>
            <div id="homepage-submit-msg" class="hidden px-4 py-3 rounded-xl text-sm font-medium"></div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <button id="confirm-book-btn" class="flex items-center justify-center gap-2 bg-secondary hover:bg-emerald text-primary font-semibold py-4 rounded-xl transition-all duration-300">
                  <i data-lucide="file-check" class="w-5 h-5"></i> Confirm & Preview
              </button>
              <a href="https://wa.me/<?= $miniBuilderWa ?>?text=${waText}" target="_blank"
                 class="flex items-center justify-center gap-2 border border-[#25D366] text-[#25D366] hover:bg-[#25D366] hover:text-white font-semibold py-4 rounded-xl transition-all duration-300">
                  <i data-lucide="message-circle" class="w-5 h-5"></i> WhatsApp
              </a>
              <a href="<?= BASE_URL ?>/pages/contact.php" class="flex items-center justify-center gap-2 border border-white/10 hover:border-secondary text-white/70 hover:text-secondary font-medium py-4 rounded-xl transition-all duration-300">
                  <i data-lucide="mail" class="w-5 h-5"></i> Email
              </a>
            </div></div>`; break;
    }
    const panel = c.closest('.hex-panel');
    // Height-lock transition: measure the panel's current height before the
    // swap, hold it explicitly (CSS can't transition to/from `auto`), replace
    // the content, then animate to the new content's natural height. This is
    // what stops short↔tall step changes (e.g. Duration → Package) from
    // visibly yanking the page.
    let startHeight = null;
    if (panel) startHeight = panel.getBoundingClientRect().height;

    c.innerHTML = `<div class="${isStepChange ? 'animate-step-in' : 'animate-select-update'}" key="${stepPos}">${html}</div>`;
    attachStepListeners();
    lucide.createIcons();
    saveState();
    updateMiniTotal();

    if (panel && startHeight !== null) {
        const endHeight = panel.scrollHeight; // natural height of the new content
        panel.style.height = startHeight + 'px';
        panel.style.overflow = 'hidden';
        // Force layout so the browser registers the start height before we
        // change it — otherwise the transition has nothing to animate from.
        void panel.offsetHeight;
        panel.style.transition = 'height 0.3s cubic-bezier(0.22,1,0.36,1)';
        requestAnimationFrame(() => { panel.style.height = endHeight + 'px'; });
        panel.addEventListener('transitionend', function onEnd(e){
            if (e.propertyName !== 'height') return;
            panel.style.height = '';
            panel.style.overflow = '';
            panel.style.transition = '';
            panel.removeEventListener('transitionend', onEnd);
        });
    }
}

function attachStepListeners(){
    document.getElementById('traveler-name')?.addEventListener('input',e=>{state.formData.travelers.name=e.target.value;saveState();});
    document.querySelectorAll('.traveler-inc').forEach(b=>b.onclick=()=>{let f=b.getAttribute('data-field');state.formData.travelers.no_of_pax[f]++;renderStepContent();});
    document.querySelectorAll('.traveler-dec').forEach(b=>b.onclick=()=>{let f=b.getAttribute('data-field');state.formData.travelers.no_of_pax[f]=Math.max(f==='adult'?1:0,state.formData.travelers.no_of_pax[f]-1);renderStepContent();});

    // "Your Choice" toggles — turning something on/off changes which steps
    // are visible, so the node map needs a re-render too. Turning "Meal" ON
    // while under the minimum adult threshold shows a blocking modal instead
    // of silently enabling it.
    document.querySelectorAll('.choice-toggle-btn').forEach(b=>b.onclick=()=>{
        const key = b.getAttribute('data-choice');
        const turningOn = !state.formData.choices[key];
        if (key === 'meal' && turningOn) {
            const minAdults = state.meals[0]?.min_adults_required || 10;
            if (state.formData.travelers.no_of_pax.adult < minAdults) {
                openMealBlockedModal(minAdults);
                return;
            }
        }
        state.formData.choices[key] = turningOn;
        renderNodeMap();
        renderStepContent();
    });
    document.querySelectorAll('.distance-btn').forEach(b=>b.onclick=()=>{
        state.formData.hotelCategory=b.getAttribute('data-hotel');
        state.hotels = []; // force a re-fetch filtered to the new preference next time Accommodation renders
        state.hotelsFetchFailed = false; // give the new preference a fresh attempt, don't inherit a prior failure
        state.hotelsFetchAttempted = false; // this is a NEW preference, not the same query — allow the auto-trigger to fire again
        renderStepContent();
    });
    document.querySelectorAll('.service-level-btn').forEach(b=>b.onclick=()=>{
        state.formData.serviceLevel = b.getAttribute('data-level');
        renderStepContent();
    });
    document.querySelectorAll('.meal-tier-btn').forEach(b=>b.onclick=()=>{
        state.formData.meal = {
            meal_sys_id: b.getAttribute('data-mealsysid'),
            meal_name: b.getAttribute('data-mealname'),
            tier: b.getAttribute('data-tier'),
            price: parseFloat(b.getAttribute('data-price'))||0
        };
        renderStepContent();
    });

    // Flight — connection type is recorded, but NO price is shown or added
    // to the estimate here; a note in the render explains fares will be
    // updated by the team. "Fare Type" is no longer a user-facing choice.
    document.querySelectorAll('.conn-type-btn').forEach(b=>b.onclick=()=>{
        if (!state.formData.flight) state.formData.flight = { connection_type: null, flight_date: '' };
        state.formData.flight.connection_type = b.getAttribute('data-conn');
        renderStepContent();
    });
    document.getElementById('flight-date')?.addEventListener('change',e=>{
        if (state.formData.flight) state.formData.flight.flight_date = e.target.value;
        saveState();
    });

    document.querySelector('.hotel-refresh-btn')?.addEventListener('click', ()=>{ state.hotels = []; state.hotelsFetchFailed = false; state.hotelsLoading = true; renderStepContent(); setTimeout(() => fetchHotels(state.selectedHotelCity), 0); });
    document.querySelector('.hotel-retry-btn')?.addEventListener('click', ()=>{ state.hotels = []; state.hotelsFetchFailed = false; state.hotelsLoading = true; renderStepContent(); setTimeout(() => fetchHotels(state.selectedHotelCity), 0); });

    document.querySelectorAll('.duration-btn').forEach(b=>b.onclick=()=>{state.formData.duration=parseInt(b.getAttribute('data-duration'));state.formData.makkahStay=Math.ceil(state.formData.duration*0.6);state.formData.madinahStay=state.formData.duration-state.formData.makkahStay;renderStepContent();});
    document.getElementById('custom-duration')?.addEventListener('change',e=>{
        let v = parseInt(e.target.value);
        if (!v || v < 3) return;
        v = Math.min(60, v);
        state.formData.duration = v;
        state.formData.makkahStay = Math.ceil(v*0.6);
        state.formData.madinahStay = v - state.formData.makkahStay;
        renderStepContent();
    });
    const mr=document.getElementById('makkah-range');if(mr)mr.oninput=e=>{let v=parseInt(e.target.value);state.formData.makkahStay=v;state.formData.madinahStay=state.formData.duration-v;renderStepContent();};
    const mdr=document.getElementById('madinah-range');if(mdr)mdr.oninput=e=>{let v=parseInt(e.target.value);state.formData.madinahStay=v;state.formData.makkahStay=state.formData.duration-v;renderStepContent();};

    // Transport — multi-select: clicking a route opens its vehicle picker; picking a
    // vehicle adds that route+vehicle as one "leg" to the transport array.
    document.querySelectorAll('.route-btn').forEach(b=>b.onclick=()=>{
        const sysId = b.getAttribute('data-id');
        state.transportPickerRouteId = state.transportPickerRouteId === sysId ? null : sysId;
        state.transportPickerRouteName = b.getAttribute('data-name');
        renderStepContent();
    });
    document.querySelectorAll('.car-btn').forEach(b=>b.onclick=()=>{
        if (!state.transportPickerRouteId) return;
        state.formData.transport.push({
            route_sys_id: state.transportPickerRouteId,
            route_name: state.transportPickerRouteName,
            vehicle_type: b.getAttribute('data-car'),
            vehicle_type_sys_id: b.getAttribute('data-carsysid'),
            price: parseFloat(b.getAttribute('data-price'))||0
        });
        state.transportPickerRouteId = null;
        renderStepContent();
    });
    document.querySelectorAll('.remove-leg-btn').forEach(b=>b.onclick=()=>{
        const idx = parseInt(b.getAttribute('data-idx'));
        state.formData.transport.splice(idx,1);
        renderStepContent();
    });

    // Moyallem (general step) — pick a service, then a category.
    document.querySelectorAll('.moyallem-service-btn').forEach(b=>b.onclick=()=>{
        const sysId = b.getAttribute('data-id');
        const idx = state.formData.moyallem.findIndex(m=>m.sys_id===sysId);
        if (idx >= 0) state.formData.moyallem.splice(idx,1);
        else state.formData.moyallem.push({ sys_id: sysId, name: b.getAttribute('data-name'), category: null, price: 0 });
        renderStepContent();
    });
    document.querySelectorAll('.moyallem-category-btn').forEach(b=>b.onclick=()=>{
        const sysId = b.getAttribute('data-id');
        const idx = state.formData.moyallem.findIndex(m=>m.sys_id===sysId);
        if (idx < 0) return;
        state.formData.moyallem[idx].category = b.getAttribute('data-category');
        state.formData.moyallem[idx].price = parseFloat(b.getAttribute('data-price'))||0;
        renderStepContent();
    });

    document.getElementById('summary-phone')?.addEventListener('input', e=>{ state.formData.travelers.phone = e.target.value; saveState(); });
    document.getElementById('summary-email')?.addEventListener('input', e=>{ state.formData.travelers.email = e.target.value; saveState(); });
    document.getElementById('confirm-book-btn')?.addEventListener('click', confirmAndBook);
}

// ── Accommodation: city tab + hotel expand + room type expand + board type selection ──
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
    const idx = state.formData.accommodation.findIndex(a => a.room_type_sys_id === roomTypeSysId && a.board_type_sys_id === boardTypeSysId);
    if (idx >= 0) {
        state.formData.accommodation.splice(idx, 1); // tap the same one again to deselect
    } else {
        // Only one board type per room type — picking a new one replaces
        // whatever was previously selected for this same room.
        state.formData.accommodation = state.formData.accommodation.filter(a => a.room_type_sys_id !== roomTypeSysId);
        const priceEntry = getBoardPriceFor(roomTypeSysId, boardTypeSysId);
        state.formData.accommodation.push({
            hotel_sys_id: hotelSysId, hotel_name: hotelName,
            room_type_sys_id: roomTypeSysId, room_name: roomName,
            board_type_sys_id: boardTypeSysId, board_name: boardName,
            price: priceEntry ? (parseFloat(priceEntry.price) || 0) : 0,
            max_adults: maxAdults || 2, max_children: maxChildren || 0
        });
    }
    renderStepContent();
};

window.toggleZiarahPick = (id, name, priceSar) => {
    const idx = state.formData.ziarah.findIndex(z=>z.id===id);
    if (idx >= 0) {
        state.formData.ziarah.splice(idx,1);
    } else {
        state.formData.ziarah.push({
            id, name, price: parseFloat(priceSar)||0,
            wantsTransport: null, routeId: null, routeName: null,
            wantsMoyallem: null, moyallemCategory: null, moyallemPriceSar: 0
        });
    }
    renderStepContent();
};
window.setZiarahWantsTransport = (id, wants) => {
    const z = state.formData.ziarah.find(x=>x.id===id);
    if (!z) return;
    z.wantsTransport = wants;
    if (!wants) { z.routeId = null; z.routeName = null; }
    renderStepContent();
};
window.pickZiarahRoute = (zid, zname, routeId, routeLabel) => {
    const z = state.formData.ziarah.find(x=>x.id===zid);
    if (!z) return;
    z.routeId = routeId;
    z.routeName = routeLabel;
    // Also opens this route in the Transport step's vehicle picker so the
    // user just needs to pick a vehicle when they get there — the route
    // choice itself carries over automatically.
    state.transportPickerRouteId = routeId;
    state.transportPickerRouteName = routeLabel;
    renderStepContent();
};
window.setZiarahWantsMoyallem = (id, wants) => {
    const z = state.formData.ziarah.find(x=>x.id===id);
    if (!z) return;
    z.wantsMoyallem = wants;
    if (!wants) { z.moyallemCategory = null; z.moyallemPriceSar = 0; }
    renderStepContent();
};
window.pickZiarahMoyallemCategory = (zid, category, priceSar) => {
    const z = state.formData.ziarah.find(x=>x.id===zid);
    if (!z) return;
    z.moyallemCategory = category;
    z.moyallemPriceSar = parseFloat(priceSar)||0;
    renderStepContent();
};

// ── Meal minimum-group-size modal ──
function openMealBlockedModal(minAdults){
    state.mealBlockedModalOpen = true;
    document.getElementById('meal-blocked-modal-text').textContent =
        `To avail this, you may need at least ${minAdults} adult travelers. Please increase your adult count in the Traveler step first.`;
    const overlay = document.getElementById('meal-blocked-modal-overlay');
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
}
window.closeMealBlockedModal = () => {
    state.mealBlockedModalOpen = false;
    const overlay = document.getElementById('meal-blocked-modal-overlay');
    overlay.classList.add('hidden');
    overlay.classList.remove('flex');
};

// ── Save to DB (same endpoint as the full package-builder) ──
async function confirmAndBook(){
    const f = state.formData;
    const btn = document.getElementById('confirm-book-btn');
    const msg = document.getElementById('homepage-submit-msg');

    // Pull the latest phone/email straight from the inputs (in case the
    // user typed and clicked Confirm without tabbing out / triggering blur).
    const phoneInput = document.getElementById('summary-phone');
    const emailInput = document.getElementById('summary-email');
    if (phoneInput) f.travelers.phone = phoneInput.value.trim();
    if (emailInput) f.travelers.email = emailInput.value.trim();

    const phoneProvided = f.travelers.phone && f.travelers.phone.trim().length >= 6;
    const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.travelers.email || '');
    if (!f.travelers.name || f.travelers.name.trim().length < 2) {
        msg.textContent = 'Please enter the lead traveler\'s name.';
        msg.className = 'px-4 py-3 rounded-xl text-sm font-medium bg-red-500/10 text-red-400';
        msg.classList.remove('hidden');
        return;
    }
    // Only ONE of phone or email is required — not both.
    if (!phoneProvided && !emailValid) {
        msg.textContent = 'Please provide a WhatsApp number or a valid email address to continue.';
        msg.className = 'px-4 py-3 rounded-xl text-sm font-medium bg-red-500/10 text-red-400';
        msg.classList.remove('hidden');
        phoneInput?.focus();
        return;
    }
    // If they did type an email, it should at least look valid — but only
    // block on this if email is the ONLY contact method being relied on.
    if (!phoneProvided && f.travelers.email && !emailValid) {
        msg.textContent = 'Please enter a valid email address to continue.';
        msg.className = 'px-4 py-3 rounded-xl text-sm font-medium bg-red-500/10 text-red-400';
        msg.classList.remove('hidden');
        emailInput?.focus();
        return;
    }
    msg.classList.add('hidden');

    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader" class="w-5 h-5 animate-spin"></i> Submitting...';
    lucide.createIcons();

    const fd = new FormData();
    fd.append('csrf_token',      '<?= htmlspecialchars($csrf) ?>');
    fd.append('build_data_json', JSON.stringify(f));

    try {
        const res  = await fetch(`<?= BASE_URL ?>/api/save-custom-build.php`, { method:'POST', body:fd });
        const data = await res.json();
        if (data.success) {
            localStorage.removeItem(MINI_STORAGE_KEY);
            window.location.href = data.redirect;
            return;
        }
        msg.textContent = data.message || 'Could not submit — please try WhatsApp instead.';
        msg.className = 'px-4 py-3 rounded-xl text-sm font-medium bg-red-500/10 text-red-400';
        msg.classList.remove('hidden');
    } catch(e) {
        msg.textContent = 'Submission failed — please try WhatsApp instead.';
        msg.className = 'px-4 py-3 rounded-xl text-sm font-medium bg-yellow-500/10 text-yellow-400';
        msg.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.innerHTML = '<i data-lucide="file-check" class="w-6 h-6"></i> Confirm & Preview';
    lucide.createIcons();
}

function renderFaqs(){
    const c=document.getElementById('faq-container');
    c.innerHTML=faqs.map((f,i)=>`
        <div class="bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl overflow-hidden">
            <button class="faq-btn w-full px-8 py-6 flex justify-between items-center" data-idx="${i}">
                <span class="font-semibold text-left">${f.q}</span>
                <i data-lucide="${state.openFaqIndex===i?'minus':'plus'}" class="text-secondary w-5 h-5 shrink-0 ml-4"></i>
            </button>
            <div class="faq-content px-8 pb-6 text-white/60 leading-relaxed ${state.openFaqIndex===i?'':'hidden'}">${f.a}</div>
        </div>`).join('');
    document.querySelectorAll('.faq-btn').forEach(b=>b.onclick=()=>{let idx=parseInt(b.getAttribute('data-idx'));state.openFaqIndex=state.openFaqIndex===idx?null:idx;renderFaqs();lucide.createIcons();});
}

// Scroll so the node-map (progress indicator) and the form card are in
// view — but NOT the "Interactive Planner" heading text above them, which
// is decorative and doesn't need to stay in view on every step change.
// Skips scrolling if that target is already comfortably in view, so a user
// who scrolled down on purpose isn't yanked back on every click.
function scrollToStepCard(){
    const target = document.getElementById('node-map');
    if (!target) return;
    const rect = target.getBoundingClientRect();
    const targetOffset = 90; // leave room for the sticky/fixed navbar
    const alreadyInView = rect.top >= -20 && rect.top <= targetOffset + 40;
    if (alreadyInView) return;
    const top = window.scrollY + rect.top - targetOffset;
    window.scrollTo({ top, behavior: 'smooth' });
}

document.getElementById('next-step').onclick=()=>{
    if(state.step<totalSteps()){
        state.step++;
        if(!state.visited.includes(state.step)) state.visited.push(state.step);
        renderNodeMap();renderStepContent(true);scrollToStepCard();
    }
};
document.getElementById('prev-step').onclick=()=>{
    if(state.step>1){state.step--;renderNodeMap();renderStepContent(true);scrollToStepCard();}
};

renderNodeMap();
renderStepContent(true);
renderFaqs();
initConstellationBg();
PrayerWidget.init('prayer-makkah', 'prayer-madinah', 'prayer-dhaka');
</script>
</body>
</html>