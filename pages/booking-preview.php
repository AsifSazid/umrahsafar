<?php
// FILE PATH: /pages/booking-preview.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

$ref = sanitize($_GET['ref'] ?? '');
$build = null;
if ($ref) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM custom_builds WHERE build_ref = ?");
        $stmt->execute([$ref]);
        $build = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) { $build = null; }
}

if (!$build) {
    http_response_code(404);
}

$pageTitle       = 'Booking Preview | TravHub';
$pageDescription = 'Preview and download your custom Umrah journey.';
$csrf = csrfToken();
include dirname(__DIR__) . '/includes/header.php';
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<section class="pt-24 pb-16 bg-dark min-h-screen">
  <div class="max-w-6xl mx-auto px-6">
    <?php if (!$build): ?>
      <div class="glass-card p-10 text-center">
        <i data-lucide="file-x" class="w-12 h-12 mx-auto mb-4 text-white/20"></i>
        <h1 class="text-xl font-bold mb-2">Booking not found</h1>
        <p class="text-white/40 text-sm mb-6">We couldn't find a booking with that reference.</p>
        <a href="<?= BASE_URL ?>/pages/index.php" class="inline-block bg-secondary hover:bg-emerald text-primary font-bold px-6 py-3 rounded-xl">Back to Home</a>
      </div>
    <?php else: ?>

    <?php
      $statusBadgeClass = bookingStatusBadgeClass($build['status'] ?? 'Submitted');
    ?>
    <div class="mb-6 no-print">
      <h1 class="text-2xl font-bold">Your Booking Preview</h1>
      <p class="text-white/40 text-sm flex items-center flex-wrap gap-3">
        <span>Reference: <span class="font-mono text-secondary"><?= htmlspecialchars($build['build_ref']) ?></span></span>
        <span class="flex items-center gap-2">Current Status:
          <span class="text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider <?= $statusBadgeClass ?>"><?= htmlspecialchars($build['status'] ?? 'Submitted') ?></span>
        </span>
      </p>
    </div>

    <div class="grid lg:grid-cols-[1fr_220px] gap-6 items-start">
      <!-- PDF Preview — height pinned to the viewport so only the PDF itself
           scrolls (via the iframe), not the whole page. -->
      <div class="bg-white text-[#1A2039] rounded-2xl overflow-hidden shadow-2xl print-a4" style="height:calc(100vh - 180px); max-height:900px;">
        <?php if ($build['pdf_path']): ?>
          <iframe src="<?= BASE_URL . '/' . htmlspecialchars($build['pdf_path']) ?>" class="w-full h-full border-0"></iframe>
        <?php else: ?>
          <div class="w-full h-full flex items-center justify-center text-center p-10">
            <div>
              <i data-lucide="file-clock" class="w-10 h-10 mx-auto mb-3 text-black/20"></i>
              <p class="text-black/40 text-sm">PDF is still being generated. Please refresh in a moment.</p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Action buttons — stacked on the right -->
      <div class="flex flex-col gap-2 no-print lg:sticky lg:top-28">
        <button onclick="window.print()" class="w-full bg-white/5 hover:bg-white/10 border border-white/10 px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2">
          <i data-lucide="printer" class="w-4 h-4"></i> Print
        </button>
        <?php if ($build['pdf_path']): ?>
        <a href="<?= BASE_URL . '/' . htmlspecialchars($build['pdf_path']) ?>" download
           class="w-full bg-white/5 hover:bg-white/10 border border-white/10 px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2">
          <i data-lucide="download" class="w-4 h-4"></i> Download PDF
        </a>
        <?php
          $pdfAbsUrl = rtrim(getSetting('site_url', ''), '/') . BASE_URL . '/' . $build['pdf_path'];
          $waMsg = urlencode("Assalamu Alaikum! Here is my Umrah booking preview (Ref: {$build['build_ref']}):\n{$pdfAbsUrl}");
          $waNum = ltrim(getSetting('whatsapp_number', ''), '+');
        ?>
        <a href="https://wa.me/<?= $waNum ?>?text=<?= $waMsg ?>" target="_blank"
           class="w-full bg-[#25D366]/10 hover:bg-[#25D366]/20 border border-[#25D366]/30 text-[#25D366] px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2">
          <i data-lucide="message-circle" class="w-4 h-4"></i> Share on WhatsApp
        </a>
        <button onclick="openEmailModal()" class="w-full bg-white/5 hover:bg-white/10 border border-white/10 px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2">
          <i data-lucide="mail" class="w-4 h-4"></i> Email PDF
        </button>
        <?php endif; ?>
      </div>
    </div>

    <div class="mt-6 no-print">
      <a href="<?= BASE_URL ?>/pages/index.php" class="text-white/40 hover:text-white text-sm flex items-center gap-2"><i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Home</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Email modal -->
<div id="email-modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4 no-print">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-md">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg">Email your booking PDF</h2>
      <button onclick="closeEmailModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="email-form" class="p-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="build_ref" value="<?= htmlspecialchars($build['build_ref'] ?? '') ?>">
      <div>
        <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Send to email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($build['customer_email'] ?? '') ?>" placeholder="your@email.com" class="w-full bg-dark border border-white/10 rounded-xl px-4 py-2.5 text-sm focus:border-secondary focus:outline-none">
      </div>
      <div id="email-msg" class="hidden text-xs px-3 py-2 rounded-lg"></div>
      <button type="submit" id="email-submit-btn" class="w-full bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm">Send PDF</button>
    </form>
  </div>
</div>

<style>
@media print {
  .no-print { display: none !important; }
  nav, footer { display: none !important; }
  body { background: white !important; }
  .print-a4 { box-shadow: none !important; height: auto !important; max-height: none !important; }
}
</style>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script>
lucide.createIcons();
function openEmailModal(){ document.getElementById('email-modal-overlay').classList.remove('hidden'); document.getElementById('email-modal-overlay').classList.add('flex'); }
function closeEmailModal(){ document.getElementById('email-modal-overlay').classList.add('hidden'); document.getElementById('email-modal-overlay').classList.remove('flex'); }

document.getElementById('email-form')?.addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('email-submit-btn');
  const msg = document.getElementById('email-msg');
  btn.disabled = true; btn.textContent = 'Sending...';
  try {
    const res = await fetch(`<?= BASE_URL ?>/api/email-booking-pdf.php`, { method: 'POST', body: new FormData(e.target) });
    const data = await res.json();
    msg.textContent = data.message;
    msg.className = `text-xs px-3 py-2 rounded-lg ${data.success ? 'bg-secondary/10 text-secondary' : 'bg-red-500/10 text-red-400'}`;
    msg.classList.remove('hidden');
    if (data.success) setTimeout(closeEmailModal, 1800);
  } catch(e) {
    msg.textContent = 'Failed to send. Please try again.';
    msg.className = 'text-xs px-3 py-2 rounded-lg bg-red-500/10 text-red-400';
    msg.classList.remove('hidden');
  }
  btn.disabled = false; btn.textContent = 'Send PDF';
});
</script>
</body></html>