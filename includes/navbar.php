<?php
// FILE PATH: /includes/navbar.php
$wa = function_exists('getSetting') ? getSetting('whatsapp_number', '+8801805460206') : '+8801805460206';
$curPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 bg-transparent py-6">
  <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
    <!-- Logo -->
    <a href="<?= BASE_URL ?>/pages/index.php" class="flex items-center gap-2">
      <div class="w-10 h-10 bg-gradient-to-br from-teal to-secondary rounded-xl flex items-center justify-center shadow-lg shadow-teal/20">
        <i data-lucide="globe" class="text-primary w-6 h-6"></i>
      </div>
      <span class="text-2xl font-bold tracking-tighter">Trav<span class="text-secondary">Hub</span></span>
    </a>

    <!-- Desktop Nav -->
    <div class="hidden lg:flex items-center gap-8">
      <a href="<?= BASE_URL ?>/pages/packages.php"        class="nav-link text-sm font-medium text-white/80 hover:text-secondary transition-colors">Packages</a>
      <a href="<?= BASE_URL ?>/pages/package-builder.php" class="nav-link text-sm font-medium text-white/80 hover:text-secondary transition-colors">Custom Builder</a>
      <a href="<?= BASE_URL ?>/pages/hotels.php"          class="nav-link text-sm font-medium text-white/80 hover:text-secondary transition-colors">Hotels</a>
      <a href="<?= BASE_URL ?>/pages/transport.php"       class="nav-link text-sm font-medium text-white/80 hover:text-secondary transition-colors">Transport</a>
      <a href="<?= BASE_URL ?>/pages/special-deals.php"   class="nav-link text-sm font-medium text-white/80 hover:text-secondary transition-colors">Deals</a>
      <div class="relative group">
        <button class="text-sm font-medium text-white/80 hover:text-secondary transition-colors flex items-center gap-1">
          More <i data-lucide="chevron-down" class="w-4 h-4"></i>
        </button>
        <div class="absolute top-full left-0 mt-2 w-48 bg-navy/95 backdrop-blur-xl border border-white/10 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
          <a href="<?= BASE_URL ?>/pages/hajj.php"          class="block px-4 py-3 text-sm text-white/80 hover:text-secondary hover:bg-white/5 transition-colors">Hajj Packages</a>
          <a href="<?= BASE_URL ?>/pages/group-booking.php" class="block px-4 py-3 text-sm text-white/80 hover:text-secondary hover:bg-white/5 transition-colors">Group Booking</a>
          <a href="<?= BASE_URL ?>/pages/contact.php"       class="block px-4 py-3 text-sm text-white/80 hover:text-secondary hover:bg-white/5 transition-colors">Contact</a>
          <div class="border-t border-white/10 mt-1"></div>
          <a href="<?= BASE_URL ?>/pages/visa-guide.php"    class="block px-4 py-3 text-sm text-white/80 hover:text-secondary hover:bg-white/5 transition-colors">Visa Guide</a>
          <a href="<?= BASE_URL ?>/pages/help-center.php"   class="block px-4 py-3 text-sm text-white/80 hover:text-secondary hover:bg-white/5 transition-colors">Help Center</a>
        </div>
      </div>
    </div>

    <!-- Desktop Actions -->
    <div class="hidden lg:flex items-center gap-4">
      <!-- Currency Selector -->
      <div class="flex items-center gap-1 bg-white/5 border border-white/10 rounded-full px-3 py-1.5" id="currency-selector">
        <button data-currency="SAR" class="currency-btn text-xs font-bold px-2 py-1 rounded-full bg-secondary text-primary transition-all">SAR</button>
        <button data-currency="USD" class="currency-btn text-xs font-bold px-2 py-1 rounded-full text-white/60 hover:text-white transition-all">USD</button>
        <button data-currency="BDT" class="currency-btn text-xs font-bold px-2 py-1 rounded-full text-white/60 hover:text-white transition-all">BDT</button>
      </div>
      <!-- User Account -->
      <?php if (function_exists('isUserLoggedIn') && isUserLoggedIn()): ?>
      <a href="<?= BASE_URL ?>/pages/user-dashboard.php" class="flex items-center gap-2 bg-white/5 border border-white/10 hover:border-secondary/40 text-white/80 hover:text-white font-semibold py-2 px-4 rounded-xl transition-all text-sm">
        <i data-lucide="user" class="w-4 h-4 text-secondary"></i>
        <?= htmlspecialchars($_SESSION['user_name'] ?? 'My Account') ?>
      </a>
      <?php else: ?>
      <a href="<?= BASE_URL ?>/pages/user-dashboard.php" class="flex items-center gap-2 bg-white/5 border border-white/10 hover:border-secondary/40 text-white/80 hover:text-white font-semibold py-2 px-4 rounded-xl transition-all text-sm">
        <i data-lucide="user" class="w-4 h-4"></i> My Account
      </a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/pages/contact.php" class="bg-secondary hover:bg-emerald text-primary font-semibold py-2.5 px-6 rounded-xl transition-all duration-300 text-sm">Inquire Now</a>
    </div>

    <!-- Mobile Toggle -->
    <button id="mobile-menu-toggle" class="lg:hidden text-white p-1">
      <i data-lucide="menu" id="menu-icon" class="w-6 h-6"></i>
    </button>
  </div>

  <!-- Mobile Menu -->
  <div id="mobile-menu" class="hidden lg:hidden absolute top-full left-0 right-0 bg-navy/95 backdrop-blur-xl border-b border-white/10 p-6 flex flex-col gap-1">
    <a href="<?= BASE_URL ?>/pages/packages.php"        class="py-3 px-4 text-base font-medium hover:text-secondary hover:bg-white/5 rounded-xl transition-all">Packages</a>
    <a href="<?= BASE_URL ?>/pages/package-builder.php" class="py-3 px-4 text-base font-medium hover:text-secondary hover:bg-white/5 rounded-xl transition-all">Custom Builder</a>
    <a href="<?= BASE_URL ?>/pages/hotels.php"          class="py-3 px-4 text-base font-medium hover:text-secondary hover:bg-white/5 rounded-xl transition-all">Hotels</a>
    <a href="<?= BASE_URL ?>/pages/transport.php"       class="py-3 px-4 text-base font-medium hover:text-secondary hover:bg-white/5 rounded-xl transition-all">Transport</a>
    <a href="<?= BASE_URL ?>/pages/special-deals.php"   class="py-3 px-4 text-base font-medium hover:text-secondary hover:bg-white/5 rounded-xl transition-all">Deals</a>
    <a href="<?= BASE_URL ?>/pages/hajj.php"            class="py-3 px-4 text-base font-medium hover:text-secondary hover:bg-white/5 rounded-xl transition-all">Hajj</a>
    <a href="<?= BASE_URL ?>/pages/group-booking.php"   class="py-3 px-4 text-base font-medium hover:text-secondary hover:bg-white/5 rounded-xl transition-all">Group Booking</a>
    <a href="<?= BASE_URL ?>/pages/contact.php"         class="py-3 px-4 text-base font-medium hover:text-secondary hover:bg-white/5 rounded-xl transition-all">Contact</a>
    <div class="border-t border-white/10 my-2"></div>
    <!-- Currency selector mobile -->
    <div class="flex items-center gap-2 px-4 py-2">
      <span class="text-xs text-white/40">Currency:</span>
      <button data-currency="SAR" class="currency-btn text-xs font-bold px-3 py-1.5 rounded-lg bg-secondary text-primary transition-all">SAR</button>
      <button data-currency="USD" class="currency-btn text-xs font-bold px-3 py-1.5 rounded-lg bg-white/5 text-white/60 transition-all">USD</button>
      <button data-currency="BDT" class="currency-btn text-xs font-bold px-3 py-1.5 rounded-lg bg-white/5 text-white/60 transition-all">BDT</button>
    </div>
    <?php if (function_exists('isUserLoggedIn') && isUserLoggedIn()): ?>
    <a href="<?= BASE_URL ?>/pages/user-dashboard.php" class="mt-1 py-3 px-4 text-base font-medium text-secondary hover:bg-secondary/10 rounded-xl transition-all flex items-center gap-2">
      <i data-lucide="user" class="w-4 h-4"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'My Account') ?>
    </a>
    <?php else: ?>
    <a href="<?= BASE_URL ?>/pages/user-dashboard.php" class="mt-1 py-3 px-4 text-base font-medium hover:text-secondary hover:bg-white/5 rounded-xl transition-all flex items-center gap-2">
      <i data-lucide="user" class="w-4 h-4"></i> My Account / Login
    </a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/pages/contact.php" class="mt-2 bg-secondary hover:bg-emerald text-primary font-semibold py-3 px-6 rounded-xl transition-all text-center">Inquire Now</a>
  </div>
</nav>