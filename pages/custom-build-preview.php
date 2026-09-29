<?php
// FILE PATH: /pages/custom-build-preview.php
// Landing page after a mini-builder (or package-builder) submission —
// shows the saved custom_builds row back to the customer, with a PDF
// download and a WhatsApp/Email follow-up option.
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

$ref = trim($_GET['ref'] ?? '');
if ($ref === '') { header('Location: ' . BASE_URL . '/pages/index.php'); exit; }

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM custom_builds WHERE sys_id = ?");
    $stmt->execute([$ref]);
    $build = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$build) { header('Location: ' . BASE_URL . '/pages/index.php'); exit; }

    $customer   = json_decode($build['customer_infos']  ?? '{}', true) ?: [];
    $persons    = json_decode($build['persons']         ?? '{}', true) ?: [];
    $visaInfos    = json_decode($build['visa_infos']       ?? '{}', true) ?: [];
    $serviceInfos = json_decode($build['service_infos']    ?? '{}', true) ?: [];
    $flightInfo = json_decode($build['flight_infos']     ?? '{}', true) ?: [];
    $days       = json_decode($build['days']             ?? '{}', true) ?: [];
    $hotelInfos = json_decode($build['hotel_infos']      ?? '{}', true) ?: [];
    $mealInfos  = json_decode($build['meal_infos']       ?? '{}', true) ?: [];
    $transport  = json_decode($build['transport_infos']  ?? '{}', true) ?: [];
    $moyallem   = json_decode($build['moyallem_infos']   ?? '{}', true) ?: [];
    $ziarah     = json_decode($build['ziarah_infos']     ?? '{}', true) ?: [];
    $totals     = json_decode($build['total_prices']     ?? '{}', true) ?: [];
    $snap       = json_decode($build['data_json']        ?? '{}', true) ?: [];
    $choices    = array_filter(explode(',', $build['choices'] ?? ''));

    $visaName = $snap['visa']['type'] ?? '—';
} catch (Exception $e) {
    header('Location: ' . BASE_URL . '/pages/index.php'); exit;
}

$pageTitle       = 'Your Custom Umrah Package | TravHub';
$pageDescription = 'Preview of your custom Umrah package submission.';
$whatsappNumber  = getSetting('whatsapp_number', '');
$whatsappNumber  = $whatsappNumber !== '' ? ltrim($whatsappNumber, '+') : '8801000000000';
$waText = rawurlencode("Assalamu Alaikum! I just submitted a custom package (Ref: {$build['sys_id']}). I'd like to talk it over.");
include dirname(__DIR__) . '/includes/header.php';
?>

<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="pt-28 pb-20 min-h-screen">
  <div class="max-w-3xl mx-auto px-6">

    <div class="text-center mb-10">
      <div class="w-16 h-16 rounded-full bg-secondary/10 flex items-center justify-center mx-auto mb-4">
        <i data-lucide="check-circle-2" class="w-8 h-8 text-secondary"></i>
      </div>
      <h1 class="text-3xl font-bold mb-2">Your Package Has Been Submitted!</h1>
      <p class="text-white/40">Reference: <span class="text-secondary font-mono"><?= htmlspecialchars($build['sys_id']) ?></span></p>
    </div>

    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 md:p-8 space-y-6">

      <!-- Traveler -->
      <div>
        <h2 class="text-sm font-bold text-white/50 uppercase tracking-wider mb-3">Traveler</h2>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div><p class="text-white/30">Name</p><p class="font-medium"><?= htmlspecialchars($customer['name'] ?? '—') ?></p></div>
          <div><p class="text-white/30">Travelers</p><p class="font-medium"><?= (int)($persons['adults']??0) ?> Adult(s)<?= !empty($persons['children']) ? ', '.(int)$persons['children'].' Child(ren)' : '' ?></p></div>
          <?php if (!empty($customer['phone'])): ?><div><p class="text-white/30">Phone</p><p class="font-medium"><?= htmlspecialchars($customer['phone']) ?></p></div><?php endif; ?>
          <?php if (!empty($customer['email'])): ?><div><p class="text-white/30">Email</p><p class="font-medium"><?= htmlspecialchars($customer['email']) ?></p></div><?php endif; ?>
        </div>
      </div>

      <!-- Journey -->
      <div class="border-t border-white/10 pt-6">
        <h2 class="text-sm font-bold text-white/50 uppercase tracking-wider mb-3">Journey</h2>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div><p class="text-white/30">Duration</p><p class="font-medium"><?= (int)($days['total']??0) ?> Days (Makkah <?= (int)($days['makkah']??0) ?>n + Madinah <?= (int)($days['madinah']??0) ?>n)</p></div>
          <?php if (in_array('hotel', $choices)): ?><div><p class="text-white/30">Hotel Category</p><p class="font-medium"><?= htmlspecialchars($hotelInfos['category'] ?? '—') ?></p></div><?php endif; ?>
        </div>
      </div>

      <!-- Line items -->
      <div class="border-t border-white/10 pt-6 space-y-3">
        <h2 class="text-sm font-bold text-white/50 uppercase tracking-wider mb-1">Selections</h2>

        <?php if (in_array('visa', $choices) && $visaInfos): ?>
        <div class="flex items-center justify-between text-sm bg-dark rounded-xl px-4 py-3">
          <span class="flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4 text-secondary"></i> Visa — <?= htmlspecialchars($visaName) ?></span>
          <span class="text-secondary font-bold">SR <?= number_format((float)($visaInfos['total_prices']['sar'][0]??0),0) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($serviceInfos): ?>
        <div class="flex items-center justify-between text-sm bg-dark rounded-xl px-4 py-3">
          <span class="flex items-center gap-2"><i data-lucide="star" class="w-4 h-4 text-secondary"></i> Service Level — <?= htmlspecialchars($serviceInfos['name']??'') ?></span>
          <span class="text-secondary font-bold">SR <?= number_format((float)($serviceInfos['total_prices']['sar'][0]??0),0) ?></span>
        </div>
        <?php endif; ?>

        <?php if (in_array('flight', $choices) && $flightInfo): ?>
        <div class="flex items-center justify-between text-sm bg-dark rounded-xl px-4 py-3">
          <span class="flex items-center gap-2"><i data-lucide="plane" class="w-4 h-4 text-secondary"></i> Flight — <?= htmlspecialchars($flightInfo['connection_type'] ?? '—') ?></span>
          <span class="text-white/30 text-xs">To be confirmed</span>
        </div>
        <?php endif; ?>

        <?php if (in_array('hotel', $choices) && !empty($hotelInfos['selections'])): foreach ($hotelInfos['selections'] as $sel): ?>
        <div class="flex items-center justify-between text-sm bg-dark rounded-xl px-4 py-3">
          <span class="flex items-center gap-2"><i data-lucide="hotel" class="w-4 h-4 text-secondary"></i> <?= htmlspecialchars($sel['hotel_name']??'') ?> — <?= htmlspecialchars($sel['room_name']??'') ?> (<?= htmlspecialchars($sel['board_name']??'') ?>)</span>
          <span class="text-secondary font-bold">SR <?= number_format((float)($sel['price']??0),0) ?></span>
        </div>
        <?php endforeach; endif; ?>

        <?php if (in_array('meal', $choices) && $mealInfos): ?>
        <div class="flex items-center justify-between text-sm bg-dark rounded-xl px-4 py-3">
          <span class="flex items-center gap-2"><i data-lucide="utensils" class="w-4 h-4 text-secondary"></i> Meal — <?= htmlspecialchars($mealInfos['tier']??'') ?></span>
          <span class="text-secondary font-bold">SR <?= number_format((float)($mealInfos['total_prices']['sar'][0]??0),0) ?></span>
        </div>
        <?php endif; ?>

        <?php if (in_array('transport', $choices) && !empty($transport['legs'])): foreach ($transport['legs'] as $leg): ?>
        <div class="flex items-center justify-between text-sm bg-dark rounded-xl px-4 py-3">
          <span class="flex items-center gap-2"><i data-lucide="car" class="w-4 h-4 text-secondary"></i> <?= htmlspecialchars($leg['route_name']??'') ?> — <?= htmlspecialchars($leg['vehicle_name']??'') ?></span>
          <span class="text-secondary font-bold">SR <?= number_format((float)($leg['price']??0),0) ?></span>
        </div>
        <?php endforeach; endif; ?>

        <?php if (in_array('moyallem', $choices) && !empty($moyallem['services'])): foreach ($moyallem['services'] as $svc): ?>
        <div class="flex items-center justify-between text-sm bg-dark rounded-xl px-4 py-3">
          <span class="flex items-center gap-2"><i data-lucide="users" class="w-4 h-4 text-secondary"></i> <?= htmlspecialchars($svc['service_name']??'') ?> (<?= htmlspecialchars($svc['category']??'') ?>)</span>
          <span class="text-secondary font-bold">SR <?= number_format((float)($svc['price']??0),0) ?></span>
        </div>
        <?php endforeach; endif; ?>

        <?php if (in_array('ziarah', $choices) && !empty($ziarah['selections'])): foreach ($ziarah['selections'] as $z): ?>
        <div class="flex items-center justify-between text-sm bg-dark rounded-xl px-4 py-3">
          <span class="flex items-center gap-2"><i data-lucide="landmark" class="w-4 h-4 text-secondary"></i> <?= htmlspecialchars($z['name']??'') ?></span>
          <span class="text-secondary font-bold">SR <?= number_format((float)($z['price']??0) + (float)($z['moyallem_price']??0),0) ?></span>
        </div>
        <?php endforeach; endif; ?>
      </div>

      <!-- Total -->
      <div class="border-t border-white/10 pt-6">
        <div class="bg-secondary/5 border border-secondary/20 rounded-xl p-5">
          <p class="text-xs text-white/40 uppercase tracking-wider mb-2">Estimated Total</p>
          <div class="flex flex-wrap items-baseline gap-x-6 gap-y-1">
            <span class="text-2xl font-bold text-secondary">SR <?= number_format((float)($totals['sar'][0]??0),0) ?></span>
            <span class="text-white/40 text-sm">BDT <?= number_format((float)($totals['bdt'][0]??0),0) ?></span>
            <span class="text-white/40 text-sm">USD <?= number_format((float)($totals['usd'][0]??0),2) ?></span>
          </div>
          <p class="text-[11px] text-white/25 mt-2">Subject to final confirmation by our consultants.</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="border-t border-white/10 pt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <?php if (!empty($build['pdf_path'])): ?>
        <a href="<?= BASE_URL . '/' . htmlspecialchars($build['pdf_path']) ?>" target="_blank"
           class="flex items-center justify-center gap-2 bg-secondary hover:bg-emerald text-primary font-semibold py-3.5 rounded-xl transition-all duration-300">
          <i data-lucide="download" class="w-5 h-5"></i> Download PDF
        </a>
        <?php else: ?>
        <span class="flex items-center justify-center gap-2 bg-white/5 text-white/30 font-medium py-3.5 rounded-xl text-sm">PDF unavailable</span>
        <?php endif; ?>
        <a href="https://wa.me/<?= $whatsappNumber ?>?text=<?= $waText ?>" target="_blank"
           class="flex items-center justify-center gap-2 border border-[#25D366] text-[#25D366] hover:bg-[#25D366] hover:text-white font-semibold py-3.5 rounded-xl transition-all duration-300">
          <i data-lucide="message-circle" class="w-5 h-5"></i> WhatsApp
        </a>
        <a href="<?= BASE_URL ?>/pages/contact.php" class="flex items-center justify-center gap-2 border border-white/10 hover:border-secondary text-white/70 hover:text-secondary font-medium py-3.5 rounded-xl transition-all duration-300">
          <i data-lucide="mail" class="w-5 h-5"></i> Email
        </a>
      </div>
    </div>

    <p class="text-center text-white/25 text-xs mt-8">Our team will reach out shortly to confirm final details and payment.</p>
  </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script>lucide.createIcons();</script>
</body></html>