<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'Umrah Visa Guide for Bangladeshi Citizens | TravHub';
include dirname(__DIR__) . '/includes/header.php';
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="pt-28 pb-24">
<div class="max-w-4xl mx-auto px-6">

    <!-- Header -->
    <div class="text-center mb-14">
        <span class="text-secondary font-bold tracking-widest uppercase text-xs">Official Requirements</span>
        <h1 class="text-4xl lg:text-5xl font-bold mt-2">Umrah Visa Guide</h1>
        <p class="text-white/40 mt-4 max-w-xl mx-auto">Complete documentation checklist for Bangladeshi citizens applying for an Umrah visa through TravHub.</p>
        <div class="mt-6 inline-flex items-center gap-2 bg-secondary/5 border border-secondary/20 text-secondary text-sm font-medium px-4 py-2 rounded-full">
            <i data-lucide="info" class="w-4 h-4"></i> Last updated: April 2026 — TravHub handles all visa submissions
        </div>
    </div>

    <!-- Overview cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
        <?php $stats = [['⏱','Processing Time','3–7 working days'],['💰','Visa Fee','SR 450 (~৳14,600)'],['📅','Validity','30 days from issue'],['🛂','Type','Single Entry']]; ?>
        <?php foreach ($stats as [$icon,$label,$val]): ?>
        <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-center">
            <p class="text-2xl mb-2"><?= $icon ?></p>
            <p class="text-xs text-white/40 uppercase tracking-wider mb-1"><?= $label ?></p>
            <p class="font-bold text-sm"><?= $val ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Document Checklist -->
    <div class="bg-white/5 border border-white/10 rounded-2xl p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
            <i data-lucide="clipboard-list" class="w-6 h-6 text-secondary"></i> Required Documents Checklist
        </h2>
        <?php
        $docs = [
            ['Passport (Original)','Valid for at least 6 months beyond return date. Must have at least 2 blank visa pages.','mandatory'],
            ['Passport-size Photos','2 recent photos (white background, no glasses, 4×6 cm).','mandatory'],
            ['National ID / NID','Both sides photocopy of Bangladesh National ID.','mandatory'],
            ['Vaccination Certificate','Meningococcal (ACWY) vaccine certificate — mandatory by Saudi authorities. Must be taken at least 10 days before travel.','mandatory'],
            ['Mahram Proof (for women)','Women under 45 must travel with a mahram. Submit marriage certificate or birth certificate proving relationship.','conditional'],
            ['Women 45+ Without Mahram','Can travel in an organized group. Submit a declaration form — TravHub provides this.','conditional'],
            ['Flight Itinerary','Confirmed round-trip ticket (or booking confirmation). TravHub provides this after booking confirmation.','provided'],
            ['Hotel Confirmation','Proof of hotel booking in Makkah and Madinah for the entire stay. TravHub provides this.','provided'],
        ];
        foreach ($docs as [$title,$desc,$type]):
            $color = $type === 'mandatory' ? 'secondary' : ($type === 'conditional' ? 'yellow-400' : 'teal');
            $badge = $type === 'mandatory' ? 'Required' : ($type === 'conditional' ? 'Conditional' : 'TravHub Provides');
        ?>
        <div class="flex gap-4 py-5 border-b border-white/5 last:border-0">
            <div class="w-5 h-5 rounded-full bg-<?= $color ?>/10 border border-<?= $color ?>/30 flex items-center justify-center shrink-0 mt-0.5">
                <div class="w-2.5 h-2.5 rounded-full bg-<?= $color ?>"></div>
            </div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <p class="font-semibold"><?= htmlspecialchars($title) ?></p>
                    <span class="text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full bg-<?= $color ?>/10 text-<?= $color ?>"><?= $badge ?></span>
                </div>
                <p class="text-white/50 text-sm leading-relaxed"><?= htmlspecialchars($desc) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Process timeline -->
    <div class="bg-white/5 border border-white/10 rounded-2xl p-8 mb-8">
        <h2 class="text-2xl font-bold mb-8 flex items-center gap-3">
            <i data-lucide="milestone" class="w-6 h-6 text-secondary"></i> Visa Application Process
        </h2>
        <?php
        $steps = [
            ['1','Submit Documents','Upload all documents to TravHub or bring to our Dhaka office.'],
            ['2','Verification','Our visa team checks all documents (1 working day).'],
            ['3','Submission to Embassy','We submit your application to the Saudi Embassy.'],
            ['4','Processing','Saudi authorities process the application (3–5 working days typically).'],
            ['5','Visa Issued','Visa is stamped in passport and returned to you.'],
            ['6','Ready to Travel','You\'re all set! TravHub provides final travel briefing.'],
        ];
        foreach ($steps as $i => [$num,$title,$desc]):
        ?>
        <div class="flex gap-4 <?= $i < count($steps)-1 ? 'mb-6' : '' ?>">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full bg-secondary/10 border-2 border-secondary text-secondary font-bold text-sm flex items-center justify-center shrink-0"><?= $num ?></div>
                <?php if ($i < count($steps)-1): ?><div class="w-0.5 h-full bg-secondary/20 my-2 grow"></div><?php endif; ?>
            </div>
            <div class="pb-2">
                <p class="font-semibold mb-0.5"><?= htmlspecialchars($title) ?></p>
                <p class="text-white/50 text-sm"><?= htmlspecialchars($desc) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- FAQ -->
    <div class="bg-white/5 border border-white/10 rounded-2xl p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
            <i data-lucide="help-circle" class="w-6 h-6 text-secondary"></i> Common Visa Questions
        </h2>
        <?php
        $faqs = [
            ['Can I apply for the visa myself?','No. Bangladeshi citizens must apply through a licensed Hajj/Umrah agency registered with the Ministry of Religious Affairs. TravHub is fully licensed.'],
            ['What if my passport expires soon?','Your passport must be valid for at least 6 months beyond your return date. Renew before applying.'],
            ['Can my wife travel without me?','Women under 45 must have a mahram companion. Women 45+ can travel with an organized group and a declaration form.'],
            ['How early should I apply?','Submit documents at least 30 days before departure to ensure sufficient time for processing.'],
            ['Is the meningitis vaccine really mandatory?','Yes. Saudi Arabia requires proof of meningococcal ACWY vaccination. It must be administered at least 10 days before entry.'],
        ];
        foreach ($faqs as $i => [$q,$a]):
        ?>
        <div class="border-b border-white/5 last:border-0">
            <button class="faq-q w-full text-left py-5 flex justify-between items-center gap-4" data-idx="<?= $i ?>">
                <span class="font-medium"><?= htmlspecialchars($q) ?></span>
                <i data-lucide="plus" class="w-4 h-4 text-secondary shrink-0 faq-icon"></i>
            </button>
            <div class="faq-a hidden pb-5 text-white/50 text-sm leading-relaxed"><?= htmlspecialchars($a) ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- CTA -->
    <div class="bg-secondary/5 border border-secondary/20 rounded-2xl p-8 text-center">
        <h3 class="text-xl font-bold mb-3">Ready to start your visa application?</h3>
        <p class="text-white/50 text-sm mb-6">TravHub handles all visa paperwork. Just bring your documents to our office or upload them online.</p>
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="<?= BASE_URL ?>/pages/contact.php" class="bg-secondary hover:bg-emerald text-primary font-semibold py-3 px-8 rounded-xl transition-all duration-300">Start Visa Process</a>
            <a href="https://wa.me/8801000000000?text=I+need+help+with+my+Umrah+visa+application." target="_blank" class="flex items-center gap-2 border border-secondary text-secondary hover:bg-secondary hover:text-primary font-semibold py-3 px-6 rounded-xl transition-all">
                <i data-lucide="message-circle" class="w-4 h-4"></i> Ask on WhatsApp
            </a>
        </div>
    </div>
</div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
document.querySelectorAll('.faq-q').forEach(btn => {
    btn.addEventListener('click', () => {
        const answer = btn.nextElementSibling;
        const icon   = btn.querySelector('.faq-icon');
        const isOpen = !answer.classList.contains('hidden');
        answer.classList.toggle('hidden', isOpen);
        icon.setAttribute('data-lucide', isOpen ? 'plus' : 'minus');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
});
</script>
</body>
</html>
