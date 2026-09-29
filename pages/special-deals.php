<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'Special Deals & Offers | TravHub Umrah';
include dirname(__DIR__) . '/includes/header.php';
?>
<style type="text/tailwindcss">
@layer components {
    .glass-card { @apply bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl; }
    .btn-primary { @apply bg-secondary hover:bg-emerald text-primary font-semibold rounded-xl transition-all duration-300; }
    .badge-deal  { @apply absolute top-4 right-4 text-white px-4 py-1.5 rounded-full text-xs font-bold shadow-lg animate-pulse; }
}
</style>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<!-- Hero -->
<section class="pt-40 pb-20 bg-gradient-to-b from-navy to-dark relative overflow-hidden">
    <div class="absolute top-1/4 left-1/4 w-80 h-80 bg-secondary/10 blur-[100px] rounded-full animate-pulse"></div>
    <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-teal/10 blur-[100px] rounded-full animate-pulse delay-1000"></div>
    <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
        <span class="inline-block text-secondary bg-secondary/10 border border-secondary/20 px-4 py-1 rounded-full text-xs font-bold uppercase tracking-widest mb-4">Limited Time Offers</span>
        <h1 class="text-5xl lg:text-7xl font-bold mb-6">Special <span class="bg-gradient-to-r from-teal to-secondary bg-clip-text text-transparent">Deals</span></h1>
        <p class="text-white/50 text-lg max-w-xl mx-auto mb-8">Exclusive packages and seasonal discounts on Umrah journeys. Act fast — limited seats available.</p>
        <!-- Global countdown timer -->
        <div class="inline-flex items-center gap-4 bg-white/5 border border-secondary/20 rounded-2xl px-8 py-4">
            <span class="text-secondary text-xs font-bold uppercase tracking-wider">Next batch expires in:</span>
            <div class="flex gap-3 text-center" id="global-timer">
                <?php foreach (['days','hours','mins','secs'] as $unit): ?>
                <div>
                    <div class="text-2xl font-bold text-white" id="t-<?= $unit ?>">--</div>
                    <div class="text-[9px] text-white/30 uppercase tracking-wider"><?= $unit ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Deals grid -->
<section class="py-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php
            $deals = [
                ['title'=>'Ramadan Early Bird','save'=>'25%','badge'=>'SAVE 25%','badgeBg'=>'bg-red-500','img'=>'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?auto=format&fit=crop&q=80&w=800','originalSar'=>7600,'priceSar'=>5700,'desc'=>'Book before May 15 and save 25% on our premium 14-night Ramadan Umrah package.','details'=>['14 Nights / 15 Days','4★ Hotel — 200m from Haram','Direct Biman Bangladesh Airlines flight'],'deal'=>'Ramadan Early Bird'],
                ['title'=>'Family Retreat Pack','save'=>'Family','badge'=>'FAMILY PACK','badgeBg'=>'bg-teal text-primary','img'=>'https://images.unsplash.com/photo-1542810634-71277d95dcbb?auto=format&fit=crop&q=80&w=800','originalSar'=>18000,'priceSar'=>14400,'desc'=>'Special family package — children under 12 stay free. Ideal for 2 adults + 2 children.','details'=>['10 Nights','4★ Adjoining Rooms','Children under 12: Free (excl. flights)'],'deal'=>'Family Retreat'],
                ['title'=>'Last Minute Flash','save'=>'30%','badge'=>'LAST MINUTE','badgeBg'=>'bg-red-600','img'=>'https://images.unsplash.com/photo-1564769625905-50e93615e769?auto=format&fit=crop&q=80&w=800','originalSar'=>5000,'priceSar'=>3500,'desc'=>'Departure in 15 days. Limited seats — economy package at flash price.','details'=>['7 Nights','3★ Hotel — 500m from Haram','Shared transport'],'deal'=>'Last Minute Flash'],
                ['title'=>'Student Special','save'=>'20%','badge'=>'STUDENT SPECIAL','badgeBg'=>'bg-teal text-primary','img'=>'https://images.unsplash.com/photo-1565035010268-a3816f98589a?auto=format&fit=crop&q=80&w=800','originalSar'=>4800,'priceSar'=>3840,'desc'=>'Valid university ID required. Affordable Umrah for students and young adults.','details'=>['7 Nights','3★ Hotel','Group transport'],'deal'=>'Student Special'],
            ];
            foreach ($deals as $d):
                $saving = $d['originalSar'] - $d['priceSar'];
                $pct    = round($saving / $d['originalSar'] * 100);
            ?>
            <div class="glass-card overflow-hidden group hover:-translate-y-1 transition-all duration-300">
                <div class="relative h-56 overflow-hidden">
                    <img src="<?= $d['img'] ?>" alt="<?= htmlspecialchars($d['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" referrerpolicy="no-referrer">
                    <div class="absolute inset-0 bg-gradient-to-t from-dark/80 to-transparent"></div>
                    <div class="badge-deal <?= $d['badgeBg'] ?>"><?= $d['badge'] ?></div>
                </div>
                <div class="p-8">
                    <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($d['title']) ?></h3>
                    <p class="text-white/50 text-sm mb-5"><?= htmlspecialchars($d['desc']) ?></p>
                    <ul class="space-y-2 mb-6">
                        <?php foreach ($d['details'] as $det): ?>
                        <li class="flex items-center gap-2 text-sm text-white/70"><i data-lucide="check-circle" class="w-4 h-4 text-secondary shrink-0"></i> <?= htmlspecialchars($det) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="flex items-end justify-between pt-5 border-t border-white/10">
                        <div>
                            <p class="text-xs text-white/30 line-through mb-1">SR <?= number_format($d['originalSar']) ?></p>
                            <p class="text-2xl font-bold text-secondary" data-price-sar="<?= $d['priceSar'] ?>">SR <?= number_format($d['priceSar']) ?></p>
                            <p class="text-[10px] text-secondary/70 mt-0.5">Save SR <?= number_format($saving) ?> (<?= $pct ?>%)</p>
                        </div>
                        <a href="<?= BASE_URL ?>/pages/contact.php?deal=<?= urlencode($d['deal']) ?>" class="btn-primary py-2.5 px-6 text-sm flex items-center gap-2">
                            <i data-lucide="zap" class="w-4 h-4"></i> Claim Deal
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Newsletter / Alert strip -->
<section class="py-16 bg-navy/30 border-t border-white/5">
    <div class="max-w-3xl mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold mb-3">Don't miss the next deal</h2>
        <p class="text-white/50 mb-8 text-sm">Share your WhatsApp number and we'll alert you when new offers launch.</p>
        <div class="flex gap-3 max-w-md mx-auto">
            <input type="tel" id="alert-phone" placeholder="+880 1X XX-XXXXXX" class="flex-1 bg-white/5 border border-white/10 rounded-xl px-5 py-3.5 focus:border-secondary focus:outline-none placeholder-white/20 text-sm">
            <a id="alert-btn" href="#" class="bg-[#25D366] hover:bg-[#1ebe5c] text-white font-semibold px-6 py-3.5 rounded-xl transition-all text-sm flex items-center gap-2">
                <i data-lucide="message-circle" class="w-4 h-4"></i> Alert Me
            </a>
        </div>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/currency-handler.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
// Countdown to next batch expiry (arbitrary future date for demo)
const expiry = new Date();
expiry.setDate(expiry.getDate() + 7);
expiry.setHours(23, 59, 59, 0);

function tick() {
    const diff = expiry - Date.now();
    if (diff <= 0) { document.getElementById('global-timer').innerHTML = '<span class="text-red-400 text-sm font-bold">Expired</span>'; return; }
    const d = Math.floor(diff/86400000);
    const h = Math.floor((diff%86400000)/3600000);
    const m = Math.floor((diff%3600000)/60000);
    const s = Math.floor((diff%60000)/1000);
    document.getElementById('t-days').textContent  = String(d).padStart(2,'0');
    document.getElementById('t-hours').textContent = String(h).padStart(2,'0');
    document.getElementById('t-mins').textContent  = String(m).padStart(2,'0');
    document.getElementById('t-secs').textContent  = String(s).padStart(2,'0');
}
tick();
setInterval(tick, 1000);

// WhatsApp alert
document.getElementById('alert-btn').addEventListener('click', e => {
    e.preventDefault();
    const phone = document.getElementById('alert-phone').value.trim();
    if (!phone) { alert('Please enter your WhatsApp number.'); return; }
    const msg = encodeURIComponent('Hi TravHub! Please add me to your deals alert list. My number: ' + phone);
    window.open('https://wa.me/8801000000000?text=' + msg, '_blank');
});
</script>
</body>
</html>
