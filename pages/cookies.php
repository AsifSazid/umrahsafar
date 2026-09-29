<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'Cookie Policy | TravHub';
include dirname(__DIR__) . '/includes/header.php';
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>
<main class="pt-28 pb-24">
<div class="max-w-3xl mx-auto px-6">
    <div class="mb-10">
        <span class="text-secondary font-bold tracking-widest uppercase text-xs">Legal</span>
        <h1 class="text-4xl font-bold mt-2">Cookie Policy</h1>
        <p class="text-white/40 mt-3 text-sm">Last updated: April 2026</p>
    </div>

    <div class="space-y-8">
        <div>
            <h2 class="text-lg font-bold text-secondary mb-3">What are cookies?</h2>
            <p class="text-white/60 text-sm leading-relaxed">Cookies are small text files stored in your browser. They help websites remember your preferences and improve your browsing experience.</p>
        </div>

        <div class="pt-8 border-t border-white/5">
            <h2 class="text-lg font-bold text-secondary mb-5">Cookies we use</h2>
            <div class="overflow-hidden rounded-2xl border border-white/10">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/10">
                            <th class="text-left px-5 py-3 text-white/50 text-xs uppercase tracking-wider">Cookie</th>
                            <th class="text-left px-5 py-3 text-white/50 text-xs uppercase tracking-wider">Purpose</th>
                            <th class="text-left px-5 py-3 text-white/50 text-xs uppercase tracking-wider">Duration</th>
                            <th class="text-left px-5 py-3 text-white/50 text-xs uppercase tracking-wider">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cookies = [
                            ['travhub_currency','Remembers your selected currency (SAR/USD/BDT)','1 year','Essential'],
                            ['travhub_builder','Saves your package builder progress across sessions','7 days','Functional'],
                            ['PHPSESSID','PHP session — CSRF protection and admin login','Session','Essential'],
                            ['travhub_rates','Caches exchange rates to reduce API calls','1 hour','Functional'],
                        ];
                        foreach ($cookies as $i => $c):
                        ?>
                        <tr class="border-b border-white/5 <?= $i%2?'':'bg-white/5' ?>">
                            <td class="px-5 py-4 font-mono text-xs text-secondary"><?= htmlspecialchars($c[0]) ?></td>
                            <td class="px-5 py-4 text-white/60 text-xs"><?= htmlspecialchars($c[1]) ?></td>
                            <td class="px-5 py-4 text-white/60 text-xs"><?= htmlspecialchars($c[2]) ?></td>
                            <td class="px-5 py-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full <?= $c[3]==='Essential'?'bg-secondary/10 text-secondary':'bg-teal/10 text-teal' ?>"><?= $c[3] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pt-8 border-t border-white/5">
            <h2 class="text-lg font-bold text-secondary mb-3">No tracking cookies</h2>
            <p class="text-white/60 text-sm leading-relaxed">TravHub does not use advertising cookies, social media tracking pixels, Google Analytics, or any third-party behavioral tracking. Your browsing on our site is not tracked for advertising purposes.</p>
        </div>

        <div class="pt-8 border-t border-white/5">
            <h2 class="text-lg font-bold text-secondary mb-3">Managing cookies</h2>
            <p class="text-white/60 text-sm leading-relaxed">You can clear cookies at any time through your browser settings. Note that clearing essential cookies will log you out of the admin panel and reset your currency preference. Most browsers allow you to block cookies — however, this may affect some functionality.</p>
        </div>

        <div class="pt-8 border-t border-white/5">
            <h2 class="text-lg font-bold text-secondary mb-3">Contact</h2>
            <p class="text-white/60 text-sm">Questions about cookies: <a href="mailto:info@travhub.com.bd" class="text-secondary hover:underline">info@travhub.com.bd</a></p>
        </div>
    </div>
</div>
</main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>