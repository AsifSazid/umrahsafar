<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'Privacy Policy | TravHub';
include dirname(__DIR__) . '/includes/header.php';
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>
<main class="pt-28 pb-24">
<div class="max-w-3xl mx-auto px-6">
    <div class="mb-10">
        <span class="text-secondary font-bold tracking-widest uppercase text-xs">Legal</span>
        <h1 class="text-4xl font-bold mt-2">Privacy Policy</h1>
        <p class="text-white/40 mt-3 text-sm">Last updated: April 2026 · TravHub, Dhaka, Bangladesh</p>
    </div>

    <?php
    $sections = [
        ['Information We Collect', 'When you use TravHub, we collect information you provide directly — such as your name, email address, phone number, passport details, and travel preferences when making a booking or inquiry. We also collect technical information including IP address, browser type, and pages visited.'],
        ['How We Use Your Information', 'We use your information to process bookings, apply for Umrah and Hajj visas on your behalf, communicate about your journey, send confirmation emails and WhatsApp messages, and improve our services. We do not sell your personal information to third parties.'],
        ['Visa & Passport Data', 'Passport and national ID information submitted for visa processing is shared only with the Embassy of Saudi Arabia and relevant Bangladesh government authorities (Ministry of Religious Affairs) as required for Hajj and Umrah visa applications. This data is stored securely and deleted after visa issuance.'],
        ['Data Storage & Security', 'Your data is stored on secured servers. We use HTTPS encryption for all data in transit. Booking and inquiry records are retained for 3 years for legal and audit purposes, after which they are securely deleted.'],
        ['Cookies', 'We use cookies to remember your currency preference and session state. We do not use tracking cookies or third-party advertising cookies. See our Cookie Policy for details.'],
        ['Your Rights', 'You have the right to access, correct, or request deletion of your personal data. To exercise these rights, contact us at info@travhub.com.bd or via WhatsApp. We will respond within 7 working days.'],
        ['Third-Party Services', 'We use exchangerate-api.com for currency conversion (no personal data shared), and WhatsApp Business for customer communication. Flights are operated by third-party airlines under their own privacy policies.'],
        ['Changes to This Policy', 'We may update this policy periodically. Significant changes will be communicated via email or WhatsApp. Continued use of our services constitutes acceptance of the updated policy.'],
        ['Contact', 'For privacy-related inquiries: Email info@travhub.com.bd · WhatsApp +880 1X XX-XXXXXX · Address: Dhaka, Bangladesh'],
    ];
    foreach ($sections as $i => [$title, $content]):
    ?>
    <div class="mb-8 <?= $i > 0 ? 'pt-8 border-t border-white/5' : '' ?>">
        <h2 class="text-lg font-bold text-secondary mb-3"><?= (string)($i+1) ?>. <?= htmlspecialchars($title) ?></h2>
        <p class="text-white/60 text-sm leading-relaxed"><?= htmlspecialchars($content) ?></p>
    </div>
    <?php endforeach; ?>

    <div class="mt-12 bg-secondary/5 border border-secondary/20 rounded-2xl p-6 text-sm text-white/50">
        <p><strong class="text-white">TravHub</strong> is a licensed Umrah and Hajj travel agency based in Dhaka, Bangladesh. This privacy policy applies to travhub.com.bd and all associated services.</p>
    </div>
</div>
</main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
