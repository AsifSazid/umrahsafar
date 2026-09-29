<?php
// FILE PATH: /admin/settings.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
requireAdmin();
$csrf = csrfToken();
$settings = getAllSettings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Site Settings | TravHub Admin</title>
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
<!-- Main -->
<main class="flex-1 p-6 lg:p-10 overflow-auto">
  <div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold mb-2">Site Settings</h1>
    <p class="text-white/40 mb-8">Manage contact info, payment details, and API keys dynamically.</p>
    <div id="toast" class="hidden fixed top-6 right-6 z-50 bg-secondary text-primary font-bold px-6 py-3 rounded-xl shadow-2xl transition-all"></div>
    <form id="settings-form" class="space-y-8">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="update_settings">

      <?php
      $groups = [
        'contact' => ['icon'=>'phone','title'=>'Contact Information','color'=>'teal'],
        'payment' => ['icon'=>'credit-card','title'=>'Payment Details','color'=>'secondary'],
        'api'     => ['icon'=>'key','title'=>'API Keys','color'=>'yellow-400'],
        'general' => ['icon'=>'globe','title'=>'General','color'=>'white/40'],
      ];
      foreach ($groups as $gk => $g):
        $items = $settings[$gk] ?? [];
        if (!$items) continue;
      ?>
      <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
        <h2 class="text-base font-bold mb-5 flex items-center gap-2 text-<?= $g['color'] ?>">
          <i data-lucide="<?= $g['icon'] ?>" class="w-4 h-4"></i> <?= $g['title'] ?>
        </h2>
        <div class="grid md:grid-cols-2 gap-4">
          <?php foreach ($items as $s): ?>
          <div>
            <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5"><?= htmlspecialchars($s['label']) ?></label>
            <input type="<?= $s['setting_key']==='exchange_api_key'?'password':'text' ?>"
                   name="<?= $s['setting_key'] ?>"
                   value="<?= htmlspecialchars($s['setting_val'] ?? '') ?>"
                   class="w-full bg-dark border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20 font-mono">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <button type="submit" class="w-full bg-secondary hover:bg-emerald text-primary font-bold py-4 rounded-xl transition-all flex items-center justify-center gap-2">
        <i data-lucide="save" class="w-5 h-5"></i> Save All Settings
      </button>
    </form>
  </div>
</main>
</div>
<script>
lucide.createIcons();
document.getElementById('settings-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  btn.disabled = true; btn.innerHTML = '<i data-lucide="loader" class="w-5 h-5 animate-spin inline mr-2"></i>Saving...';
  lucide.createIcons();
  const fd = new FormData(e.target);
  const res = await fetch(`<?= BASE_URL ?>/api/settings-handler.php`, {method:'POST', body:fd});
  const data = await res.json();
  const toast = document.getElementById('toast');
  toast.textContent = data.message;
  toast.className = `fixed top-6 right-6 z-50 font-bold px-6 py-3 rounded-xl shadow-2xl ${data.success?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  toast.classList.remove('hidden');
  setTimeout(()=>toast.classList.add('hidden'), 3000);
  btn.disabled = false; btn.innerHTML = '<i data-lucide="save" class="w-5 h-5 inline mr-2"></i>Save All Settings';
  lucide.createIcons();
});
</script>
</body></html>