<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Already logged in
if (isAdminLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$error = '';
if (isset($_GET['timeout'])) {
    // handled below in the timeout-message block — kept as a $_GET check
    // only, login itself no longer POSTs to this page directly.
}
$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | TravHub</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
    tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins','sans-serif'] }, colors: { primary:'#1A2039', secondary:'#50BC81', navy:'#1E2648', dark:'#111625', emerald:'#3AAB71' } } } }
    </script>
</head>
<body class="bg-dark text-white font-sans min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-sm">
    <!-- Logo -->
    <div class="text-center mb-10">
        <div class="w-16 h-16 bg-gradient-to-br from-teal-400 to-secondary rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-secondary/20">
            <i data-lucide="shield-check" class="w-8 h-8 text-primary"></i>
        </div>
        <h1 class="text-2xl font-bold">TravHub Admin</h1>
        <p class="text-white/40 text-sm mt-1">Secure access only</p>
    </div>

    <!-- Timeout message -->
    <?php if (isset($_GET['timeout'])): ?>
    <div class="bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 text-sm rounded-xl p-4 mb-6 flex items-center gap-2">
        <i data-lucide="clock" class="w-4 h-4 shrink-0"></i> Session expired. Please log in again.
    </div>
    <?php endif; ?>

    <!-- Login error/message (populated by JS after the AJAX call) -->
    <div id="login-msg" class="hidden bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-xl p-4 mb-6 flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i> <span id="login-msg-text"></span>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6 bg-white/5 border border-white/10 rounded-xl p-1">
        <button type="button" id="tab-login-btn" onclick="switchAuthTab('login')" class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all bg-secondary text-primary">Login</button>
        <button type="button" id="tab-register-btn" onclick="switchAuthTab('register')" class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all text-white/50 hover:text-white">Register</button>
    </div>

    <!-- Login Form -->
    <form id="login-panel" class="bg-white/5 border border-white/10 rounded-2xl p-8 space-y-5">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="client_login" value="0">

        <div>
            <label class="text-sm text-white/60 block mb-2">Username or Email</label>
            <div class="relative">
                <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30"></i>
                <input type="text" name="login" required autocomplete="username"
                    class="w-full bg-white/5 border border-white/10 rounded-xl pl-11 pr-4 py-3.5 focus:border-secondary focus:outline-none transition-colors"
                    placeholder="superadmin or you@travhub.com.bd">
            </div>
        </div>
        <div>
            <label class="text-sm text-white/60 block mb-2">Password</label>
            <div class="relative">
                <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30"></i>
                <input type="password" name="password" required autocomplete="current-password"
                    class="w-full bg-white/5 border border-white/10 rounded-xl pl-11 pr-4 py-3.5 focus:border-secondary focus:outline-none transition-colors"
                    placeholder="••••••••">
            </div>
        </div>
        <button type="submit" class="w-full bg-secondary hover:bg-emerald text-primary font-semibold py-3.5 rounded-xl transition-all duration-300 flex items-center justify-center gap-2">
            <i data-lucide="log-in" class="w-5 h-5"></i> Sign In
        </button>
    </form>

    <!-- Register Form -->
    <div id="register-panel" class="hidden bg-white/5 border border-white/10 rounded-2xl p-8 space-y-4">
        <div id="reg-msg" class="hidden mb-1 px-3 py-2 rounded-lg text-xs font-medium"></div>
        <form id="reg-form" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="action" value="register">
            <div>
                <label class="text-sm text-white/60 block mb-2">Full Name</label>
                <input type="text" name="name" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 focus:border-secondary focus:outline-none transition-colors" placeholder="Your name">
            </div>
            <div>
                <label class="text-sm text-white/60 block mb-2">Email</label>
                <input type="email" name="email" id="reg-email" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 focus:border-secondary focus:outline-none transition-colors" placeholder="you@travhub.com.bd">
                <p id="reg-email-msg" class="text-[10px] mt-1"></p>
            </div>
            <div>
                <label class="text-sm text-white/60 block mb-2">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="reg-password" required minlength="6" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 pr-11 focus:border-secondary focus:outline-none transition-colors" placeholder="Min. 6 characters">
                    <button type="button" onclick="togglePasswordField('reg-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60" tabindex="-1"><i data-lucide="eye" class="w-4 h-4"></i></button>
                </div>
                <p id="reg-password-msg" class="text-[10px] mt-1"></p>
            </div>
            <div>
                <label class="text-sm text-white/60 block mb-2">Confirm Password</label>
                <div class="relative">
                    <input type="password" name="confirm_password" id="reg-confirm-password" required minlength="6" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 pr-11 focus:border-secondary focus:outline-none transition-colors" placeholder="Re-enter password">
                    <button type="button" onclick="togglePasswordField('reg-confirm-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60" tabindex="-1"><i data-lucide="eye" class="w-4 h-4"></i></button>
                </div>
                <p id="reg-confirm-password-msg" class="text-[10px] mt-1"></p>
            </div>
            <button type="submit" id="reg-submit-btn" disabled class="w-full bg-secondary hover:bg-emerald text-primary font-semibold py-3.5 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                <i data-lucide="user-plus" class="w-5 h-5"></i> Create Account
            </button>
        </form>
    </div>

    <p class="text-center text-white/20 text-xs mt-8">
        TravHub Admin Panel · Authorized Access Only
    </p>
</div>

<script>
lucide.createIcons();

// Shows a live MM:SS countdown in place of the rate-limit message, and
// keeps the submit button disabled until it hits zero.
function startRateLimitCountdown(msgTextEl, seconds, btnEl, baseMessage) {
  clearInterval(btnEl._rateLimitTimer);
  let remaining = seconds;
  const tick = () => {
    if (remaining <= 0) {
      clearInterval(btnEl._rateLimitTimer);
      btnEl.disabled = false;
      msgTextEl.textContent = 'You can try again now.';
      return;
    }
    const m = Math.floor(remaining / 60), s = remaining % 60;
    msgTextEl.textContent = `${baseMessage} (${m}:${String(s).padStart(2,'0')} remaining)`;
    remaining--;
  };
  btnEl.disabled = true;
  tick();
  btnEl._rateLimitTimer = setInterval(tick, 1000);
}

document.getElementById('login-panel')?.addEventListener('submit', async e => {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  const originalHtml = btn.innerHTML;
  btn.disabled = true; btn.innerHTML = 'Signing in...';
  const msgBox = document.getElementById('login-msg');
  const msgText = document.getElementById('login-msg-text');
  try {
    const res = await fetch(`${window.BASE_URL}/api/user-auth.php`, {method:'POST', body: new FormData(e.target)});
    const data = await res.json();
    if (data.success && data.redirect) {
      window.location.href = data.redirect;
      return;
    }
    msgBox.classList.remove('hidden');
    if (data.retry_after_seconds) {
      btn.innerHTML = originalHtml; lucide.createIcons();
      startRateLimitCountdown(msgText, data.retry_after_seconds, btn, 'Too many login attempts.');
      return; // countdown itself controls btn.disabled from here
    }
    msgText.textContent = data.message || 'Login failed.';
  } catch (err) {
    msgText.textContent = 'Login service unavailable. Please try again.';
    msgBox.classList.remove('hidden');
  }
  btn.disabled = false; btn.innerHTML = originalHtml;
  lucide.createIcons();
});

function switchAuthTab(tab) {
  const loginBtn = document.getElementById('tab-login-btn');
  const regBtn   = document.getElementById('tab-register-btn');
  const loginPanel = document.getElementById('login-panel');
  const regPanel    = document.getElementById('register-panel');
  const active   = 'flex-1 py-2.5 rounded-lg text-sm font-bold transition-all bg-secondary text-primary';
  const inactive = 'flex-1 py-2.5 rounded-lg text-sm font-bold transition-all text-white/50 hover:text-white';
  if (tab === 'login') {
    loginBtn.className = active; regBtn.className = inactive;
    loginPanel.classList.remove('hidden'); regPanel.classList.add('hidden');
  } else {
    regBtn.className = active; loginBtn.className = inactive;
    regPanel.classList.remove('hidden'); loginPanel.classList.add('hidden');
  }
}

// Password show/hide toggle — swaps the input type and the eye/eye-off icon
function togglePasswordField(inputId, btnEl) {
  const input = document.getElementById(inputId);
  const showing = input.type === 'text';
  input.type = showing ? 'password' : 'text';
  btnEl.innerHTML = `<i data-lucide="${showing ? 'eye' : 'eye-off'}" class="w-4 h-4"></i>`;
  lucide.createIcons();
}

// ── Register form — live validation (no phone field on this form) ──
const regState = { emailOk: false, passwordOk: false, confirmOk: false };

function updateRegSubmitState() {
  const btn = document.getElementById('reg-submit-btn');
  if (!btn) return;
  btn.disabled = !(regState.emailOk && regState.passwordOk && regState.confirmOk);
}

function setRegMsg(fieldId, text, ok) {
  const el = document.getElementById(fieldId + '-msg');
  if (!el) return;
  el.textContent = text;
  el.className = `text-[10px] mt-1 ${ok ? 'text-secondary' : 'text-red-400'}`;
}

let emailCheckTimer;
document.getElementById('reg-email')?.addEventListener('input', function() {
  clearTimeout(emailCheckTimer);
  const email = this.value.trim();
  regState.emailOk = false;
  updateRegSubmitState();
  if (!email) { setRegMsg('reg-email', '', true); return; }
  emailCheckTimer = setTimeout(async () => {
    try {
      const res = await fetch(`${window.BASE_URL}/api/check-email.php?email=${encodeURIComponent(email)}`);
      const data = await res.json();
      if (!data.success) { setRegMsg('reg-email', data.message, false); return; }
      regState.emailOk = !data.exists;
      setRegMsg('reg-email', data.exists ? 'This email is already registered.' : 'Email available.', !data.exists);
    } catch (e) { setRegMsg('reg-email', 'Could not check email.', false); }
    updateRegSubmitState();
  }, 400);
});

let passwordCheckTimer;
document.getElementById('reg-password')?.addEventListener('input', function() {
  clearTimeout(passwordCheckTimer);
  const pw = this.value;
  regState.passwordOk = false;
  updateRegSubmitState();
  if (!pw) { setRegMsg('reg-password', '', true); checkRegConfirmMatch(); return; }
  passwordCheckTimer = setTimeout(async () => {
    try {
      const fd = new FormData(); fd.append('password', pw);
      const res = await fetch(`${window.BASE_URL}/api/password-validation.php`, { method: 'POST', body: fd });
      const data = await res.json();
      regState.passwordOk = !!data.valid;
      setRegMsg('reg-password', data.message, data.valid);
    } catch (e) { setRegMsg('reg-password', 'Could not validate password.', false); }
    updateRegSubmitState();
    checkRegConfirmMatch();
  }, 300);
});

function checkRegConfirmMatch() {
  const pw = document.getElementById('reg-password')?.value || '';
  const conf = document.getElementById('reg-confirm-password')?.value || '';
  if (!conf) { regState.confirmOk = false; setRegMsg('reg-confirm-password', '', true); updateRegSubmitState(); return; }
  regState.confirmOk = (pw === conf) && pw.length >= 6;
  setRegMsg('reg-confirm-password', regState.confirmOk ? 'Passwords match.' : 'Passwords do not match.', regState.confirmOk);
  updateRegSubmitState();
}
document.getElementById('reg-confirm-password')?.addEventListener('input', checkRegConfirmMatch);

document.getElementById('reg-form')?.addEventListener('submit', async e => {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  btn.disabled = true; btn.innerHTML = 'Creating account...';
  const res = await fetch(`${window.BASE_URL}/api/user-auth.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();
  const msg = document.getElementById('reg-msg');
  msg.textContent = data.message;
  msg.className = `mb-1 px-3 py-2 rounded-lg text-xs font-medium ${data.success?'bg-secondary/10 text-secondary':'bg-red-500/10 text-red-400'}`;
  msg.classList.remove('hidden');
  if (data.success) {
    // Registering creates a Guest account — guests CAN log in here, so
    // just reset the form and switch to the Login tab.
    e.target.reset();
    Object.assign(regState, { emailOk: false, passwordOk: false, confirmOk: false });
    ['reg-email', 'reg-password', 'reg-confirm-password'].forEach(id => setRegMsg(id, '', true));
    updateRegSubmitState();
    setTimeout(() => switchAuthTab('login'), 1200);
  }
  btn.disabled = false; btn.innerHTML = '<i data-lucide="user-plus" class="w-5 h-5"></i> Create Account';
  lucide.createIcons();
});
</script>
</body>
</html>