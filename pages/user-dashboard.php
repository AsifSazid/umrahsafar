<?php
// FILE PATH: /pages/user-dashboard.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'My Dashboard | TravHub';
$pageDescription = 'Track your Umrah bookings and manage your account.';
$csrf = csrfToken();
$user = isUserLoggedIn() ? ['id'=>$_SESSION['user_id'],'name'=>$_SESSION['user_name'],'email'=>$_SESSION['user_email']] : null;
$bookings = [];
$customBuilds = [];
if ($user) {
    try {
        require_once dirname(__DIR__) . '/data/server/db_connection.php';
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM bookings WHERE user_id = :uid ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([':uid' => $user['id']]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt2 = $db->prepare("SELECT * FROM custom_builds WHERE user_id = :uid ORDER BY created_at DESC LIMIT 20");
        $stmt2->execute([':uid' => $user['id']]);
        $customBuilds = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}
include dirname(__DIR__) . '/includes/header.php';
?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>
<style type="text/tailwindcss">
@layer components {
  .glass-card  { @apply bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl; }
  .btn-primary { @apply bg-secondary hover:bg-emerald text-primary font-semibold rounded-xl transition-all duration-300; }
  .input-field { @apply w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20; }
  .tab-btn    { @apply px-4 py-2.5 text-sm font-semibold rounded-xl transition-all; }
}
</style>

<main class="pt-28 pb-24 min-h-screen">
  <div class="max-w-4xl mx-auto px-4">

    <?php if (!$user): ?>
    <!-- AUTH FORMS -->
    <div class="max-w-sm mx-auto">
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-secondary/10 rounded-2xl flex items-center justify-center mx-auto mb-4"><i data-lucide="user" class="w-8 h-8 text-secondary"></i></div>
        <h1 class="text-2xl font-bold">My Account</h1>
        <p class="text-white/40 text-sm mt-1">Login or create account to track your bookings</p>
      </div>

      <!-- Guest Lookup -->
      <div class="glass-card p-6 mb-4">
        <h3 class="font-bold mb-4 flex items-center gap-2"><i data-lucide="search" class="w-4 h-4 text-secondary"></i> Track Booking (Guest)</h3>
        <div id="lookup-msg" class="hidden mb-3 px-3 py-2 rounded-lg text-xs font-medium"></div>
        <div class="flex gap-2">
          <input type="text" id="lookup-ref" placeholder="Enter booking ref (THR-BK-...)" class="input-field flex-1">
          <button onclick="lookupBooking()" class="btn-primary px-4 py-3 text-sm font-bold shrink-0">Track</button>
        </div>
        <div id="lookup-result" class="hidden mt-4"></div>
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 mb-4">
        <button onclick="switchTab('login')" id="tab-login" class="tab-btn flex-1 bg-secondary/10 text-secondary border border-secondary/20">Login</button>
        <button onclick="switchTab('register')" id="tab-register" class="tab-btn flex-1 bg-white/5 text-white/60">Register</button>
      </div>

      <!-- Login Form -->
      <div id="login-panel" class="glass-card p-6">
        <div id="login-msg" class="hidden mb-3 px-3 py-2 rounded-lg text-xs font-medium"></div>
        <form id="login-form" class="space-y-3">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="action" value="login">
          <input type="hidden" name="client_login" value="1">
          <div><label class="text-xs text-white/40 block mb-1">Username or Email</label><input type="text" name="login" required class="input-field" placeholder="your username or email"></div>
          <div><label class="text-xs text-white/40 block mb-1">Password</label><input type="password" name="password" required class="input-field" placeholder="••••••••"></div>
          <button type="submit" class="btn-primary w-full py-3 font-bold text-sm">Login</button>
        </form>
      </div>

      <!-- Register Form -->
      <div id="register-panel" class="glass-card p-6 hidden">
        <div id="reg-msg" class="hidden mb-3 px-3 py-2 rounded-lg text-xs font-medium"></div>
        <form id="reg-form" class="space-y-3">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="action" value="register">
          <div><label class="text-xs text-white/40 block mb-1">Full Name *</label><input type="text" name="name" required class="input-field" placeholder="Your name"></div>

          <div>
            <label class="text-xs text-white/40 block mb-1">Email *</label>
            <input type="email" name="email" id="reg-email" required class="input-field" placeholder="your@email.com">
            <p id="reg-email-msg" class="text-[10px] mt-1"></p>
          </div>

          <div>
            <label class="text-xs text-white/40 block mb-1">Phone</label>
            <input type="tel" name="phone" id="reg-phone" class="input-field" placeholder="+880 1X XX-XXXXXX">
            <p id="reg-phone-msg" class="text-[10px] mt-1"></p>
          </div>

          <div>
            <label class="text-xs text-white/40 block mb-1">Password *</label>
            <div class="relative">
              <input type="password" name="password" id="reg-password" required minlength="6" class="input-field pr-10" placeholder="Min. 6 characters">
              <button type="button" onclick="togglePasswordField('reg-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60" tabindex="-1">
                <i data-lucide="eye" class="w-4 h-4"></i>
              </button>
            </div>
            <p id="reg-password-msg" class="text-[10px] mt-1"></p>
          </div>

          <div>
            <label class="text-xs text-white/40 block mb-1">Confirm Password *</label>
            <div class="relative">
              <input type="password" name="confirm_password" id="reg-confirm-password" required minlength="6" class="input-field pr-10" placeholder="Re-enter password">
              <button type="button" onclick="togglePasswordField('reg-confirm-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60" tabindex="-1">
                <i data-lucide="eye" class="w-4 h-4"></i>
              </button>
            </div>
            <p id="reg-confirm-password-msg" class="text-[10px] mt-1"></p>
          </div>

          <button type="submit" id="reg-submit-btn" disabled class="btn-primary w-full py-3 font-bold text-sm disabled:opacity-40 disabled:cursor-not-allowed">Create Account</button>
        </form>
      </div>
    </div>

    <?php else: ?>
    <!-- LOGGED IN DASHBOARD -->
    <div class="flex items-center justify-between mb-8 flex-wrap gap-3">
      <div>
        <h1 class="text-2xl font-bold">Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
        <p class="text-white/40 text-sm"><?= htmlspecialchars($user['email']) ?></p>
      </div>
      <div class="flex gap-2">
        <a href="./package-builder.php" class="btn-primary px-5 py-2.5 text-sm font-bold flex items-center gap-2"><i data-lucide="plus" class="w-4 h-4"></i> New Journey</a>
        <button onclick="logout()" class="px-4 py-2.5 text-sm font-bold bg-white/5 hover:bg-white/10 rounded-xl flex items-center gap-2 text-white/60"><i data-lucide="log-out" class="w-4 h-4"></i> Logout</button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
      <?php
      $pending   = count(array_filter($bookings, fn($b)=>!in_array($b['status'], ['Confirmed','Cancelled','Completed'])));
      $confirmed = count(array_filter($bookings, fn($b)=>$b['status']==='Confirmed'));
      $custom    = count($customBuilds);
      $total     = count($bookings);
      $cards = [['Total Bookings',$total,'package','secondary'],['Pending',$pending,'clock','yellow-400'],['Confirmed',$confirmed,'check-circle','teal'],['Custom Builds',$custom,'settings','white/60']];
      foreach ($cards as [$label,$val,$icon,$color]): ?>
      <div class="glass-card p-4 flex items-center gap-3">
        <div class="w-9 h-9 bg-<?= $color ?>/10 rounded-xl flex items-center justify-center shrink-0"><i data-lucide="<?= $icon ?>" class="w-4 h-4 text-<?= $color ?>"></i></div>
        <div><p class="text-[10px] text-white/40 uppercase tracking-wider"><?= $label ?></p><p class="text-xl font-bold"><?= $val ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-5 border-b border-white/10 pb-3">
      <button onclick="dashTab('bookings')" id="dtab-bookings" class="tab-btn bg-secondary/10 text-secondary border border-secondary/20">Bookings</button>
      <button onclick="dashTab('custom')" id="dtab-custom" class="tab-btn bg-white/5 text-white/60">Custom Builds</button>
      <button onclick="dashTab('settings')" id="dtab-settings" class="tab-btn bg-white/5 text-white/60">Account</button>
    </div>

    <!-- Bookings Tab -->
    <div id="dtab-bookings-panel">
      <?php if ($bookings): ?>
      <div class="space-y-3">
        <?php foreach ($bookings as $b): ?>
        <div class="glass-card p-5">
          <div class="flex items-start justify-between flex-wrap gap-2 mb-3">
            <div>
              <span class="font-bold text-sm"><?= htmlspecialchars($b['booking_ref']) ?></span>
              <p class="text-white/40 text-xs mt-0.5"><?= htmlspecialchars($b['package_name'] ?? 'Custom Package') ?></p>
            </div>
            <span class="text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider
              <?= bookingStatusBadgeClass($b['status']) ?>">
              <?= htmlspecialchars($b['status']) ?>
            </span>
          </div>
          <div class="flex flex-wrap gap-4 text-xs text-white/40">
            <span><?= $b['adults'] ?> Adults<?= $b['children']?' + '.$b['children'].' Children':'' ?></span>
            <?php if($b['total_price_bdt']): ?><span class="text-secondary font-bold">৳<?= number_format($b['total_price_bdt'],0) ?></span><?php endif; ?>
            <span><?= date('d M Y', strtotime($b['created_at'])) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="text-center py-20 text-white/30">
        <i data-lucide="package" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="mb-4">No bookings yet.</p>
        <a href="./packages.php" class="btn-primary px-6 py-2.5 text-sm font-bold inline-block">Browse Packages</a>
      </div>
      <?php endif; ?>
    </div>

    <!-- Custom Builds Tab -->
    <div id="dtab-custom-panel" class="hidden">
      <?php if ($customBuilds): ?>
      <div class="space-y-3">
        <?php foreach ($customBuilds as $b): ?>
        <div class="glass-card p-5">
          <div class="flex items-start justify-between flex-wrap gap-2 mb-2">
            <div><span class="font-bold text-sm"><?= htmlspecialchars($b['build_ref']) ?></span><p class="text-white/40 text-xs mt-0.5"><?= htmlspecialchars($b['package_level'] ?? '') ?> | <?= htmlspecialchars($b['flight_type'] ?? '') ?></p></div>
            <span class="text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider <?= bookingStatusBadgeClass($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span>
          </div>
          <div class="flex flex-wrap gap-4 text-xs text-white/40">
            <span><?= $b['duration'] ?> days</span>
            <span><?= $b['adults'] ?> Adults</span>
            <?php if($b['total_price_sar']): ?><span class="text-secondary font-bold">SR <?= number_format($b['total_price_sar'],0) ?></span><?php endif; ?>
            <span><?= date('d M Y', strtotime($b['created_at'])) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="text-center py-20 text-white/30">
        <i data-lucide="settings" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="mb-4">No custom builds yet.</p>
        <a href="./package-builder.php" class="btn-primary px-6 py-2.5 text-sm font-bold inline-block">Build Your Journey</a>
      </div>
      <?php endif; ?>
    </div>

    <!-- Account Settings Tab -->
    <div id="dtab-settings-panel" class="hidden max-w-sm">
      <div class="glass-card p-6">
        <h3 class="font-bold mb-4 flex items-center gap-2"><i data-lucide="lock" class="w-4 h-4 text-secondary"></i> Change Password</h3>
        <div id="cpw-msg" class="hidden mb-3 px-3 py-2 rounded-lg text-xs font-medium"></div>
        <form id="cpw-form" class="space-y-3">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="action" value="change_password">
          <div><label class="text-xs text-white/40 block mb-1">Current Password</label><input type="password" name="old_password" required class="input-field"></div>
          <div><label class="text-xs text-white/40 block mb-1">New Password</label><input type="password" name="new_password" required minlength="6" class="input-field"></div>
          <div><label class="text-xs text-white/40 block mb-1">Confirm New Password</label><input type="password" name="confirm_password" required class="input-field"></div>
          <button type="submit" class="btn-primary w-full py-3 font-bold text-sm">Update Password</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/currency-handler.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
lucide.createIcons();

// Auth tab switch
function switchTab(tab) {
  ['login','register'].forEach(t => {
    document.getElementById(`${t}-panel`).classList.toggle('hidden', t !== tab);
    document.getElementById(`tab-${t}`).className = `tab-btn flex-1 ${t===tab?'bg-secondary/10 text-secondary border border-secondary/20':'bg-white/5 text-white/60'}`;
  });
}

// Dashboard tab switch
function dashTab(tab) {
  ['bookings','custom','settings'].forEach(t => {
    document.getElementById(`dtab-${t}-panel`).classList.toggle('hidden', t !== tab);
    document.getElementById(`dtab-${t}`).className = `tab-btn ${t===tab?'bg-secondary/10 text-secondary border border-secondary/20':'bg-white/5 text-white/60'}`;
  });
}

// Guest booking lookup
async function lookupBooking() {
  const ref = document.getElementById('lookup-ref').value.trim();
  if (!ref) return;
  const res = await fetch(`<?= BASE_URL ?>/api/booking-lookup.php?ref=${encodeURIComponent(ref)}`);
  const data = await res.json();
  const resultEl = document.getElementById('lookup-result');
  const msgEl = document.getElementById('lookup-msg');
  if (data.success && data.booking) {
    const b = data.booking;
    const statusColor = /Rejected|Cancelled/.test(b.status) ? 'text-red-400'
      : /Confirmed|Completed|Approved|Received/.test(b.status) ? 'text-secondary'
      : 'text-yellow-400';
    resultEl.innerHTML = `<div class="bg-white/5 border border-white/10 rounded-xl p-4 space-y-3">
      <div class="space-y-2">
        <div class="flex justify-between items-start">
          <span class="font-bold text-sm">${b.booking_ref}</span>
          <span class="text-xs font-bold ${statusColor} uppercase">${b.status}</span>
        </div>
        <p class="text-xs text-white/60">${b.package_name||'Custom Package'}</p>
        <div class="flex gap-4 text-xs text-white/40">
          <span>${b.adults} Adults</span>
          ${b.total_price_bdt ? `<span class="text-secondary font-bold">৳${parseInt(b.total_price_bdt).toLocaleString()}</span>` : ''}
          <span>${new Date(b.created_at).toLocaleDateString('en-BD',{day:'numeric',month:'short',year:'numeric'})}</span>
        </div>
      </div>
      <a href="<?= BASE_URL ?>/pages/booking-preview.php?ref=${encodeURIComponent(b.booking_ref)}" class="btn-primary w-full py-2.5 text-sm font-bold flex items-center justify-center gap-2">
        <i data-lucide="eye" class="w-4 h-4"></i> View Full Details
      </a>
    </div>`;
    resultEl.classList.remove('hidden');
    msgEl.classList.add('hidden');
    lucide.createIcons();
  } else {
    msgEl.textContent = data.message || 'Booking not found.';
    msgEl.className = 'mb-3 px-3 py-2 rounded-lg text-xs font-medium bg-red-500/10 text-red-400';
    msgEl.classList.remove('hidden');
    resultEl.classList.add('hidden');
  }
}

// Login
function startRateLimitCountdown(msgEl, seconds, btnEl, baseMessage) {
  clearInterval(btnEl._rateLimitTimer);
  let remaining = seconds;
  const tick = () => {
    if (remaining <= 0) {
      clearInterval(btnEl._rateLimitTimer);
      btnEl.disabled = false;
      msgEl.textContent = 'You can try again now.';
      return;
    }
    const m = Math.floor(remaining / 60), s = remaining % 60;
    msgEl.textContent = `${baseMessage} (${m}:${String(s).padStart(2,'0')} remaining)`;
    remaining--;
  };
  btnEl.disabled = true;
  tick();
  btnEl._rateLimitTimer = setInterval(tick, 1000);
}

document.getElementById('login-form')?.addEventListener('submit', async e => {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  btn.disabled = true; btn.textContent = 'Logging in...';
  const res = await fetch(`<?= BASE_URL ?>/api/user-auth.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();
  const msg = document.getElementById('login-msg');
  msg.className = `mb-3 px-3 py-2 rounded-lg text-xs font-medium ${data.success?'bg-secondary/10 text-secondary':'bg-red-500/10 text-red-400'}`;
  msg.classList.remove('hidden');
  if (data.success && data.redirect) { window.location.href = data.redirect; return; }
  if (data.retry_after_seconds) {
    btn.textContent = 'Login';
    startRateLimitCountdown(msg, data.retry_after_seconds, btn, 'Too many login attempts.');
    return;
  }
  msg.textContent = data.message;
  btn.disabled = false; btn.textContent = 'Login';
});

// Password show/hide toggle — swaps the input type and the eye/eye-off icon
function togglePasswordField(inputId, btnEl) {
  const input = document.getElementById(inputId);
  const showing = input.type === 'text';
  input.type = showing ? 'password' : 'text';
  btnEl.innerHTML = `<i data-lucide="${showing ? 'eye' : 'eye-off'}" class="w-4 h-4"></i>`;
  lucide.createIcons();
}

// ── Register form — live validation ──────────────────────────
const regState = { emailOk: false, phoneOk: true, passwordOk: false, confirmOk: false };

function updateRegSubmitState() {
  const btn = document.getElementById('reg-submit-btn');
  if (!btn) return;
  btn.disabled = !(regState.emailOk && regState.phoneOk && regState.passwordOk && regState.confirmOk);
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
      const res = await fetch(`<?= BASE_URL ?>/api/check-email.php?email=${encodeURIComponent(email)}`);
      const data = await res.json();
      if (!data.success) { setRegMsg('reg-email', data.message, false); return; }
      regState.emailOk = !data.exists;
      setRegMsg('reg-email', data.exists ? 'This email is already registered.' : 'Email available.', !data.exists);
    } catch (e) { setRegMsg('reg-email', 'Could not check email.', false); }
    updateRegSubmitState();
  }, 400);
});

let phoneCheckTimer;
document.getElementById('reg-phone')?.addEventListener('input', function() {
  clearTimeout(phoneCheckTimer);
  const phone = this.value.trim();
  if (!phone) { regState.phoneOk = true; setRegMsg('reg-phone', '', true); updateRegSubmitState(); return; }
  regState.phoneOk = false;
  updateRegSubmitState();
  phoneCheckTimer = setTimeout(async () => {
    try {
      const res = await fetch(`<?= BASE_URL ?>/api/check-phone.php?phone=${encodeURIComponent(phone)}`);
      const data = await res.json();
      if (!data.success) { setRegMsg('reg-phone', data.message, false); return; }
      regState.phoneOk = !data.exists;
      setRegMsg('reg-phone', data.exists ? 'This phone number is already registered.' : 'Phone number available.', !data.exists);
    } catch (e) { setRegMsg('reg-phone', 'Could not check phone number.', false); }
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
      const res = await fetch(`<?= BASE_URL ?>/api/password-validation.php`, { method: 'POST', body: fd });
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

// Register
document.getElementById('reg-form')?.addEventListener('submit', async e => {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  btn.disabled = true; btn.textContent = 'Creating account...';
  const res = await fetch(`<?= BASE_URL ?>/api/user-auth.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();
  const msg = document.getElementById('reg-msg');
  msg.textContent = data.message;
  msg.className = `mb-3 px-3 py-2 rounded-lg text-xs font-medium ${data.success?'bg-secondary/10 text-secondary':'bg-red-500/10 text-red-400'}`;
  msg.classList.remove('hidden');
  if (data.success) {
    // Registering creates a Guest account (pending admin verification) —
    // there's no session/redirect here, just reset the form for a clean slate.
    e.target.reset();
    Object.assign(regState, { emailOk: false, phoneOk: true, passwordOk: false, confirmOk: false });
    ['reg-email', 'reg-phone', 'reg-password', 'reg-confirm-password'].forEach(id => setRegMsg(id, '', true));
    btn.textContent = 'Create Account';
    updateRegSubmitState();
  } else {
    btn.disabled = false; btn.textContent = 'Create Account';
  }
});

// Change password (user)
document.getElementById('cpw-form')?.addEventListener('submit', async e => {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  btn.disabled = true; btn.textContent = 'Updating...';
  const res = await fetch(`<?= BASE_URL ?>/api/user-auth.php`, {method:'POST', body: new FormData(e.target)});
  const data = await res.json();
  const msg = document.getElementById('cpw-msg');
  msg.textContent = data.message;
  msg.className = `mb-3 px-3 py-2 rounded-lg text-xs font-medium ${data.success?'bg-secondary/10 text-secondary':'bg-red-500/10 text-red-400'}`;
  msg.classList.remove('hidden');
  if (data.success) e.target.reset();
  btn.disabled = false; btn.textContent = 'Update Password';
});

// Logout
async function logout() {
  const fd = new FormData();
  fd.append('csrf_token', '<?= htmlspecialchars($csrf) ?>');
  fd.append('action', 'logout');
  const res = await fetch(`<?= BASE_URL ?>/api/user-auth.php`, {method:'POST', body:fd});
  const data = await res.json();
  if (data.redirect) window.location.href = data.redirect;
}

// Enter key for lookup
document.getElementById('lookup-ref')?.addEventListener('keydown', e => {
  if (e.key === 'Enter') lookupBooking();
});
</script>
</body></html>