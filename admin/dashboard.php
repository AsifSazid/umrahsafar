<?php
// FILE PATH: /admin/dashboard.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();

// Super-admin + admin only get the "Sync to Booking" button — kept as an
// array so a future role can be added/removed here without touching the
// button-rendering logic below.
$syncAllowedRoles = ['super-admin', 'admin'];
$canSync = in_array($_SESSION['role_alias'] ?? '', $syncAllowedRoles, true);

// Same enum list as bookings.status / custom_builds.status — kept as one
// array so the dropdown and any future validation stay in sync.
$bookingStatusOptions = ['Contacted','Visa Processing','Visa Approved','Visa Rejected',
    'Hotel Processing','Hotel Confirmed','Flight Processing','Flight Confirmed',
    'Flight Date Changed','Payment Pending','Payment Received','Confirmed',
    'On Hold','Cancelled','Completed'];

try {
    $db = getDB();
    $totalBookings  = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $pendingCount   = $db->query("SELECT COUNT(*) FROM bookings WHERE status NOT IN ('Confirmed','Cancelled','Completed')")->fetchColumn();
    $confirmedCount = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'")->fetchColumn();
    $customBuilds   = $db->query("SELECT COUNT(*) FROM custom_builds")->fetchColumn();

    $recentBookings = $db->query("SELECT * FROM bookings ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($recentBookings as &$b) {
        $b['customer_infos'] = json_decode($b['customer_infos'] ?? '{}', true) ?: [];
        $b['final_prices']   = json_decode($b['final_prices'] ?? '{}', true) ?: [];
        $b['total_prices']   = json_decode($b['total_prices'] ?? '{}', true) ?: [];
        $b['discount']       = json_decode($b['discount'] ?? 'null', true);
    }
    unset($b);

    $recentCustom = $db->query("SELECT * FROM custom_builds ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($recentCustom as &$c) {
        $c['customer_infos'] = json_decode($c['customer_infos'] ?? '{}', true) ?: [];
        $c['persons']        = json_decode($c['persons'] ?? '{}', true) ?: [];
        $c['days']           = json_decode($c['days'] ?? '{}', true) ?: [];
        $c['total_prices']   = json_decode($c['total_prices'] ?? '{}', true) ?: [];
    }
    unset($c);

    // Which custom_builds already have a synced booking — lets the button
    // show "Synced" (disabled) instead of re-offering a sync that would
    // just fail on the bookings.uk_ref unique constraint.
    $syncedRefs = $db->query("SELECT ref_sys_id FROM bookings WHERE type = 'custom'")->fetchAll(PDO::FETCH_COLUMN);

    $meStmt = $db->prepare("SELECT u.name, u.username, r.name AS role_name FROM users u JOIN system_roles r ON r.sys_id = u.active_role_sys_id WHERE u.id = :id");
    $meStmt->execute([':id' => $_SESSION['admin_id']]);
    $me = $meStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $totalBookings = $pendingCount = $confirmedCount = $customBuilds = 0;
    $recentBookings = $recentCustom = $syncedRefs = [];
    $me = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Poppins','sans-serif']},colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625',emerald:'#3AAB71',teal:'#02CCFE'}}}}</script>
<style>body{background:#111625}</style>
</head>
<body class="text-white font-sans min-h-screen">
<div class="flex min-h-screen">

<!-- Sidebar -->
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>

<!-- Mobile top bar -->
<div class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-navy border-b border-white/10 px-4 py-3 flex items-center justify-between">
  <span class="font-bold text-lg">Trav<span class="text-secondary">Hub</span> Admin</span>
  <button id="mob-menu-btn" class="text-white"><i data-lucide="menu" class="w-6 h-6"></i></button>
</div>
<div id="mob-menu" class="hidden lg:hidden fixed top-14 left-0 right-0 z-30 bg-navy border-b border-white/10 p-4 space-y-1">
  <?php foreach([['dashboard','layout-dashboard','Bookings'],['packages','box','Packages'],['hotels','bed','Hotels'],['transport','car','Transport'],['ziarah','landmark','Ziarah'],['moyallem','users','Moyallem'],['meals','utensils','Meals'],['settings','settings','Settings'],['change-password','lock','Password']] as [$tab,$icon,$label]):
    if (!canAccessTab($tab)) continue;
  ?>
  <a href="<?= BASE_URL ?>/admin/<?= $tab ?>.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm <?= basename($_SERVER['PHP_SELF'],'.php')===$tab?'bg-secondary/10 text-secondary':'text-white/60 hover:bg-white/5' ?>">
    <i data-lucide="<?= $icon ?>" class="w-4 h-4"></i> <?= $label ?>
  </a>
  <?php endforeach; ?>
  <a href="<?= BASE_URL ?>/admin/logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-400"><i data-lucide="log-out" class="w-4 h-4"></i> Logout</a>
</div>

<!-- Main Content -->
<main class="flex-1 p-4 pt-20 lg:p-8 overflow-auto">
<?php if (!canAccessTab('dashboard')): ?>
  <?php $roleLabel = $me['role_name'] ?? ucfirst($_SESSION['role_alias'] ?? 'Guest'); ?>
  <div class="min-h-[70vh] flex flex-col items-center justify-center text-center gap-3">
    <p class="text-white/40 text-sm">Welcome back, <?= htmlspecialchars($me['name'] ?? $_SESSION['admin_username'] ?? 'Guest') ?> (<?= htmlspecialchars($roleLabel) ?>)</p>
    <h1 class="text-3xl lg:text-4xl font-bold text-white/80">You are <?= htmlspecialchars($roleLabel) ?>!</h1>
    <?php if (!empty($me['username'])): ?>
    <p class="text-white/30 text-xs">Your username: <span class="font-mono text-white/50"><?= htmlspecialchars($me['username']) ?></span></p>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="mb-8">
    <h1 class="text-2xl lg:text-3xl font-bold">Dashboard</h1>
    <p class="text-white/40 text-sm mt-1">Welcome back, <?= htmlspecialchars($me['name'] ?? $_SESSION['admin_username'] ?? 'Admin') ?><?= !empty($me['role_name']) ? ' (' . htmlspecialchars($me['role_name']) . ')' : '' ?></p>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php foreach([
      ['Total Bookings', $totalBookings,  'package',       'secondary'],
      ['Pending',        $pendingCount,   'clock',         'yellow-400'],
      ['Confirmed',      $confirmedCount, 'check-circle',  'teal'],
      ['Custom Builds',  $customBuilds,   'settings',      'white/60'],
    ] as [$label,$val,$icon,$color]): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs text-white/40 uppercase tracking-wider"><?= $label ?></p>
        <div class="w-8 h-8 bg-<?= $color ?>/10 rounded-lg flex items-center justify-center">
          <i data-lucide="<?= $icon ?>" class="w-4 h-4 text-<?= $color ?>"></i>
        </div>
      </div>
      <p class="text-3xl font-bold"><?= $val ?></p>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Quick Links -->
  <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-8">
    <?php foreach([
      ['/admin/packages.php',       'box',         'Packages',  'Manage'],
      ['/admin/transport.php',      'car',         'Transport', 'Routes'],
      ['/admin/moyallem.php',       'users',       'Moyallem',  'Services'],
      ['/admin/settings.php',       'settings',    'Settings',  'Config'],
      ['/admin/change-password.php','lock',        'Password',  'Security'],
    ] as [$href,$icon,$label,$sub]): ?>
    <a href="<?= BASE_URL . $href ?>" class="bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-2xl p-4 transition-all group">
      <i data-lucide="<?= $icon ?>" class="w-5 h-5 text-secondary mb-2"></i>
      <p class="text-xs font-bold"><?= $label ?></p>
      <p class="text-[10px] text-white/30"><?= $sub ?></p>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="grid lg:grid-cols-3 gap-6">
    <!-- Recent Bookings -->
    <div class="lg:col-span-2">
      <h2 class="font-bold mb-4 flex items-center gap-2 text-sm uppercase tracking-wider text-white/40">
        <i data-lucide="package" class="w-4 h-4"></i> Recent Bookings
      </h2>
      <div class="space-y-2">
        <?php if ($recentBookings): ?>
        <?php foreach ($recentBookings as $b): ?>
        <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3 flex items-center justify-between gap-3 flex-wrap" data-sys-id="<?= htmlspecialchars($b['sys_id']) ?>">
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-bold text-sm"><?= htmlspecialchars($b['sys_id']) ?></span>
              <?php if(($b['type']??'')==='custom'): ?>
              <span class="text-[9px] bg-teal/10 text-teal border border-teal/20 px-2 py-0.5 rounded-full">Custom</span>
              <?php endif; ?>
            </div>
            <p class="text-xs text-white/40 truncate"><?= htmlspecialchars($b['customer_infos']['name'] ?? '') ?> · <?= htmlspecialchars($b['customer_infos']['phone'] ?? '') ?></p>
          </div>
          <div class="flex items-center gap-3">
            <div class="text-right">
              <?php $hasDiscount = !empty($b['discount']); ?>
              <?php if ($hasDiscount && !empty($b['total_prices']['sar'][0])): ?>
              <span class="text-white/30 text-[10px] line-through block">SR <?= number_format($b['total_prices']['sar'][0],0) ?> · ৳<?= number_format($b['total_prices']['bdt'][0]??0,0) ?></span>
              <?php endif; ?>
              <?php if(!empty($b['final_prices']['sar'][0])): ?>
              <span class="text-secondary font-bold text-sm block">SR <?= number_format($b['final_prices']['sar'][0],0) ?></span>
              <?php endif; ?>
              <?php if(!empty($b['final_prices']['bdt'][0])): ?>
              <span class="text-white/40 text-[11px]">৳<?= number_format($b['final_prices']['bdt'][0],0) ?></span>
              <?php endif; ?>
            </div>
            <?php if (!empty($b['pdf_path'])): ?>
            <a href="<?= BASE_URL.'/'.htmlspecialchars($b['pdf_path']) ?>" target="_blank" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg" title="View PDF"><i data-lucide="file-text" class="w-3.5 h-3.5"></i></a>
            <?php endif; ?>
            <button onclick='openDiscountModal(<?= json_encode(["sys_id"=>$b["sys_id"],"discount"=>$b["discount"]??null], JSON_HEX_APOS|JSON_HEX_QUOT) ?>)' class="p-2 bg-white/5 hover:bg-white/10 rounded-lg" title="Discount"><i data-lucide="percent" class="w-3.5 h-3.5"></i></button>
            <div class="flex items-center gap-1.5 bg-dark border border-white/10 rounded-lg pl-2">
              <span class="w-2 h-2 rounded-full shrink-0 <?= bookingStatusDotClass($b['status']) ?>"></span>
              <select onchange="updateBookingStatus('<?= htmlspecialchars($b['sys_id'],ENT_QUOTES) ?>', this.value, this)" class="text-[10px] font-bold pl-1 pr-2 py-1.5 rounded-lg uppercase bg-dark text-white focus:outline-none">
                <?php foreach ($bookingStatusOptions as $opt): ?>
                <option value="<?= htmlspecialchars($opt) ?>" <?= $opt===$b['status']?'selected':'' ?>><?= htmlspecialchars($opt) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="text-center py-12 text-white/30 text-sm">No bookings yet.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Custom Builds -->
    <div>
      <h2 class="font-bold mb-4 flex items-center gap-2 text-sm uppercase tracking-wider text-white/40">
        <i data-lucide="settings" class="w-4 h-4"></i> Custom Builds
      </h2>
      <div class="space-y-2">
        <?php if ($recentCustom): ?>
        <?php foreach ($recentCustom as $c):
            $isSynced = in_array($c['sys_id'], $syncedRefs, true);
        ?>
        <div class="bg-white/5 border border-white/10 rounded-xl p-3" data-sys-id="<?= htmlspecialchars($c['sys_id']) ?>">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <p class="font-bold text-xs"><?= htmlspecialchars($c['sys_id']) ?></p>
              <p class="text-[10px] text-white/40 truncate"><?= htmlspecialchars($c['customer_infos']['name'] ?? '') ?></p>
              <p class="text-[10px] text-white/30"><?= (int)($c['days']['total']??0) ?> days<?= !empty($c['persons']['adults']) ? ' · '.(int)$c['persons']['adults'].' adult(s)' : '' ?></p>
            </div>
            <div class="text-right shrink-0">
              <span class="text-secondary font-bold text-xs block">
                <?= !empty($c['total_prices']['sar'][0]) ? 'SR '.number_format($c['total_prices']['sar'][0],0) : '' ?>
              </span>
              <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full uppercase mt-1 inline-block <?= bookingStatusBadgeClass($c['status']) ?>"><?= htmlspecialchars($c['status']) ?></span>
            </div>
          </div>
          <div class="mt-2 pt-2 border-t border-white/5 flex gap-2">
            <?php if (!empty($c['pdf_path'])): ?>
            <a href="<?= BASE_URL.'/'.htmlspecialchars($c['pdf_path']) ?>" target="_blank" class="flex items-center justify-center gap-1.5 text-[10px] bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg" title="View PDF"><i data-lucide="file-text" class="w-3 h-3"></i> PDF</a>
            <?php endif; ?>
            <?php if ($canSync): ?>
            <?php if ($isSynced): ?>
            <span class="flex-1 inline-flex items-center justify-center gap-1.5 text-[10px] bg-white/5 text-white/30 font-bold px-3 py-1.5 rounded-lg"><i data-lucide="check" class="w-3 h-3"></i> Synced</span>
            <?php else: ?>
            <button onclick="syncCustomBuild('<?= htmlspecialchars($c['sys_id'],ENT_QUOTES) ?>', this)" class="flex-1 flex items-center justify-center gap-1.5 text-[10px] bg-secondary/10 hover:bg-secondary/20 text-secondary font-bold px-3 py-1.5 rounded-lg"><i data-lucide="arrow-right-circle" class="w-3 h-3"></i> Sync to Booking</button>
            <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="text-center py-12 text-white/30 text-sm">No custom builds yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>
</main>

<div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

<!-- ══════════ DISCOUNT MODAL ══════════ -->
<div id="discount-modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-center justify-center p-4" style="display:none">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-sm">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg">Apply Discount</h2>
      <button onclick="closeDiscountModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <div class="p-6 space-y-4">
      <div class="flex gap-2">
        <button type="button" onclick="setDiscountType('percentage')" id="discount-type-percentage" class="flex-1 py-2.5 rounded-xl text-sm font-semibold border">Percentage</button>
        <button type="button" onclick="setDiscountType('amount')" id="discount-type-amount" class="flex-1 py-2.5 rounded-xl text-sm font-semibold border">Amount</button>
      </div>
      <div id="discount-currency-wrap">
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Currency</label>
        <select id="discount-currency" onchange="updateDiscountValueLabel()" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
          <option value="sar">SAR</option>
          <option value="bdt" selected>BDT</option>
          <option value="usd">USD</option>
        </select>
      </div>
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5" id="discount-value-label">Percentage (%)</label>
        <input type="number" id="discount-value" step="0.01" min="0" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none" placeholder="0">
      </div>
      <p class="text-[11px] text-white/30" id="discount-hint">An amount discount is converted proportionally to the other two currencies using this booking's saved exchange rate.</p>
      <button onclick="saveDiscount()" id="discount-save-btn" class="w-full bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm">Apply</button>
    </div>
  </div>
</div>
<script>
lucide.createIcons();
const dashCsrf = <?= json_encode(csrfToken()) ?>;
// ── Discount modal ──
let discountBookingSysId = '', discountType = 'percentage';
function openDiscountModal(b) {
  discountBookingSysId = b.sys_id;
  const existing = b.discount;
  discountType = (existing && existing.type) || 'percentage';
  document.getElementById('discount-value').value = existing ? existing.value : '';
  document.getElementById('discount-currency').value = (existing && existing.currency) || 'bdt';
  setDiscountType(discountType);
  document.getElementById('discount-modal-overlay').style.display = 'flex';
}
function closeDiscountModal() { document.getElementById('discount-modal-overlay').style.display = 'none'; }
function setDiscountType(type) {
  discountType = type;
  document.getElementById('discount-hint').classList.toggle('hidden', type !== 'amount');
  updateDiscountValueLabel();
  ['percentage','amount'].forEach(t => {
    const btn = document.getElementById(`discount-type-${t}`);
    btn.className = `flex-1 py-2.5 rounded-xl text-sm font-semibold border ${t===type ? 'border-secondary bg-secondary/10 text-secondary' : 'border-white/10 bg-white/5 text-white/60'}`;
  });
}
function updateDiscountValueLabel() {
  if (discountType === 'percentage') {
    document.getElementById('discount-value-label').textContent = 'Percentage (%)';
  } else {
    const symbols = {sar:'SR', bdt:'৳', usd:'$'};
    const cur = document.getElementById('discount-currency').value;
    document.getElementById('discount-value-label').textContent = `Amount (${symbols[cur]||cur.toUpperCase()})`;
  }
}
async function saveDiscount() {
  const value = parseFloat(document.getElementById('discount-value').value);
  if (isNaN(value) || value < 0) { showDashToast('Enter a valid discount value.', false); return; }
  const btn = document.getElementById('discount-save-btn');
  btn.disabled = true; btn.textContent = 'Applying...';
  const fd = new FormData();
  fd.append('csrf_token', dashCsrf);
  fd.append('action', 'apply_discount');
  fd.append('sys_id', discountBookingSysId);
  fd.append('discount_type', discountType);
  fd.append('discount_currency', document.getElementById('discount-currency').value);
  fd.append('discount_value', value);
  const res = await fetch(`${window.BASE_URL}/api/bookings.php`, {method:'POST', body:fd});
  const data = await res.json();
  showDashToast(data.message, data.success);
  btn.disabled = false; btn.textContent = 'Apply';
  if (data.success) { closeDiscountModal(); setTimeout(()=>location.reload(), 700); }
}
function showDashToast(msg, ok) {
  const toast = document.getElementById('toast');
  toast.textContent = msg;
  toast.className = `fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm ${ok?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  toast.classList.remove('hidden'); setTimeout(()=>toast.classList.add('hidden'),3000);
}

async function updateBookingStatus(sysId, newStatus, selectEl) {
  selectEl.disabled = true;
  const fd = new FormData();
  fd.append('csrf_token', dashCsrf);
  fd.append('action', 'update_status');
  fd.append('sys_id', sysId);
  fd.append('status', newStatus);
  const res = await fetch(`${window.BASE_URL}/api/bookings.php`, {method:'POST', body:fd});
  const data = await res.json();
  const toast = document.getElementById('toast');
  toast.textContent = data.message;
  toast.className = `fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm ${data.success?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  toast.classList.remove('hidden'); setTimeout(()=>toast.classList.add('hidden'),3000);
  selectEl.disabled = false;
  if (data.success) setTimeout(()=>location.reload(), 600);
}
async function syncCustomBuild(sysId, btn) {
  btn.disabled = true;
  btn.innerHTML = '<i data-lucide="loader" class="w-3 h-3 animate-spin"></i> Syncing...';
  lucide.createIcons();
  const fd = new FormData();
  fd.append('csrf_token', dashCsrf);
  fd.append('action', 'sync');
  fd.append('custom_build_sys_id', sysId);
  const res = await fetch(`${window.BASE_URL}/api/bookings.php`, {method:'POST', body:fd});
  const data = await res.json();
  const toast = document.getElementById('toast');
  toast.textContent = data.message;
  toast.className = `fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm ${data.success?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  toast.classList.remove('hidden'); setTimeout(()=>toast.classList.add('hidden'),3000);
  if (data.success) setTimeout(()=>location.reload(), 700);
  else { btn.disabled = false; btn.innerHTML = '<i data-lucide="arrow-right-circle" class="w-3 h-3"></i> Sync to Booking'; lucide.createIcons(); }
}
</script>
</div>

<script>
lucide.createIcons();
document.getElementById('mob-menu-btn')?.addEventListener('click', () => {
  document.getElementById('mob-menu').classList.toggle('hidden');
});
</script>
</body></html>