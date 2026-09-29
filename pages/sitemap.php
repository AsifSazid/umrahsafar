<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'Sitemap | TravHub';
include dirname(__DIR__) . '/includes/header.php';
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>
<main class="pt-28 pb-24">
<div class="max-w-4xl mx-auto px-6">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold">Sitemap</h1>
        <p class="text-white/40 mt-3">All pages on TravHub</p>
    </div>
    <div class="grid md:grid-cols-2 gap-8">
        <?php
        $sections = [
            ['Umrah Packages','package',[
                ['Home','/pages/index.php'],
                ['All Packages','/pages/packages.php'],
                ['Custom Builder','/pages/package-builder.php'],
                ['Special Deals','/pages/special-deals.php'],
            ]],
            ['Hajj & Services','star',[
                ['Hajj 2026','/pages/hajj.php'],
                ['Hotels','/pages/hotels.php'],
                ['Transport','/pages/transport.php'],
                ['Visa Guide','/pages/visa-guide.php'],
            ]],
            ['Support','help-circle',[
                ['Admin Login','/admin/index.php'],
                ['Help Center','/pages/help-center.php'],
                ['Contact Us','/pages/contact.php'],
                ['Booking Confirmation','/pages/booking-confirmation.php'],
            ]],
            ['Legal','file-text',[
                ['Privacy Policy','/pages/privacy.php'],
                ['Terms of Service','/pages/terms.php'],
                ['Cookie Policy','/pages/cookies.php'],
                ['Sitemap','/pages/sitemap.php'],
            ]],
        ];
        foreach ($sections as [$title,$icon,$links]):
        ?>
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 bg-secondary/10 rounded-xl flex items-center justify-center">
                    <i data-lucide="<?= $icon ?>" class="w-4 h-4 text-secondary"></i>
                </div>
                <h2 class="font-bold text-base"><?= htmlspecialchars($title) ?></h2>
            </div>
            <ul class="space-y-2.5">
                <?php foreach ($links as [$label,$href]): ?>
                <li><a href="<?= BASE_URL . $href ?>" class="flex items-center gap-2 text-white/60 hover:text-secondary transition-colors text-sm">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-secondary/50"></i> <?= htmlspecialchars($label) ?>
                </a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
