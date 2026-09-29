<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle       = 'Hajj Packages 2026 | TravHub Bangladesh';
$pageDescription = 'Book Hajj 2026 from Bangladesh with TravHub. Government-quota and private packages with full visa assistance.';
include dirname(__DIR__) . '/includes/header.php';
?>
<style type="text/tailwindcss">
@layer components {
    .glass-card { @apply bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl; }
    .btn-primary { @apply bg-secondary hover:bg-emerald text-primary font-semibold rounded-xl transition-all duration-300; }
    .btn-outline { @apply border-2 border-secondary text-secondary hover:bg-secondary hover:text-primary font-semibold rounded-xl transition-all duration-300; }
}
</style>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<!-- HERO -->
<section class="relative min-h-[65vh] flex items-center pt-20 overflow-hidden">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-b from-primary/85 via-primary/70 to-dark z-10"></div>
        <img src="https://images.unsplash.com/photo-1565035010268-a3816f98589a?auto=format&fit=crop&q=80&w=2000" alt="Hajj Makkah" class="w-full h-full object-cover" referrerpolicy="no-referrer">
        <div class="absolute top-1/3 left-1/4 w-96 h-96 bg-secondary/15 blur-[130px] rounded-full animate-pulse"></div>
    </div>
    <div class="max-w-7xl mx-auto px-6 w-full relative z-20">
        <div class="max-w-3xl">
            <span class="inline-block text-secondary bg-secondary/10 border border-secondary/20 px-4 py-1 rounded-full text-xs font-bold uppercase tracking-widest mb-4">Hajj 2026 · ১৪৪৭ হিজরি</span>
            <h1 class="text-5xl lg:text-7xl font-bold leading-tight mb-6">Hajj <span class="bg-gradient-to-r from-teal to-secondary bg-clip-text text-transparent">2026</span></h1>
            <p class="text-white/60 text-lg max-w-xl mb-8 leading-relaxed">The fifth pillar of Islam — the journey of a lifetime. TravHub offers Government-quota and private Hajj packages from Bangladesh with complete guidance.</p>
            <div class="flex flex-wrap gap-4 mb-10">
                <a href="#packages" class="bg-secondary hover:bg-emerald text-primary font-semibold py-3 px-8 rounded-xl transition-all">View Packages</a>
                <a href="<?= BASE_URL ?>/pages/contact.php?subject=Hajj+Package+Inquiry" class="border-2 border-secondary text-secondary hover:bg-secondary hover:text-primary font-semibold py-3 px-8 rounded-xl transition-all">Inquire Now</a>
            </div>
            <!-- Countdown -->
            <div class="flex flex-wrap gap-3 items-center">
                <span class="text-white/40 text-xs uppercase tracking-wider">Hajj 2026 begins in:</span>
                <div class="flex gap-2" id="hajj-countdown">
                    <?php foreach (['days','hours','mins','secs'] as $u): ?>
                    <div class="text-center bg-white/5 border border-white/10 rounded-xl px-3 py-2 min-w-[54px]">
                        <div class="text-xl font-bold text-secondary" id="hc-<?= $u ?>">--</div>
                        <div class="text-[9px] uppercase tracking-wider text-white/30"><?= $u ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <span class="text-secondary/70 text-xs">~14 Jun 2026</span>
            </div>
        </div>
    </div>
</section>

<!-- INFO STRIP -->
<div class="bg-secondary/5 border-y border-secondary/10">
    <div class="max-w-7xl mx-auto px-6 py-5 grid grid-cols-2 lg:grid-cols-4 gap-4">
        <?php
        $infos = [
            ['calendar','Hajj 2026 Dates','~9–14 June 2026'],
            ['users','Group Size','20–50 pilgrims'],
            ['file-text','Registration','Open — Limited Quota'],
            ['shield-check','Visa','Fully managed'],
        ];
        foreach ($infos as [$icon,$label,$val]):
        ?>
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-secondary/10 rounded-xl flex items-center justify-center shrink-0">
                <i data-lucide="<?= $icon ?>" class="w-4 h-4 text-secondary"></i>
            </div>
            <div>
                <p class="text-[10px] text-white/40 uppercase tracking-wider"><?= $label ?></p>
                <p class="text-sm font-semibold"><?= $val ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- PACKAGES -->
<section id="packages" class="py-24 bg-dark">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-14">
            <span class="text-secondary font-bold tracking-widest uppercase text-xs">Choose Your Journey</span>
            <h2 class="text-4xl font-bold mt-2">Hajj 2026 Packages</h2>
            <p class="text-white/40 mt-3 max-w-xl mx-auto">All packages include Hajj visa, Makkah & Mina accommodation, all transport and certified guidance.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php
            $packages = [
                ['Economy','Government Quota','bg-secondary text-primary',270000,40,3,
                 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?auto=format&fit=crop&q=80&w=800',
                 ['Government-allocated Hajj visa','3★ Hotel in Mina & Makkah','Full Arafat & Mina transport','Certified Bangladeshi group guide','Qurbani (sacrifice) included'],
                 'Subject to Hajj quota allocation from Bangladesh ministry.'],
                ['Premium','Most Popular','bg-teal text-primary',420000,35,4,
                 'https://images.unsplash.com/photo-1542810634-71277d95dcbb?auto=format&fit=crop&q=80&w=800',
                 ['Private Hajj visa — faster processing','4★ Hotel — 300m from Haram','Private AC coach for all rites','Dedicated imam & guide per group','Qurbani + Ziyarah tours included'],
                 'Limited to 45 seats per departure group.'],
                ['VIP Luxury','Ultra Premium','bg-gradient-to-r from-teal to-secondary text-primary',780000,30,5,
                 'https://images.unsplash.com/photo-1564769625905-50e93615e769?auto=format&fit=crop&q=80&w=800',
                 ['VIP Hajj visa — priority processing','5★ Hotel adjacent to Haram','Private chauffeur for all movements','Personal scholar & 24/7 assistant','Business class flight (Biman)'],
                 'Maximum 20 seats per batch. Book early.'],
            ];
            foreach ($packages as [$name,$badge,$bColor,$priceBdt,$duration,$stars,$img,$features,$note]):
                $priceSar = round($priceBdt / 32.5);
            ?>
            <div class="glass-card overflow-hidden group hover:-translate-y-2 transition-all duration-500 flex flex-col">
                <div class="relative h-56 overflow-hidden">
                    <img src="<?= $img ?>" alt="Hajj <?= htmlspecialchars($name) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" referrerpolicy="no-referrer">
                    <div class="absolute inset-0 bg-gradient-to-t from-dark/80 to-transparent"></div>
                    <div class="absolute top-4 left-4 <?= $bColor ?> text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full"><?= $badge ?></div>
                    <div class="absolute bottom-4 left-4 text-secondary text-xs tracking-wider"><?= str_repeat('★',$stars) ?></div>
                </div>
                <div class="p-7 flex flex-col flex-1">
                    <div class="flex justify-between items-start mb-5">
                        <div>
                            <h3 class="text-xl font-bold">Hajj <?= htmlspecialchars($name) ?></h3>
                            <p class="text-white/40 text-xs mt-1"><?= $duration ?> Days including all rites</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-white/40 uppercase">Per Person</p>
                            <p class="text-2xl font-bold text-secondary">৳<?= number_format($priceBdt) ?></p>
                            <p class="text-[10px] text-white/30">≈ SR <?= number_format($priceSar) ?></p>
                        </div>
                    </div>
                    <ul class="space-y-2.5 mb-5 flex-1">
                        <?php foreach ($features as $feat): ?>
                        <li class="flex items-start gap-2.5 text-sm text-white/70">
                            <i data-lucide="check-circle" class="w-4 h-4 text-secondary shrink-0 mt-0.5"></i>
                            <?= htmlspecialchars($feat) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="text-[10px] text-white/25 mb-5 italic"><?= htmlspecialchars($note) ?></p>
                    <div class="flex gap-3">
                        <a href="https://wa.me/8801000000000?text=<?= urlencode('Assalamu Alaikum! I am interested in Hajj '.$name.' package 2026.') ?>" target="_blank"
                           class="flex-1 flex items-center justify-center gap-1.5 bg-[#25D366] hover:bg-[#1ebe5c] text-white font-semibold py-2.5 rounded-xl transition-all text-sm">
                            <i data-lucide="message-circle" class="w-4 h-4"></i> WhatsApp
                        </a>
                        <a href="<?= BASE_URL ?>/pages/contact.php?subject=<?= urlencode('Hajj 2026 '.$name) ?>" class="flex-1 btn-outline py-2.5 text-sm text-center">Inquire</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- RITES GUIDE -->
<section class="py-20 bg-navy/30">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-12">
            <span class="text-secondary font-bold tracking-widest uppercase text-xs">Step by Step</span>
            <h2 class="text-3xl font-bold mt-2">The Five Days of Hajj</h2>
        </div>
        <div class="space-y-4">
            <?php
            $rites = [
                ['8 Dhul Hijjah','Day of Tarwiyah (Mina)','Pilgrims travel to Mina after Fajr and spend the day in ibadah — performing Dhuhr, Asr, Maghrib, Isha and the next Fajr, all shortened (qasr) but not combined.'],
                ['9 Dhul Hijjah','Day of Arafat — Wuquf','The most important day of Hajj. Pilgrims stand on the plain of Arafat from Dhuhr until sunset in du\'a and remembrance. Without this standing, there is no Hajj.'],
                ['10 Dhul Hijjah','Eid al-Adha — Rami, Nahr, Taqsir','After Fajr at Muzdalifah, pilgrims return to Mina. They throw 7 pebbles at Jamarat al-Aqabah, perform Qurbani, then shave or cut their hair — partial release from Ihram.'],
                ['11–12 Dhul Hijjah','Tashreeq Days — All Three Jamarat','Pilgrims remain in Mina and throw 7 pebbles at each of the three Jamarat (Sughra, Wusta, Kubra) after Dhuhr each day. Pilgrims may depart after the 12th.'],
                ['Before Departing','Tawaf al-Wada (Farewell Tawaf)','The final obligation. Before leaving Makkah, pilgrims perform 7 circuits of Tawaf around the Kaaba as a farewell to the Sacred House.'],
            ];
            foreach ($rites as $i => [$date,$title,$desc]):
            ?>
            <div class="glass-card p-6 flex gap-5 hover:border-secondary/20 transition-colors">
                <div class="w-12 h-12 rounded-full bg-secondary/10 border-2 border-secondary flex items-center justify-center text-secondary font-bold text-sm shrink-0"><?= $i+1 ?></div>
                <div>
                    <p class="text-[10px] text-secondary uppercase tracking-widest mb-0.5"><?= htmlspecialchars($date) ?></p>
                    <h3 class="font-bold mb-1"><?= htmlspecialchars($title) ?></h3>
                    <p class="text-white/50 text-sm leading-relaxed"><?= htmlspecialchars($desc) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-dark border-t border-white/5">
    <div class="max-w-2xl mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold mb-4">Reserve Your Spot for Hajj 2026</h2>
        <p class="text-white/50 text-sm mb-8">Quota is strictly limited. Register your interest now — our team will confirm availability within 24 hours.</p>
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="https://wa.me/8801000000000?text=<?= urlencode('Assalamu Alaikum! I want to register for Hajj 2026 with TravHub.') ?>" target="_blank"
               class="flex items-center gap-2 bg-[#25D366] hover:bg-[#1ebe5c] text-white font-semibold py-3 px-8 rounded-xl transition-all">
                <i data-lucide="message-circle" class="w-5 h-5"></i> Register via WhatsApp
            </a>
            <a href="<?= BASE_URL ?>/pages/contact.php?subject=Hajj+2026+Registration" class="flex items-center gap-2 border border-secondary text-secondary hover:bg-secondary hover:text-primary font-semibold py-3 px-8 rounded-xl transition-all">
                <i data-lucide="mail" class="w-5 h-5"></i> Email Registration
            </a>
        </div>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
const hajjDate = new Date('2026-06-14T00:00:00+03:00');
function tickHajj() {
    const diff = hajjDate - Date.now();
    if (diff <= 0) return;
    document.getElementById('hc-days').textContent  = Math.floor(diff/86400000);
    document.getElementById('hc-hours').textContent = String(Math.floor((diff%86400000)/3600000)).padStart(2,'0');
    document.getElementById('hc-mins').textContent  = String(Math.floor((diff%3600000)/60000)).padStart(2,'0');
    document.getElementById('hc-secs').textContent  = String(Math.floor((diff%60000)/1000)).padStart(2,'0');
}
tickHajj();
setInterval(tickHajj, 1000);
</script>
</body>
</html>
