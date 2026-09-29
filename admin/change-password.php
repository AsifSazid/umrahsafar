<?php
// FILE PATH: /admin/change-password.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
requireAdmin();
$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Change Password | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Poppins','sans-serif']},colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625',emerald:'#3AAB71',teal:'#02CCFE'}}}}</script>
<style>body{background:#111625}</style>
</head>
<body class="text-white font-sans min-h-screen">
<div class="flex min-h-screen">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="flex-1 p-6 lg:p-10 flex items-start justify-center">
  <div class="w-full max-w-md mt-10">
    <div class="bg-navy border border-white/10 rounded-2xl p-8">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-secondary/10 rounded-xl flex items-center justify-center"><i data-lucide="lock" class="w-5 h-5 text-secondary"></i></div>
        <div><h1 class="text-xl font-bold">Change Password</h1><p class="text-white/40 text-xs">Admin account security</p></div>
      </div>
      <div id="msg" class="hidden mb-4 px-4 py-3 rounded-xl text-sm font-medium"></div>
      <form id="pw-form" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="action" value="change_admin_password">
        <div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Current Password</label>
          <div class="relative">
            <input type="password" name="current_password" id="cur-pw" placeholder="••••••••"
              class="w-full bg-dark border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none pr-12 placeholder-white/20">
            <button type="button" onclick="togglePw('cur-pw',this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white"><i data-lucide="eye" class="w-4 h-4"></i></button>
          </div>
        </div>
        <div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">New Password</label>
          <div class="relative">
            <input type="password" name="new_password" id="new-pw" placeholder="Min. 8 characters"
              class="w-full bg-dark border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none pr-12 placeholder-white/20">
            <button type="button" onclick="togglePw('new-pw',this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white"><i data-lucide="eye" class="w-4 h-4"></i></button>
          </div>
          <!-- Strength bar -->
          <div class="mt-2 h-1 bg-white/10 rounded-full overflow-hidden">
            <div id="strength-bar" class="h-full w-0 rounded-full transition-all duration-300 bg-red-500"></div>
          </div>
          <p id="strength-label" class="text-[10px] text-white/30 mt-1"></p>
        </div>
        <div>
          <label class="text-xs text-white/40 uppercase tracking-wider block mb-1.5">Confirm New Password</label>
          <input type="password" name="confirm_password" id="conf-pw" placeholder="Re-enter new password"
            class="w-full bg-dark border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20">
        </div>
        <button type="submit" class="w-full bg-secondary hover:bg-emerald text-primary font-bold py-3.5 rounded-xl transition-all flex items-center justify-center gap-2 mt-2">
          <i data-lucide="shield-check" class="w-4 h-4"></i> Update Password
        </button>
      </form>
    </div>
  </div>
</main>
</div>
<script>
lucide.createIcons();
function togglePw(id, btn) {
  const inp = document.getElementById(id);
  const show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  btn.innerHTML = `<i data-lucide="${show?'eye-off':'eye'}" class="w-4 h-4"></i>`;
  lucide.createIcons();
}
document.getElementById('new-pw').addEventListener('input', function() {
  const v = this.value, bar = document.getElementById('strength-bar'), lbl = document.getElementById('strength-label');
  let score = 0;
  if (v.length >= 8) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  const levels = [['0%','bg-red-500',''],['25%','bg-red-500','Weak'],['50%','bg-yellow-400','Fair'],['75%','bg-teal','Good'],['100%','bg-secondary','Strong']];
  const [w,c,t] = levels[score];
  bar.style.width = w; bar.className = `h-full rounded-full transition-all duration-300 ${c}`;
  lbl.textContent = t;
});
document.getElementById('pw-form').addEventListener('submit', async e => {
  e.preventDefault();
  const np = document.getElementById('new-pw').value;
  const cp = document.getElementById('conf-pw').value;
  const msg = document.getElementById('msg');
  if (np !== cp) { showMsg('Passwords do not match.', false); return; }
  if (np.length < 6) { showMsg('Password must be at least 6 characters.', false); return; }
  const btn = e.target.querySelector('button[type=submit]');
  btn.disabled = true; btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin inline mr-2"></i>Updating...'; lucide.createIcons();
  const fd = new FormData(e.target);
  // Admin password change goes to a dedicated endpoint
  const res = await fetch(`<?= BASE_URL ?>/api/admin-change-password.php`, {method:'POST', body:fd});
  const data = await res.json();
  showMsg(data.message, data.success);
  if (data.success) e.target.reset();
  btn.disabled = false; btn.innerHTML = '<i data-lucide="shield-check" class="w-4 h-4 inline mr-2"></i>Update Password'; lucide.createIcons();
});
function showMsg(text, ok) {
  const msg = document.getElementById('msg');
  msg.textContent = text;
  msg.className = `mb-4 px-4 py-3 rounded-xl text-sm font-medium ${ok?'bg-secondary/10 text-secondary border border-secondary/20':'bg-red-500/10 text-red-400 border border-red-500/20'}`;
  msg.classList.remove('hidden');
}
</script>
</body></html>