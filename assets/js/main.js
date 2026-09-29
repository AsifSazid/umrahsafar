/**
 * TravHub Main JS — shared across all pages
 * Navbar scroll, mobile menu, lucide init
 */
document.addEventListener('DOMContentLoaded', () => {

    // ── Lucide Icons ──
    if (typeof lucide !== 'undefined') lucide.createIcons();

    // ── Mobile menu toggle ──
    const toggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.getElementById('menu-icon');
    if (toggle && mobileMenu && menuIcon) {
        toggle.addEventListener('click', () => {
            const isOpen = !mobileMenu.classList.contains('hidden');
            mobileMenu.classList.toggle('hidden', isOpen);
            menuIcon.setAttribute('data-lucide', isOpen ? 'menu' : 'x');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    }

    // ── Active nav link highlight ──
    const path = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') && path.includes(link.getAttribute('href').replace('/pages/', '').replace('.php', ''))) {
            link.classList.add('text-secondary');
            link.classList.remove('text-white/80');
        }
    });

    // ── Navbar: glassmorphic while at the top, solid background once scrolled ──
    // Stays transparent/glassy over the hero so it doesn't compete with hero
    // imagery, but switches to a solid dark background once the user scrolls
    // past it so nav text/links stay readable over any page content below.
    const navbar = document.getElementById('navbar');
    if (navbar) {
        const SCROLL_THRESHOLD = 40; // px scrolled before switching to solid
        function updateNavbarBg() {
            const scrolled = window.scrollY > SCROLL_THRESHOLD;
            navbar.classList.toggle('bg-transparent', !scrolled);
            navbar.classList.toggle('bg-dark/95', scrolled);
            navbar.classList.toggle('backdrop-blur-xl', scrolled);
            navbar.classList.toggle('border-b', scrolled);
            navbar.classList.toggle('border-white/5', scrolled);
            navbar.classList.toggle('shadow-lg', scrolled);
            navbar.classList.toggle('py-6', !scrolled);
            navbar.classList.toggle('py-4', scrolled);
        }
        updateNavbarBg(); // set correct state immediately (e.g. on a page loaded already scrolled)
        window.addEventListener('scroll', updateNavbarBg, { passive: true });
    }

    // ── Fade-in on scroll (IntersectionObserver) ──
    const fadeEls = document.querySelectorAll('.fade-on-scroll');
    if (fadeEls.length && 'IntersectionObserver' in window) {
        const io = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('opacity-100', 'translate-y-0');
                    e.target.classList.remove('opacity-0', 'translate-y-4');
                }
            });
        }, { threshold: 0.1 });
        fadeEls.forEach(el => {
            el.classList.add('opacity-0', 'translate-y-4', 'transition-all', 'duration-700');
            io.observe(el);
        });
    }
});