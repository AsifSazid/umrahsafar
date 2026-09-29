<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'Help Center | TravHub Umrah Support';
include dirname(__DIR__) . '/includes/header.php';
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="pt-28 pb-24">
<div class="max-w-4xl mx-auto px-6">
    <div class="text-center mb-14">
        <span class="text-secondary font-bold tracking-widest uppercase text-xs">Support</span>
        <h1 class="text-4xl lg:text-5xl font-bold mt-2">Help Center</h1>
        <p class="text-white/40 mt-4">Find answers to the most common questions about Umrah booking with TravHub.</p>
    </div>

    <!-- Search bar -->
    <div class="relative mb-12">
        <i data-lucide="search" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-white/30"></i>
        <input type="text" id="faq-search" placeholder="Search your question..." class="w-full bg-white/5 border border-white/10 rounded-2xl pl-14 pr-6 py-4 text-base focus:border-secondary focus:outline-none transition-colors placeholder-white/20">
    </div>

    <!-- FAQ categories -->
    <?php
    $categories = [
        ['icon'=>'package','title'=>'Booking & Packages','faqs'=>[
            ['How do I book an Umrah package with TravHub?','You can book through our website using the Package Builder, or contact us directly via WhatsApp or the Contact page. Our team will confirm availability and send you a booking form.'],
            ['Can I modify my booking after confirmation?','Yes, modifications are possible up to 21 days before departure subject to availability. Changes to dates or hotel may incur additional charges.'],
            ['What is your cancellation policy?','Cancellations more than 30 days before departure: 10% cancellation fee. 15-29 days: 25%. 7-14 days: 50%. Less than 7 days: non-refundable.'],
            ['How do I pay for my package?','We accept bKash, DBBL bank transfer, and cash at our Dhaka office. Full payment is required at least 21 days before departure.'],
        ]],
        ['icon'=>'file-text','title'=>'Visa & Documents','faqs'=>[
            ['Does TravHub handle the Umrah visa?','Yes. TravHub is a licensed Umrah agency. We handle the complete visa application process on your behalf.'],
            ['How long does the Umrah visa take?','Typically 3-7 working days once all documents are submitted correctly. Apply at least 30 days before your departure.'],
            ['What documents do I need for the visa?','Passport (6 months validity), 2 photos (white background), NID copy, meningitis vaccination certificate, and mahram proof if applicable. See our Visa Guide for full details.'],
        ]],
        ['icon'=>'plane','title'=>'Flights & Travel','faqs'=>[
            ['Are flights included in the packages?','Most packages include return flights from Dhaka (DAC) to Jeddah (JED) or Madinah (MED). Check the package description. Land-only packages do not include flights.'],
            ['Which airlines does TravHub use?','We work with Biman Bangladesh Airlines, Saudi Airlines, Air Arabia, and other carriers based on availability and best value.'],
            ['What is the baggage allowance?','Standard economy allowance applies (typically 23kg checked + 7kg cabin). Business class passengers get 30kg. We will confirm your specific allowance before travel.'],
        ]],
        ['icon'=>'building','title'=>'Hotels & Accommodation','faqs'=>[
            ['How close are the hotels to Haram?','We offer hotels from 0m (attached to Haram) to 600m distance. The package details show exact distance. All our hotels are walkable or have shuttle service.'],
            ['Can I request a specific hotel?','Yes, when booking via the Custom Package Builder or contacting us directly. Availability and pricing varies.'],
            ['Are meals included?','Breakfast is included in most 4★ and 5★ packages. Economy packages are bed-only unless stated. Full-board options are available on request.'],
        ]],
        ['icon'=>'users','title'=>'Groups & Families','faqs'=>[
            ['Do you offer group bookings for madrasas or organizations?','Yes! We offer special group rates for 15+ travelers. Contact us directly for group pricing and dedicated coordination.'],
            ['Can women travel without a male guardian (mahram)?','Women under 45 must travel with a mahram. Women 45 and above can travel in an organized group with a notarized declaration. TravHub provides the required declaration form.'],
            ['Is there a discount for children?','Children under 2 (infants) travel free (no seat, no Umrah). Children 2-11 receive discounted rates on accommodation. Flights are subject to airline pricing.'],
        ]],
    ];
    foreach ($categories as $cat):
    ?>
    <div class="mb-10">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 bg-secondary/10 rounded-xl flex items-center justify-center">
                <i data-lucide="<?= $cat['icon'] ?>" class="w-5 h-5 text-secondary"></i>
            </div>
            <h2 class="text-xl font-bold"><?= htmlspecialchars($cat['title']) ?></h2>
        </div>
        <div class="space-y-3">
            <?php foreach ($cat['faqs'] as $i => $faq): ?>
            <div class="faq-item bg-white/5 border border-white/10 rounded-2xl overflow-hidden" data-question="<?= strtolower(htmlspecialchars($faq[0])) ?>">
                <button class="faq-toggle w-full flex justify-between items-center px-6 py-5 text-left gap-4">
                    <span class="font-medium"><?= htmlspecialchars($faq[0]) ?></span>
                    <i data-lucide="plus" class="w-5 h-5 text-secondary shrink-0 faq-icon transition-transform"></i>
                </button>
                <div class="faq-answer hidden px-6 pb-5 text-white/60 text-sm leading-relaxed border-t border-white/5 pt-4">
                    <?= htmlspecialchars($faq[1]) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- No results -->
    <div id="no-faq-results" class="hidden text-center py-12">
        <i data-lucide="search-x" class="w-12 h-12 text-white/20 mx-auto mb-4"></i>
        <p class="text-white/40">No matching questions found.</p>
    </div>

    <!-- Still need help -->
    <div class="mt-12 bg-secondary/5 border border-secondary/20 rounded-2xl p-8 text-center">
        <h3 class="text-xl font-bold mb-3">Still need help?</h3>
        <p class="text-white/50 text-sm mb-6">Our team is available 7 days a week to answer your questions.</p>
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="https://wa.me/8801000000000" target="_blank" class="flex items-center gap-2 bg-[#25D366] hover:bg-[#1ebe5c] text-white font-semibold py-3 px-6 rounded-xl transition-all text-sm">
                <i data-lucide="message-circle" class="w-4 h-4"></i> Chat on WhatsApp
            </a>
            <a href="<?= BASE_URL ?>/pages/contact.php" class="flex items-center gap-2 border border-secondary text-secondary hover:bg-secondary hover:text-primary font-semibold py-3 px-6 rounded-xl transition-all text-sm">
                <i data-lucide="mail" class="w-4 h-4"></i> Send Email
            </a>
        </div>
    </div>
</div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
// FAQ accordion
document.querySelectorAll('.faq-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const answer = btn.nextElementSibling;
        const icon   = btn.querySelector('.faq-icon');
        const isOpen = !answer.classList.contains('hidden');
        // Close all
        document.querySelectorAll('.faq-answer').forEach(a => a.classList.add('hidden'));
        document.querySelectorAll('.faq-icon').forEach(i => i.setAttribute('data-lucide','plus'));
        // Toggle this one
        if (!isOpen) {
            answer.classList.remove('hidden');
            icon.setAttribute('data-lucide','minus');
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
});

// Search
document.getElementById('faq-search').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    let visible = 0;
    document.querySelectorAll('.faq-item').forEach(item => {
        const match = !q || item.getAttribute('data-question').includes(q);
        item.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.querySelectorAll('.mb-10').forEach(section => {
        const hasVisible = [...section.querySelectorAll('.faq-item')].some(i => i.style.display !== 'none');
        section.style.display = hasVisible ? '' : 'none';
    });
    document.getElementById('no-faq-results').classList.toggle('hidden', visible > 0);
});
</script>
</body>
</html>
