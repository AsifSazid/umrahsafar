<?php
// FILE PATH: /includes/sidebar.php
// Shared admin sidebar — every admin/*.php page includes this instead of
// keeping its own copy. "Active" tab highlighting is automatic (based on
// the current filename), so no per-page variables are needed before
// including this file.

// ── Role → allowed sidebar tabs (single source: includes/functions.php) ──
// getSidebarPermissions()/canAccessTab() are shared with every
// admin/*.php page's own content-level check, so the sidebar and the
// page itself can never disagree about what a role can reach.
?>
<aside class="w-64 bg-navy border-r border-white/5 p-6 flex-col shrink-0 hidden lg:flex">
  <a href="<?= BASE_URL ?>/pages/index.php" class="flex items-center gap-2 mb-10">
    <div class="w-9 h-9 bg-gradient-to-br from-teal-400 to-secondary rounded-xl flex items-center justify-center">
      <i data-lucide="globe" class="w-5 h-5 text-primary"></i>
    </div>
    <span class="font-bold text-lg">Trav<span class="text-secondary">Hub</span></span>
  </a>
  <nav class="space-y-1 flex-1">
    <?php foreach([
      ['dashboard',      'layout-dashboard', 'Bookings'],
      ['packages',       'box',              'Packages'],
      ['service-levels', 'star',             'Service Levels'],
      ['visa-types',     'file-text',        'Visa Types'],
      ['flights',        'plane',            'Flights'],
      ['hotels',         'bed',              'Hotels'],
      ['transport',      'car',              'Transport'],
      ['vehicle-types',  'truck',            'Vehicle Types'],
      ['room-board-types', 'utensils-crossed', 'Room Board Types'],
      ['ziarah',         'landmark',         'Ziarah'],
      ['moyallem',       'users',            'Moyallem'],
      ['meals',          'utensils',         'Meals'],
      ['users',          'user-cog',         'Users'],
      ['system-roles',   'shield',           'System Roles'],
      ['settings',       'settings',         'Settings'],
      ['change-password','lock',             'Password'],
    ] as [$tab,$icon,$label]):
      if (!canAccessTab($tab)) continue;
    ?>
    <a href="<?= BASE_URL ?>/admin/<?= $tab ?>.php"
       class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= basename($_SERVER['PHP_SELF'],'.php')===$tab?'bg-secondary/10 text-secondary border border-secondary/20':'text-white/60 hover:bg-white/5 hover:text-white' ?>">
      <i data-lucide="<?= $icon ?>" class="w-4 h-4"></i> <?= $label ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <a href="<?= BASE_URL ?>/admin/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/40 hover:text-red-400 hover:bg-red-400/5 transition-all mt-4">
    <i data-lucide="log-out" class="w-4 h-4"></i> Logout
  </a>
</aside>