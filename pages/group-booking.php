<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle       = 'Group Booking | Madrasa & Institution Umrah | TravHub';
$pageDescription = 'Special group Umrah packages for madrasas, organizations and large families. Dedicated coordination and group rates.';
include dirname(__DIR__) . '/includes/header.php';
$csrf = csrfToken();
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="pt-28 pb-24">
<div class="max-w-5xl mx-auto px-6">

    <!-- Header -->
    <div class="text-center mb-14">
        <span class="text-secondary font-bold tracking-widest uppercase text-xs">Group Travel</span>
        <h1 class="text-4xl lg:text-5xl font-bold mt-2">Group Umrah Booking</h1>
        <p class="text-white/40 mt-4 max-w-2xl mx-auto">Special rates and dedicated coordination for madrasas, Islamic organizations, corporates, and large families. Minimum 15 travelers.</p>
    </div>

    <!-- Why group -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-14">
        <?php
        $benefits = [
            ['users','Group Discount','Up to 20% off standard rates for 15+ travelers'],
            ['user-check','Dedicated Coordinator','One point of contact for the entire group'],
            ['bus','Private Coach','Exclusive vehicle — no sharing with other groups'],
            ['phone','24/7 Support','WhatsApp group for real-time coordination in KSA'],
        ];
        foreach ($benefits as [$icon,$title,$desc]):
        ?>
        <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-center">
            <div class="w-11 h-11 bg-secondary/10 rounded-xl flex items-center justify-center mx-auto mb-3">
                <i data-lucide="<?= $icon ?>" class="w-5 h-5 text-secondary"></i>
            </div>
            <p class="font-bold text-sm mb-1"><?= htmlspecialchars($title) ?></p>
            <p class="text-white/40 text-xs leading-relaxed"><?= htmlspecialchars($desc) ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid lg:grid-cols-5 gap-10">

        <!-- Form -->
        <div class="lg:col-span-3 bg-white/5 border border-white/10 rounded-2xl p-8">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-secondary"></i> Group Inquiry Form
            </h2>

            <div id="group-alert" class="hidden rounded-xl p-4 mb-6 text-sm font-medium"></div>

            <form id="group-form" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="subject" value="Group Booking Inquiry">

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-white/50 uppercase tracking-wider block mb-2">Contact Name *</label>
                        <input type="text" name="name" required placeholder="Your full name"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
                    </div>
                    <div>
                        <label class="text-xs text-white/50 uppercase tracking-wider block mb-2">WhatsApp / Phone *</label>
                        <input type="tel" name="phone" required placeholder="+880 1X XX-XXXXXX"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-white/50 uppercase tracking-wider block mb-2">Email *</label>
                        <input type="email" name="email" required placeholder="your@email.com"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
                    </div>
                    <div>
                        <label class="text-xs text-white/50 uppercase tracking-wider block mb-2">Organization / Institution</label>
                        <input type="text" name="organization" placeholder="Madrasa / Company / Family"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs text-white/50 uppercase tracking-wider block mb-2">Group Size *</label>
                        <input type="number" name="group_size" min="15" required placeholder="Min. 15"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
                    </div>
                    <div>
                        <label class="text-xs text-white/50 uppercase tracking-wider block mb-2">Preferred Month</label>
                        <select name="preferred_month" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none text-white">
                            <?php
                            $months = ['Flexible','May 2026','June 2026','July 2026','August 2026','September 2026','October 2026','November 2026','December 2026','January 2027','February 2027','March 2027 (Ramadan)'];
                            foreach ($months as $m) echo "<option>$m</option>";
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-white/50 uppercase tracking-wider block mb-2">Duration</label>
                        <select name="duration" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none text-white">
                            <option>7 Days</option>
                            <option selected>10 Days</option>
                            <option>14 Days</option>
                            <option>21 Days</option>
                            <option>Custom</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs text-white/50 uppercase tracking-wider block mb-2">Budget per Person (BDT)</label>
                    <div class="grid grid-cols-3 gap-3">
                        <?php foreach (['Under ৳80,000','৳80,000 – ৳1,50,000','Above ৳1,50,000'] as $b): ?>
                        <label class="flex items-center gap-2 cursor-pointer bg-white/5 border border-white/10 hover:border-secondary rounded-xl p-3 transition-all">
                            <input type="radio" name="budget" value="<?= htmlspecialchars($b) ?>" class="accent-secondary">
                            <span class="text-xs"><?= htmlspecialchars($b) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="text-xs text-white/50 uppercase tracking-wider block mb-2">Special Requirements</label>
                    <textarea name="message" rows="4" placeholder="Wheelchair access, specific imam, female-only group, particular hotel requests, Ramadan timing, etc."
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none resize-none placeholder-white/20"></textarea>
                </div>

                <button type="submit" id="group-submit"
                    class="w-full bg-secondary hover:bg-emerald text-primary font-semibold py-4 rounded-xl transition-all flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    <span id="group-btn-text">Submit Group Inquiry</span>
                </button>

                <a href="https://wa.me/8801000000000?text=<?= urlencode('Assalamu Alaikum! We are a group of [N] people interested in Umrah. Please contact us.') ?>" target="_blank"
                   class="w-full flex items-center justify-center gap-2 bg-[#25D366] hover:bg-[#1ebe5c] text-white font-semibold py-3 rounded-xl transition-all text-sm">
                    <i data-lucide="message-circle" class="w-4 h-4"></i> Or contact directly on WhatsApp
                </a>
            </form>
        </div>

        <!-- Sidebar info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Group pricing guide -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
                <h3 class="font-bold mb-4 flex items-center gap-2">
                    <i data-lucide="tag" class="w-4 h-4 text-secondary"></i> Group Pricing Guide
                </h3>
                <div class="space-y-3">
                    <?php
                    $tiers = [
                        ['15–24','Standard group rate', '5% discount'],
                        ['25–49','Mid group rate',      '10% discount'],
                        ['50–99','Large group rate',    '15% discount'],
                        ['100+', 'Custom negotiated',  '20%+ discount'],
                    ];
                    foreach ($tiers as [$size,$label,$disc]):
                    ?>
                    <div class="flex items-center justify-between py-2 border-b border-white/5 last:border-0">
                        <div>
                            <span class="text-sm font-medium"><?= $size ?> people</span>
                            <span class="text-xs text-white/40 ml-2"><?= $label ?></span>
                        </div>
                        <span class="text-secondary text-xs font-bold"><?= $disc ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-white/30 text-xs mt-4">Discounts applied to base package price. Visa fees excluded.</p>
            </div>

            <!-- What's included -->
            <div class="bg-secondary/5 border border-secondary/20 rounded-2xl p-6">
                <h3 class="font-bold mb-4 text-secondary">Group Package Includes</h3>
                <ul class="space-y-2.5">
                    <?php
                    $included = [
                        'Dedicated group coordinator (Dhaka + KSA)',
                        'Private AC coach for all transfers',
                        'Exclusive hotel floor/block booking',
                        'Certified Bangladeshi scholar/guide',
                        'Group WhatsApp for real-time updates',
                        'Welcome dinner in Makkah',
                        'Custom itinerary to your schedule',
                        'Certificate of completion',
                    ];
                    foreach ($included as $item):
                    ?>
                    <li class="flex items-start gap-2 text-sm text-white/70">
                        <i data-lucide="check-circle" class="w-4 h-4 text-secondary shrink-0 mt-0.5"></i>
                        <?= htmlspecialchars($item) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Contact direct -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 text-center">
                <p class="text-sm text-white/60 mb-4">Prefer to talk? Our group travel specialist is available:</p>
                <p class="font-bold text-white mb-1">+880 1X XX-XXXXXX</p>
                <p class="text-white/40 text-xs">Sat–Thu: 9AM–8PM (Bangladesh Time)</p>
            </div>
        </div>
    </div>
</div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
document.getElementById('group-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn  = document.getElementById('group-submit');
    const text = document.getElementById('group-btn-text');
    const alert = document.getElementById('group-alert');
    btn.disabled = true;
    text.textContent = 'Sending...';
    alert.className = 'hidden';

    // Build enriched message from form data
    const fd = new FormData(this);
    const org  = fd.get('organization') || 'Not specified';
    const size = fd.get('group_size')   || '?';
    const month= fd.get('preferred_month') || 'Flexible';
    const dur  = fd.get('duration')     || '10 Days';
    const bud  = fd.get('budget')       || 'Not specified';
    const msg  = fd.get('message')      || '';
    fd.set('message',
        `GROUP BOOKING INQUIRY\n\nOrganization: ${org}\nGroup Size: ${size} people\nPreferred Month: ${month}\nDuration: ${dur}\nBudget/person: ${bud}\n\nAdditional Requirements:\n${msg}`
    );

    try {
        const res  = await fetch(`<?= BASE_URL ?>/api/contact-handler.php`, { method:'POST', body: fd });
        const data = await res.json();
        alert.className = `rounded-xl p-4 mb-6 text-sm font-medium ${data.success ? 'bg-secondary/10 border border-secondary/30 text-secondary' : 'bg-red-500/10 border border-red-500/30 text-red-400'}`;
        alert.textContent = data.success ? 'Thank you! Our group coordinator will contact you within 24 hours.' : data.message;
        alert.scrollIntoView({ behavior:'smooth', block:'nearest' });
        if (data.success) {
            this.reset();
            // Also open WhatsApp for quick follow-up
            const waMsg = encodeURIComponent(`Hi TravHub! I just submitted a group booking inquiry for ${size} people. Please contact me.`);
            setTimeout(() => window.open(`https://wa.me/8801000000000?text=${waMsg}`, '_blank'), 1500);
        }
    } catch(err) {
        alert.className = 'rounded-xl p-4 mb-6 text-sm font-medium bg-red-500/10 border border-red-500/30 text-red-400';
        alert.textContent = 'Something went wrong. Please contact us directly on WhatsApp.';
    }

    btn.disabled = false;
    text.textContent = 'Submit Group Inquiry';
});
</script>
</body>
</html>
