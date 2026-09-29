<?php
// FILE PATH: /pages/booking-confirmation.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$ref = sanitize($_GET['ref'] ?? '');
$booking = null;

if ($ref) {
    try {
        require_once dirname(__DIR__) . '/data/server/db_connection.php';
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM bookings WHERE booking_ref = :ref LIMIT 1");
        $stmt->execute([':ref' => $ref]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) { }
}

// Dynamic settings
$bkash     = getSetting('bkash_number',  '01XXXXXXXXX');
$bkashType = getSetting('bkash_type',    'Personal');
$bankName  = getSetting('bank_name',     'Dutch-Bangla Bank');
$bankAcc   = getSetting('bank_account',  'XXXXXXXXXXXX');
$bankHolder= getSetting('bank_holder',   'TravHub Ltd');
$waNumber  = getSetting('whatsapp_number', '+8801000000000');
$adminEmail= getSetting('admin_email',   'info@travhub.com.bd');

$pageTitle = 'Booking Confirmed | TravHub';
include dirname(__DIR__) . '/includes/header.php';
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>
<style type="text/tailwindcss">
@layer components {
  .glass-card  { @apply bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl; }
  .btn-primary { @apply bg-secondary hover:bg-emerald text-primary font-semibold rounded-xl transition-all duration-300; }
}
</style>

<main class="pt-28 pb-24 min-h-screen">
  <div class="max-w-2xl mx-auto px-4">

    <!-- Success Header -->
    <div class="text-center mb-10">
      <div class="w-20 h-20 bg-secondary/10 border-2 border-secondary/30 rounded-full flex items-center justify-center mx-auto mb-5 animate-pulse">
        <i data-lucide="check-circle" class="w-10 h-10 text-secondary"></i>
      </div>
      <h1 class="text-3xl font-bold mb-2">Booking Received!</h1>
      <p class="text-white/40">Our consultants will contact you within 2 hours to confirm.</p>
      <?php if ($ref): ?>
      <div class="inline-block mt-4 bg-white/5 border border-white/10 rounded-xl px-6 py-3">
        <p class="text-xs text-white/40 uppercase tracking-wider">Booking Reference</p>
        <p class="text-xl font-bold text-secondary font-mono"><?= htmlspecialchars($ref) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($booking): ?>
    <!-- Booking Summary -->
    <div class="glass-card p-6 mb-6">
      <h2 class="font-bold mb-4 flex items-center gap-2"><i data-lucide="clipboard-list" class="w-4 h-4 text-secondary"></i> Your Booking Summary</h2>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><p class="text-[10px] text-white/30 uppercase tracking-wider mb-1">Name</p><p class="font-medium"><?= htmlspecialchars($booking['customer_name']) ?></p></div>
        <div><p class="text-[10px] text-white/30 uppercase tracking-wider mb-1">Phone</p><p class="font-medium"><?= htmlspecialchars($booking['customer_phone']) ?></p></div>
        <div><p class="text-[10px] text-white/30 uppercase tracking-wider mb-1">Package</p><p class="font-medium"><?= htmlspecialchars($booking['package_name'] ?? '—') ?></p></div>
        <div><p class="text-[10px] text-white/30 uppercase tracking-wider mb-1">Travelers</p><p class="font-medium"><?= $booking['adults'] ?> Adults<?= $booking['children']?' + '.$booking['children'].' Children':'' ?></p></div>
        <?php if($booking['total_price_bdt']): ?>
        <div><p class="text-[10px] text-white/30 uppercase tracking-wider mb-1">Est. Total (BDT)</p><p class="font-bold text-secondary">৳<?= number_format($booking['total_price_bdt'],0) ?></p></div>
        <?php endif; ?>
        <?php if($booking['total_price_sar']): ?>
        <div><p class="text-[10px] text-white/30 uppercase tracking-wider mb-1">Est. Total (SAR)</p><p class="font-bold text-secondary">SR <?= number_format($booking['total_price_sar'],0) ?></p></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Payment Instructions -->
    <div class="glass-card p-6 mb-6">
      <h2 class="font-bold mb-5 flex items-center gap-2"><i data-lucide="credit-card" class="w-4 h-4 text-secondary"></i> Payment Instructions</h2>
      <p class="text-white/40 text-sm mb-5">Please make an advance payment to confirm your booking. Send payment screenshot via WhatsApp.</p>

      <div class="space-y-4">
        <!-- bKash -->
        <div class="bg-pink-500/5 border border-pink-500/20 rounded-xl p-4">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 bg-pink-500/10 rounded-lg flex items-center justify-center"><i data-lucide="smartphone" class="w-4 h-4 text-pink-400"></i></div>
            <span class="font-bold text-sm text-pink-300">bKash</span>
          </div>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div><p class="text-[10px] text-white/30 uppercase">Number</p><p class="font-bold font-mono"><?= htmlspecialchars($bkash) ?></p></div>
            <div><p class="text-[10px] text-white/30 uppercase">Type</p><p class="font-medium"><?= htmlspecialchars($bkashType) ?></p></div>
          </div>
          <p class="text-[10px] text-white/30 mt-2">Send Money → Use your booking reference as reference</p>
        </div>

        <!-- Bank Transfer -->
        <div class="bg-blue-500/5 border border-blue-500/20 rounded-xl p-4">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center"><i data-lucide="building-2" class="w-4 h-4 text-blue-400"></i></div>
            <span class="font-bold text-sm text-blue-300">Bank Transfer</span>
          </div>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div><p class="text-[10px] text-white/30 uppercase">Bank</p><p class="font-medium"><?= htmlspecialchars($bankName) ?></p></div>
            <div><p class="text-[10px] text-white/30 uppercase">Account No.</p><p class="font-bold font-mono"><?= htmlspecialchars($bankAcc) ?></p></div>
            <div class="col-span-2"><p class="text-[10px] text-white/30 uppercase">Account Holder</p><p class="font-medium"><?= htmlspecialchars($bankHolder) ?></p></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact -->
    <div class="glass-card p-6 mb-6">
      <h2 class="font-bold mb-4 flex items-center gap-2"><i data-lucide="message-circle" class="w-4 h-4 text-secondary"></i> Send Payment Proof</h2>
      <p class="text-white/40 text-sm mb-4">After payment, send a screenshot with your booking reference to:</p>
      <a href="https://wa.me/<?= ltrim(preg_replace('/[^0-9+]/','',$waNumber),'+') ?>?text=Booking+Ref:+<?= urlencode($ref) ?>+Payment+Proof"
         target="_blank"
         class="btn-primary w-full py-3.5 font-bold flex items-center justify-center gap-2">
        <i data-lucide="message-circle" class="w-5 h-5"></i>
        Send on WhatsApp
      </a>
      <?php if ($adminEmail): ?>
      <p class="text-center text-xs text-white/30 mt-3">Or email: <a href="mailto:<?= htmlspecialchars($adminEmail) ?>" class="text-secondary hover:underline"><?= htmlspecialchars($adminEmail) ?></a></p>
      <?php endif; ?>
    </div>

    <!-- Track Booking -->
    <div class="text-center space-y-3">
      <a href="./user-dashboard.php" class="inline-flex items-center gap-2 text-sm text-secondary hover:text-emerald transition-colors font-semibold">
        <i data-lucide="search" class="w-4 h-4"></i> Track your booking anytime
      </a>
      <p class="text-white/30 text-xs">Use reference <strong class="text-white/60"><?= htmlspecialchars($ref) ?></strong> on the user dashboard</p>
    </div>

  </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/currency-handler.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body></html>
