<?php
// WhatsApp number — pulled from site_settings (Admin → Settings → Contact
// Information → WhatsApp Number). Falls back to a placeholder when not set.
$footerWa = function_exists('getSetting') ? getSetting('whatsapp_number', '') : '';
?>
<footer class="bg-dark pt-20 pb-10 border-t border-white/5">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">

            <!-- Brand -->
            <div class="space-y-6">
                <a href="<?= BASE_URL ?>/pages/index.php" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-teal to-secondary rounded-lg flex items-center justify-center">
                        <i data-lucide="globe" class="text-primary w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tighter">Trav<span class="text-secondary">Hub</span></span>
                </a>
                <p class="text-white/40 text-sm leading-relaxed">Leading the way in modern, technology-driven spiritual travel from Bangladesh. We make your Umrah journey seamless and meaningful.</p>
                <div class="flex gap-4">
                    <!-- Lucide dropped brand/logo icons in recent versions — using inline SVGs
                         for Facebook/Twitter/Instagram instead of data-lucide, which would
                         otherwise fail silently with "icon name was not found" console errors. -->
                    <a href="#" class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center hover:bg-secondary hover:text-primary transition-all" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.91h-2.34V22c4.78-.76 8.44-4.92 8.44-9.94Z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center hover:bg-secondary hover:text-primary transition-all" aria-label="Twitter / X">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M18.9 2H22l-7.2 8.23L23 22h-6.9l-5.4-6.6L4.6 22H1.5l7.7-8.8L1 2h7.1l4.9 6.02L18.9 2Zm-1.2 18h1.7L7.4 3.9H5.6L17.7 20Z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center hover:bg-secondary hover:text-primary transition-all" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37Z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                    <a href="https://wa.me/<?= $footerWa !== '' ? ltrim($footerWa,'+') : '8801000000000' ?>" target="_blank" class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center hover:bg-secondary hover:text-primary transition-all">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-base font-bold mb-6">Quick Links</h4>
                <ul class="space-y-3 text-white/40 text-sm">
                    <li><a href="<?= BASE_URL ?>/pages/packages.php"        class="hover:text-secondary transition-colors">Umrah Packages</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/package-builder.php" class="hover:text-secondary transition-colors">Custom Builder</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/hajj.php"            class="hover:text-secondary transition-colors">Hajj 2026</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/hotels.php"          class="hover:text-secondary transition-colors">Hotel Booking</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/transport.php"       class="hover:text-secondary transition-colors">Transport Services</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/special-deals.php"   class="hover:text-secondary transition-colors">Special Deals</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/visa-guide.php"      class="hover:text-secondary transition-colors">Visa Guide</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h4 class="text-base font-bold mb-6">Support</h4>
                <ul class="space-y-3 text-white/40 text-sm">
                    <li><a href="<?= BASE_URL ?>/admin/index.php"     class="hover:text-secondary transition-colors">Admin Login</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/help-center.php"     class="hover:text-secondary transition-colors">Help Center</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/terms.php"           class="hover:text-secondary transition-colors">Terms of Service</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/privacy-policy.php"  class="hover:text-secondary transition-colors">Privacy Policy</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/cookies.php"         class="hover:text-secondary transition-colors">Cookie Policy</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/contact.php"         class="hover:text-secondary transition-colors">Contact Us</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/sitemap.php"         class="hover:text-secondary transition-colors">Sitemap</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="text-base font-bold mb-6">Contact Us</h4>
                <ul class="space-y-4 text-white/40 text-sm">
                    <li class="flex items-start gap-3">
                        <i data-lucide="mail" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i>
                        <span>info@travhub.com.bd</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="phone" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i>
                        <span><?= $footerWa !== '' ? htmlspecialchars($footerWa) : '+880 1X XX-XXXXXX' ?></span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="map-pin" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i>
                        <span>Dhaka, Bangladesh</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="message-circle" class="w-4 h-4 text-secondary mt-0.5 shrink-0"></i>
                        <a href="https://wa.me/<?= $footerWa !== '' ? ltrim($footerWa,'+') : '8801000000000' ?>" class="hover:text-secondary transition-colors">WhatsApp Consultation</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="pt-8 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-4 text-white/20 text-xs uppercase tracking-widest">
        <p class="flex items-center gap-2">
            <span>© <?= date('Y') ?> TRAVHUB. ALL RIGHTS RESERVED. |</span>
            <span class="inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 40" class="w-5 h-auto rounded-sm shrink-0">
                    <defs>
                        <clipPath id="flag-clip-6040">
                            <rect width="60" height="40" rx="8" ry="8" />
                        </clipPath>
                    </defs>
                    <rect width="60" height="40" rx="8" ry="8" fill="#004d38" />
                    <g clip-path="url(#flag-clip-6040)">
                        <rect x="2.5" y="2" width="55" height="36" rx="6" ry="6" fill="#016648" />
                        <path d="M 52 4 C 57 10 57 30 45 40 L 60 40 L 60 4 Z" fill="#00533c" opacity="0.45" />
                        <circle cx="27" cy="20" r="10.5" fill="#f42a41" />
                    </g>
                </svg>
                <span>BANGLADESH</span>
            </span>
        </p>
            <div class="flex gap-6">
                <a href="<?= BASE_URL ?>/pages/sitemap.php"        class="hover:text-white/50 transition-colors">Sitemap</a>
                <a href="<?= BASE_URL ?>/pages/cookies.php"        class="hover:text-white/50 transition-colors">Cookies</a>
                <a href="<?= BASE_URL ?>/pages/privacy-policy.php" class="hover:text-white/50 transition-colors">Privacy</a>
                <a href="<?= BASE_URL ?>/pages/terms.php"          class="hover:text-white/50 transition-colors">Terms</a>
            </div>
        </div>
    </div>
</footer>