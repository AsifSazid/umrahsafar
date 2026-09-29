<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'Terms of Service | TravHub';
include dirname(__DIR__) . '/includes/header.php';
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>
<main class="pt-28 pb-24">
<div class="max-w-3xl mx-auto px-6">
    <div class="mb-10">
        <span class="text-secondary font-bold tracking-widest uppercase text-xs">Legal</span>
        <h1 class="text-4xl font-bold mt-2">Terms of Service</h1>
        <p class="text-white/40 mt-3 text-sm">Last updated: April 2026 · Governing law: Bangladesh</p>
    </div>

    <?php
    $sections = [
        ['Agreement', 'By booking with TravHub or using our website, you agree to these Terms of Service. If you do not agree, please do not use our services.'],
        ['Booking & Payment', 'A booking is confirmed only after full payment is received and written confirmation is issued by TravHub. Prices are quoted in SAR, BDT, and USD based on live exchange rates. Final prices are locked at the time of payment confirmation. TravHub accepts bKash, DBBL bank transfer, and cash at our Dhaka office.'],
        ['Cancellation & Refund Policy', "Cancellations must be submitted in writing (email or WhatsApp). Refund schedule:\n• 30+ days before departure: 90% refund\n• 15–29 days: 75% refund\n• 7–14 days: 50% refund\n• Less than 7 days: No refund\nVisa fees (SR 450) are non-refundable once submitted to the Saudi Embassy. Airline tickets are subject to airline cancellation policies."],
        ['Visa & Documentation', 'TravHub submits visa applications on behalf of customers. However, visa approval is at the sole discretion of Saudi Arabian authorities. TravHub is not liable for visa rejections. Customers must ensure all submitted documents are genuine and accurate. Providing false documentation is a criminal offence.'],
        ['Customer Responsibilities', 'Customers are responsible for: holding a valid passport (6+ months), obtaining the meningitis ACWY vaccination, arriving at the airport at the designated time, following the laws and customs of Saudi Arabia, and adhering to the group schedule during guided tours.'],
        ['TravHub Responsibilities', 'TravHub will: process visa applications diligently, provide confirmed hotel bookings, arrange transport as described in the package, provide a certified guide for group tours, and offer 24/7 emergency contact during the pilgrimage.'],
        ['Force Majeure', 'TravHub is not liable for failure to perform services due to circumstances beyond our control, including but not limited to: natural disasters, Saudi government policy changes, airline cancellations, political events, or pandemics. In such cases, TravHub will endeavour to arrange alternatives or provide partial refunds.'],
        ['Limitation of Liability', "TravHub's total liability to any customer shall not exceed the amount paid for the relevant booking. TravHub is not liable for personal injury, loss of luggage, or missed flights caused by customer negligence."],
        ['Governing Law', 'These terms are governed by the laws of Bangladesh. Disputes shall be resolved first through mediation in Dhaka, and if unresolved, through the courts of Bangladesh.'],
        ['Contact', 'For terms-related queries: info@travhub.com.bd · WhatsApp +880 1X XX-XXXXXX'],
    ];
    foreach ($sections as $i => [$title, $content]):
    ?>
    <div class="mb-8 <?= $i > 0 ? 'pt-8 border-t border-white/5' : '' ?>">
        <h2 class="text-lg font-bold text-secondary mb-3"><?= (string)($i+1) ?>. <?= htmlspecialchars($title) ?></h2>
        <p class="text-white/60 text-sm leading-relaxed whitespace-pre-line"><?= htmlspecialchars($content) ?></p>
    </div>
    <?php endforeach; ?>
</div>
</main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
