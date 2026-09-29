<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle       = 'Umrah Transport | Makkah & Madinah | TravHub';
$pageDescription = 'Private and shared transport options for Umrah — Sedan, SUV, GMC, Luxury Van. Airport transfers and Ziyarah tours.';
include dirname(__DIR__) . '/includes/header.php';
?>
<style type="text/tailwindcss">
@layer components {
    .glass-card { @apply bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl; }
    .btn-primary { @apply bg-secondary hover:bg-emerald text-primary font-semibold rounded-xl transition-all duration-300; }
}
</style>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<!-- HERO -->
<section class="pt-36 pb-16 bg-gradient-to-b from-navy to-dark relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-secondary/10 blur-[150px] rounded-full"></div>
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="max-w-2xl">
            <span class="inline-block text-secondary bg-secondary/10 border border-secondary/20 px-4 py-1 rounded-full text-xs font-bold uppercase tracking-widest mb-4">Ground Transportation</span>
            <h1 class="text-5xl font-bold mb-5">Umrah <span class="bg-gradient-to-r from-teal to-secondary bg-clip-text text-transparent">Transport</span></h1>
            <p class="text-white/50 text-lg leading-relaxed">Comfortable, air-conditioned vehicles operated by experienced drivers. All transport includes airport pickup, Haram drop-off and Ziyarah transfers.</p>
        </div>
    </div>
</section>

<!-- VEHICLES -->
<section class="py-20 bg-dark">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12">
            <span class="text-secondary font-bold tracking-widest uppercase text-xs">Our Fleet</span>
            <h2 class="text-3xl font-bold mt-2">Choose Your Vehicle</h2>
            <p class="text-white/40 mt-3">Prices are per vehicle per trip (not per person). All vehicles have AC and licensed drivers.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            $vehicles = [
                ['Sedan','4 Passengers','120','car','Most affordable option for small families or couples.',['AC & comfortable','Licensed driver','Airport pickup','24/7 availability'],'bg-white/5'],
                ['SUV / GMC','7 Passengers','180','truck','Most popular choice — spacious for families with luggage.',['Extra luggage space','Premium seats','Airport + Ziyarah','Child seat available'],'bg-secondary/5 border-secondary/20'],
                ['Luxury Van','12 Passengers','280','bus','Ideal for extended families or small groups.',['Business-class comfort','Fridge & TV','Group coordination','Dedicated coordinator'],'bg-white/5'],
                ['Full Coach','45 Passengers','650','users','Best value for large groups — dedicated bus.',['Full-size AC coach','Group guide on board','All transfers included','Luggage storage'],'bg-white/5'],
            ];
            foreach ($vehicles as [$name,$capacity,$priceSar,$icon,$desc,$features,$extra]):
            ?>
            <div class="glass-card <?= $extra ?> p-6 flex flex-col hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 bg-secondary/10 rounded-2xl flex items-center justify-center mb-5">
                    <i data-lucide="<?= $icon ?>" class="w-7 h-7 text-secondary"></i>
                </div>
                <h3 class="text-lg font-bold mb-1"><?= htmlspecialchars($name) ?></h3>
                <p class="text-xs text-white/40 mb-3 flex items-center gap-1.5">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-secondary"></i> Up to <?= htmlspecialchars($capacity) ?>
                </p>
                <p class="text-white/50 text-xs mb-5 leading-relaxed"><?= htmlspecialchars($desc) ?></p>
                <ul class="space-y-2 mb-6 flex-1">
                    <?php foreach ($features as $f): ?>
                    <li class="flex items-center gap-2 text-xs text-white/60">
                        <i data-lucide="check" class="w-3.5 h-3.5 text-secondary shrink-0"></i><?= htmlspecialchars($f) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="pt-4 border-t border-white/10">
                    <p class="text-[10px] text-white/40 uppercase tracking-wider">Per Trip (one way)</p>
                    <p class="text-2xl font-bold text-secondary mt-0.5" data-price-sar="<?= $priceSar ?>">SR <?= $priceSar ?></p>
                    <a href="https://wa.me/8801000000000?text=<?= urlencode('I need a '.$name.' for my Umrah trip.') ?>" target="_blank"
                       class="mt-4 w-full btn-primary py-2.5 text-sm flex items-center justify-center gap-2">
                        <i data-lucide="message-circle" class="w-4 h-4"></i> Book This Vehicle
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ROUTES / SERVICES -->
<section class="py-20 bg-navy/30">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12">
            <span class="text-secondary font-bold tracking-widest uppercase text-xs">Routes</span>
            <h2 class="text-3xl font-bold mt-2">Transfer Routes & Pricing</h2>
        </div>
        <div class="overflow-x-auto rounded-2xl border border-white/10">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-white/5 border-b border-white/10">
                        <th class="text-left px-6 py-4 text-white/50 text-xs uppercase tracking-wider">Route</th>
                        <th class="text-center px-4 py-4 text-white/50 text-xs uppercase tracking-wider">Sedan (4 pax)</th>
                        <th class="text-center px-4 py-4 text-white/50 text-xs uppercase tracking-wider">SUV (7 pax)</th>
                        <th class="text-center px-4 py-4 text-white/50 text-xs uppercase tracking-wider">Van (12 pax)</th>
                        <th class="text-center px-4 py-4 text-white/50 text-xs uppercase tracking-wider">Coach (45)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $routes = [
                        ['Jeddah Airport → Makkah','120','180','280','480'],
                        ['Makkah → Madinah','250','380','550','900'],
                        ['Jeddah Airport → Madinah','300','450','650','1000'],
                        ['Makkah → Jeddah Airport','120','180','280','480'],
                        ['Madinah → Jeddah Airport','300','450','650','1000'],
                        ['Makkah Ziyarah Tour (half-day)','150','220','320','550'],
                        ['Madinah Ziyarah Tour (half-day)','140','200','300','500'],
                    ];
                    foreach ($routes as $i => $r):
                    ?>
                    <tr class="border-b border-white/5 <?= $i%2===0?'bg-white/5':'' ?> hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4 font-medium"><?= htmlspecialchars($r[0]) ?></td>
                        <?php for ($j=1;$j<=4;$j++): ?>
                        <td class="px-4 py-4 text-center text-secondary font-bold" data-price-sar="<?= $r[$j] ?>">SR <?= $r[$j] ?></td>
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="text-white/30 text-xs mt-4 text-center">All prices in SAR per vehicle (not per person). Return trips are double. Prices may vary during Hajj season.</p>
    </div>
</section>

<!-- INCLUDED FEATURES -->
<section class="py-16 bg-dark">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-bold">All Transport Includes</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $features = [
                ['wifi','Free WiFi','In-car WiFi hotspot on all premium vehicles'],
                ['thermometer-snowflake','AC Cooling','Full air-conditioning maintained throughout'],
                ['clock','24/7 Service','Available round the clock including prayer times'],
                ['shield-check','Licensed Drivers','Saudi-licensed, experienced Umrah drivers'],
            ];
            foreach ($features as [$icon,$title,$desc]):
            ?>
            <div class="glass-card p-6 text-center">
                <div class="w-12 h-12 bg-secondary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="<?= $icon ?>" class="w-6 h-6 text-secondary"></i>
                </div>
                <h3 class="font-bold text-sm mb-1"><?= htmlspecialchars($title) ?></h3>
                <p class="text-white/40 text-xs leading-relaxed"><?= htmlspecialchars($desc) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-14 bg-secondary/5 border-t border-secondary/10">
    <div class="max-w-2xl mx-auto px-6 text-center">
        <h2 class="text-2xl font-bold mb-3">Need a Custom Transport Arrangement?</h2>
        <p class="text-white/50 text-sm mb-6">Multi-city routes, extended hire, or group transport for special schedules — we handle it all.</p>
        <a href="https://wa.me/8801000000000?text=<?= urlencode('I need transport for my Umrah trip. Please provide a custom quote.') ?>" target="_blank"
           class="inline-flex items-center gap-2 bg-[#25D366] hover:bg-[#1ebe5c] text-white font-semibold py-3 px-8 rounded-xl transition-all">
            <i data-lucide="message-circle" class="w-5 h-5"></i> Get Custom Quote on WhatsApp
        </a>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/currency-handler.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>