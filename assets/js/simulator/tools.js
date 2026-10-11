'use strict';

/* ===== HELPERS ===== */
const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

let lang = (function () { try { return localStorage.getItem('umrahsafar:lang') === 'bn' ? 'bn' : 'en'; } catch (e) { return 'en'; } })();
const bnD = s => String(s).replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]);
const num = n => lang === 'bn' ? bnD(n) : String(n);

const store = {
  get(k) { try { return localStorage.getItem('umrahsafar:' + k); } catch (e) { return null; } },
  set(k, v) { try { localStorage.setItem('umrahsafar:' + k, v); } catch (e) { } },
  del(k) { try { localStorage.removeItem('umrahsafar:' + k); } catch (e) { } }
};

/* ===== AUDIO ===== */
// Strategy: ResponsiveVoice (best Arabic quality, audio cached by SW for offline)
//           → Web Speech API fallback (device-dependent, always available)
let currentUtterance = null;
let arabicVoice = null;
let _rvReady = false;

function _pickArabicVoice() {
  if (!('speechSynthesis' in window)) return;
  const voices = window.speechSynthesis.getVoices();
  arabicVoice = voices.find(v => v.lang === 'ar-SA') ||
                voices.find(v => v.lang === 'ar-EG') ||
                voices.find(v => v.lang.startsWith('ar')) ||
                null;
}
if ('speechSynthesis' in window) {
  _pickArabicVoice();
  window.speechSynthesis.onvoiceschanged = _pickArabicVoice;
}

// Wait for ResponsiveVoice to load (it's deferred)
window.responsiveVoiceButtonHidden = true; // hide default RV button
window.addEventListener('load', () => {
  if (window.responsiveVoice) {
    try { responsiveVoice.init(); } catch (e) {}
    _rvReady = true;
  }
});

function _rvAvailable() {
  // voiceSupport() returns false in Chrome (no built-in Arabic voice) — skip that check.
  // We just need RV object loaded; errors are handled via onerror callback in speak().
  if (window.responsiveVoice && !_rvReady) {
    try { responsiveVoice.init(); } catch (e) {}  // lazy init if load event missed
    _rvReady = true;
  }
  return _rvReady && !!window.responsiveVoice;
}

function stopAudio() {
  _rvPlayId++;  // invalidate any in-flight onend/onerror callbacks
  if (_rvAvailable()) try { responsiveVoice.cancel(); } catch (e) {}
  if ('speechSynthesis' in window) window.speechSynthesis.cancel();
  currentUtterance = null;
  document.querySelectorAll('.dua-audio').forEach(b => {
    b.classList.remove('playing');
    b.textContent = '▶ ' + (lang === 'bn' ? 'শুনুন' : 'Listen');
    delete b.dataset.playing;
  });
}

/* Web Speech API fallback — gender-aware pitch */
function _wsSpeakArabic(text, onDone) {
  if (!('speechSynthesis' in window)) {
    if (typeof window._toastFn === 'function') window._toastFn(lang === 'bn' ? 'এই ব্রাউজারে অডিও সাপোর্ট নেই' : 'Audio not supported in this browser', 'warn');
    onDone(); return;
  }
  _pickArabicVoice();
  // Chrome often has no Arabic voice — attempt anyway; onerror fires silently if unsupported
  const isMale = (store.get('gender') || window._gender || 'm') !== 'f';
  const utt = new SpeechSynthesisUtterance(text);
  utt.lang = arabicVoice ? arabicVoice.lang : 'ar-SA';
  if (arabicVoice) utt.voice = arabicVoice;
  utt.rate = arabicVoice ? (isMale ? 0.65 : 0.75) : (isMale ? 0.60 : 0.68);
  utt.pitch = isMale ? 0.70 : 1.10;
  utt.onend = onDone;
  utt.onerror = onDone;
  currentUtterance = utt;
  window.speechSynthesis.speak(utt);
}

/* Pick RV voice name by gender (m → Male, f → Female) */
function _rvVoiceName() {
  // store.get('gender') is set immediately on load from localStorage;
  // window._gender is updated by game.js but may lag at startup — so prefer store.
  const g = store.get('gender') || window._gender || 'm';
  return g === 'f' ? 'Arabic Female' : 'Arabic Male';
}

let _rvPlayId = 0;  // increments on every play; stop increments it too → stale callbacks ignored

function playDua(key, btn) {
  // If this button is already playing → stop only, don't restart
  if (btn.dataset.playing) {
    stopAudio();
    return;
  }

  stopAudio();  // stop any OTHER dua that's playing

  const text = DUA[key].ar;
  const myId = ++_rvPlayId;  // unique id for this play session

  btn.classList.add('playing');
  btn.textContent = '⏹ ' + (lang === 'bn' ? 'বন্ধ করুন' : 'Stop');
  btn.dataset.playing = '1';

  const resetBtn = () => {
    if (_rvPlayId !== myId) return;  // stale callback after stop/cancel — ignore
    btn.classList.remove('playing');
    btn.textContent = '▶ ' + (lang === 'bn' ? 'শুনুন' : 'Listen');
    delete btn.dataset.playing;
    currentUtterance = null;
  };

  // ResponsiveVoice — Arabic Female works well; Arabic Male may not be available on
  // free API key, so for male we try RV first and fall through to WS on any error.
  if (_rvAvailable()) {
    const voiceName = _rvVoiceName();
    responsiveVoice.speak(text, voiceName, {
      rate: voiceName === 'Arabic Male' ? 0.85 : 0.9,
      pitch: 1,
      volume: 1,
      onend: resetBtn,
      onerror: () => { if (_rvPlayId === myId) _wsSpeakArabic(text, resetBtn); }
    });
    return;
  }

  // Fallback: Web Speech API
  _wsSpeakArabic(text, resetBtn);
}

/* ===== DUA HTML ===== */
function duaHTML(key) {
  const d = DUA[key];
  const listenLabel = lang === 'bn' ? 'শুনুন' : 'Listen';
  return `<div class="dua" data-dua="${key}">
    <p class="ar" lang="ar" dir="rtl">${d.ar}</p>
    <p class="tr">${esc(d.tr[lang])}</p>
    <p class="mean">${esc(d.m[lang])}</p>
    <button class="dua-audio" onclick="playDua('${key}',this)">▶ ${esc(listenLabel)}</button>
  </div>`;
}

/* ===== FONT SIZE & THEME ===== */
let fontSize = store.get('fontSize') || 'md';
let theme = store.get('theme') || 'auto';

function applyFontSize(fs) {
  fontSize = fs; store.set('fontSize', fs);
  document.body.classList.remove('fs-sm', 'fs-lg', 'fs-xl');
  if (fs !== 'md') document.body.classList.add('fs-' + fs);
  document.querySelectorAll('.fs-opt').forEach(b => b.classList.toggle('active', b.dataset.fs === fs));
}
function applyTheme(th) {
  theme = th; store.set('theme', th);
  if (th === 'auto') document.documentElement.removeAttribute('data-theme');
  else document.documentElement.setAttribute('data-theme', th);
  document.querySelectorAll('.theme-opt').forEach(b => b.classList.toggle('active', b.dataset.theme === th));
}
applyFontSize(fontSize);
applyTheme(theme);

/* ===== SHARE / PROGRESS ===== */
function shareProgress(phase, walked) {
  const phases = ['ihram', 'route', 'approach', 'inside', 'tawaf_start', 'tawaf', 'maqam', 'zamzam', 'to_safa', 'sai_safa', 'sai', 'halq', 'free'];
  const idx = phases.indexOf(phase);
  const pct = Math.round(idx / phases.length * 100);
  const msg = `Umrah Safar | TravHub Global Limited\n${lang === 'bn' ? 'অগ্রগতি' : 'Progress'}: ${pct}%\n${window.location.href}`;
  if (navigator.share) {
    navigator.share({ title: 'Umrah Safar', text: msg, url: window.location.href }).catch(() => { });
  } else {
    navigator.clipboard.writeText(msg)
      .then(() => window._toastFn && window._toastFn(lang === 'bn' ? 'লিংক কপি হয়েছে!' : 'Link copied!', 'ok'))
      .catch(() => { });
  }
}

/* ===== PWA ===== */
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('sw.js').catch(() => { });
}

/* ===== SIDE PANEL ===== */
const TOOL_TABS = ['checklist', 'ihram', 'glossary', 'mistakes', 'calc', 'prayer', 'madinah', 'compare', 'settings'];
const TOOL_LABELS = {
  en: { checklist: 'Packing', ihram: 'Ihram Guide', glossary: 'Glossary', mistakes: 'Mistakes', calc: 'Distance', prayer: 'Prayer Times', madinah: 'Madinah', compare: 'Umrah vs Hajj', settings: 'Settings' },
  bn: { checklist: 'পোটলা', ihram: 'ইহরাম গাইড', glossary: 'পরিভাষা', mistakes: 'সাধারণ ভুল', calc: 'দূরত্ব', prayer: 'নামাজের ওয়াক্ত', madinah: 'মদিনা', compare: 'উমরাহ বনাম হজ', settings: 'সেটিংস' }
};

function openTools() {
  document.getElementById('sidePanel').hidden = false;
  document.getElementById('sidePanelTitle').textContent = lang === 'bn' ? 'টুলস' : 'Tools';
  renderToolTabs();
  activateToolTab(store.get('lastTab') || 'checklist');
}
function closeTools() { document.getElementById('sidePanel').hidden = true; stopAudio(); }

document.getElementById('toolsBtn').addEventListener('click', openTools);
document.getElementById('sideClose').addEventListener('click', closeTools);
document.getElementById('sidePanel').addEventListener('click', e => { if (e.target === document.getElementById('sidePanel')) closeTools(); });

function renderToolTabs() {
  const tabs = document.getElementById('sideTabs');
  tabs.innerHTML = TOOL_TABS.map(k =>
    `<button class="side-tab" data-tab="${k}" role="tab">${esc(TOOL_LABELS[lang][k])}</button>`
  ).join('');
  tabs.querySelectorAll('.side-tab').forEach(b => b.addEventListener('click', () => activateToolTab(b.dataset.tab)));
}

function activateToolTab(key) {
  store.set('lastTab', key);
  document.getElementById('sideTabs').querySelectorAll('.side-tab').forEach(b => b.classList.toggle('active', b.dataset.tab === key));
  document.getElementById('sideBody').innerHTML = `<section class="active">${renderTab(key)}</section>`;
  afterTabRender(key);
}

function renderTab(key) {
  switch (key) {
    case 'checklist': return renderChecklist();
    case 'ihram':     return renderIhramGuide();
    case 'glossary':  return renderGlossary();
    case 'mistakes':  return renderMistakes();
    case 'calc':      return renderCalc();
    case 'prayer':    return renderPrayerTimes();
    case 'madinah':   return renderMadinah();
    case 'compare':   return renderCompare();
    case 'settings':  return renderSettings();
    default: return '';
  }
}

function afterTabRender(key) {
  if (key === 'checklist') restoreChecklist();
  if (key === 'glossary') {
    const inp = document.getElementById('glossarySearch');
    if (inp) inp.addEventListener('input', () => filterGlossary(inp.value));
  }
  if (key === 'calc') {
    const btn = document.getElementById('calcBtn');
    if (btn) btn.addEventListener('click', doCalc);
  }
  if (key === 'prayer') {
    if (!prayerTimesData) {
      fetchPrayerTimes().then(data => {
        if (data && document.getElementById('sideBody')) {
          document.getElementById('sideBody').innerHTML = `<section class="active">${renderPrayerTimes()}</section>`;
        }
      });
    }
    if (store.get('prayerReminder') === '1') startPrayerReminder();
  }
  if (key === 'settings') {
    document.querySelectorAll('.fs-opt').forEach(b => {
      b.classList.toggle('active', b.dataset.fs === fontSize);
      b.addEventListener('click', () => applyFontSize(b.dataset.fs));
    });
    document.querySelectorAll('.theme-opt').forEach(b => {
      b.classList.toggle('active', b.dataset.theme === theme);
      b.addEventListener('click', () => applyTheme(b.dataset.theme));
    });
  }
}

/* ===== CHECKLIST ===== */
function renderChecklist() {
  const data = CHECKLIST[lang];
  let html = `<h3 style="margin:0 0 14px;font-family:var(--f-disp);font-weight:400">${lang === 'bn' ? 'পোটলা চেকলিস্ট' : 'Packing Checklist'}</h3>`;
  let ci = 0;
  for (const [cat, items] of Object.entries(data)) {
    html += `<div class="checklist-cat">${esc(cat)}</div><ul class="checklist">`;
    html += items.map(item => {
      const id = 'chk-' + ci++;
      return `<li><input type="checkbox" id="${id}" data-item="${esc(item)}"><label for="${id}">${esc(item)}</label></li>`;
    }).join('');
    html += `</ul>`;
  }
  html += `<button class="btn" style="margin-top:12px" onclick="clearChecklist()">${lang === 'bn' ? 'সব মুছুন' : 'Clear all'}</button>`;
  return html;
}
function restoreChecklist() {
  const saved = JSON.parse(store.get('checklist') || '{}');
  document.querySelectorAll('.checklist input').forEach(inp => { if (saved[inp.dataset.item]) inp.checked = true; });
  document.querySelectorAll('.checklist input').forEach(inp => inp.addEventListener('change', saveChecklist));
}
function saveChecklist() {
  const saved = {};
  document.querySelectorAll('.checklist input').forEach(inp => { if (inp.checked) saved[inp.dataset.item] = 1; });
  store.set('checklist', JSON.stringify(saved));
}
function clearChecklist() { store.del('checklist'); activateToolTab('checklist'); }

/* ===== IHRAM GUIDE ===== */
function renderIhramGuide() {
  const gender = window._gender || 'm';
  const steps = IHRAM_STEPS[gender][lang];
  let html = `<p style="font-size:var(--fs-sm);color:var(--ink-2);margin:0 0 16px">${lang === 'bn' ? 'ধাপে ধাপে ইহরাম পরার নিয়ম।' : 'Step-by-step guide to wearing Ihram.'}</p>`;
  html += `<div class="ihram-steps">`;
  steps.forEach((s, i) => {
    html += `<div class="ihram-step">
      <div class="ihram-step-num">${num(i + 1)}</div>
      <div class="ihram-step-body"><h4>${esc(s.t)}</h4><p>${esc(s.d)}</p></div>
    </div>`;
  });
  html += `</div>`;
  return html;
}

/* ===== GLOSSARY ===== */
function renderGlossary() {
  let html = `<input class="glossary-search" id="glossarySearch" placeholder="${lang === 'bn' ? 'খুঁজুন...' : 'Search...'}" type="search">`;
  html += `<div class="glossary-list">`;
  html += GLOSSARY.map((g, i) =>
    `<div class="glossary-item" data-idx="${i}">
      <span class="g-ar" lang="ar">${g.ar}</span>
      <span class="g-en">${esc(g.en)}</span>
      <span class="g-bn">${esc(g.bn)}</span>
    </div>`
  ).join('');
  html += `</div>`;
  return html;
}
function filterGlossary(q) {
  q = q.toLowerCase();
  document.querySelectorAll('.glossary-item').forEach(el => {
    const i = parseInt(el.dataset.idx), g = GLOSSARY[i];
    el.hidden = q && !g.en.toLowerCase().includes(q) && !g.bn.toLowerCase().includes(q) && !g.ar.includes(q);
  });
}

/* ===== MISTAKES ===== */
function renderMistakes() {
  const list = MISTAKES[lang];
  let html = `<p style="font-size:var(--fs-sm);color:var(--ink-2);margin:0 0 14px">${lang === 'bn' ? 'উমরাহয় সাধারণভাবে হওয়া ভুল ও তার সমাধান।' : 'Common errors during Umrah and how to avoid them.'}</p>`;
  html += `<div class="mistake-list">`;
  html += list.map(m =>
    `<div class="mistake">
      <h4>${esc(m.t)}</h4>
      <p>${esc(m.d)}</p>
      <p class="fix">${esc(m.fix)}</p>
    </div>`
  ).join('');
  html += `</div>`;
  return html;
}

/* ===== DISTANCE CALC ===== */
const HOTEL_ZONES = {
  en: {
    'Ibrahim Al Khalil Rd (Misfalah)': 'A',
    'Ajyad Street': 'B',
    'Jabal Omar': 'C',
    'Jarwal': 'D',
    'Masjid al-Jinn area': 'E'
  },
  bn: {
    'ইবরাহিম খলিল রোড (মিসফালাহ)': 'A',
    'আজিয়াদ স্ট্রিট': 'B',
    'জাবালে ওমর': 'C',
    'জারওয়াল': 'D',
    'মসজিদে জিন এলাকা': 'E'
  }
};
const GATE_INFO = {
  A: { no: 1,  km: 0.8, min: 10, max: 15 },
  B: { no: 1,  km: 0.7, min: 8,  max: 12 },
  C: { no: 79, km: 0.5, min: 5,  max: 10 },
  D: { no: 62, km: 1.0, min: 12, max: 18 },
  E: { no: 45, km: 0.9, min: 11, max: 16 }
};
function renderCalc() {
  const zones = HOTEL_ZONES[lang];
  const opts = Object.keys(zones).map(z => `<option value="${zones[z]}">${esc(z)}</option>`).join('');
  return `<div class="calc-form">
    <div>
      <label class="calc-label">${lang === 'bn' ? 'হোটেল এলাকা' : 'Hotel area'}</label>
      <select class="calc-select" id="calcZone">${opts}</select>
    </div>
    <button class="calc-btn" id="calcBtn">${lang === 'bn' ? 'দূরত্ব দেখুন' : 'Calculate distance'}</button>
    <div class="calc-result" id="calcResult" hidden></div>
  </div>`;
}
function doCalc() {
  const z = document.getElementById('calcZone').value;
  const info = GATE_INFO[z];
  const res = document.getElementById('calcResult');
  res.hidden = false;
  res.innerHTML = `
    <div class="dist-val">${info.km} ${lang === 'bn' ? 'কিমি' : 'km'}</div>
    <div class="dist-note">${lang === 'bn'
      ? `গেট নম্বর ${num(info.no)} পর্যন্ত আনুমানিক ${num(info.min)}–${num(info.max)} মিনিট হাঁটা`
      : `~${info.min}–${info.max} min walk to Gate ${info.no}`
    }</div>`;
}

/* ===== PRAYER TIMES ===== */
const PRAYER_KEYS = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
const PRAYER_NAMES = {
  en: { Fajr: 'Fajr', Sunrise: 'Sunrise', Dhuhr: 'Dhuhr', Asr: 'Asr', Maghrib: 'Maghrib', Isha: 'Isha' },
  bn: { Fajr: 'ফজর', Sunrise: 'সূর্যোদয়', Dhuhr: 'যোহর', Asr: 'আসর', Maghrib: 'মাগরিব', Isha: 'ইশা' }
};
let prayerTimesData = null;
let _prayerCheckInterval = null;
let _lastPrayerRemind = '';

async function fetchPrayerTimes() {
  try {
    const res = await fetch('https://api.aladhan.com/v1/timingsByCity?city=Makkah&country=SA&method=4');
    const json = await res.json();
    if (json.code === 200) { prayerTimesData = json.data.timings; return prayerTimesData; }
  } catch (e) {}
  return null;
}

function renderPrayerTimes() {
  let html = `<p style="font-size:var(--fs-sm);color:var(--ink-2);margin:0 0 12px">
    ${lang === 'bn' ? '🕌 আজকের নামাজের ওয়াক্ত — মক্কা মুকাররমা' : '🕌 Today\'s prayer times — Makkah al-Mukarramah'}
  </p>`;
  if (!prayerTimesData) {
    html += `<div style="text-align:center;padding:24px;color:var(--ink-2)">${lang === 'bn' ? 'লোড হচ্ছে...' : 'Loading...'}</div>`;
  } else {
    const now = new Date();
    const nowMin = now.getHours() * 60 + now.getMinutes();
    const nextKey = PRAYER_KEYS.filter(k => {
      const [h, m] = (prayerTimesData[k] || '00:00').split(':').map(Number);
      return h * 60 + m > nowMin;
    })[0] || '';
    html += `<div class="prayer-list">`;
    for (const key of PRAYER_KEYS) {
      const timeStr = (prayerTimesData[key] || '--:--').substring(0, 5);
      const [h, m] = timeStr.split(':').map(Number);
      const pMin = h * 60 + m;
      const isPast = pMin < nowMin;
      const isNext = key === nextKey;
      html += `<div class="prayer-row${isNext ? ' prayer-next' : isPast ? ' prayer-past' : ''}">
        <span class="prayer-name">${esc(PRAYER_NAMES[lang][key])}</span>
        <span class="prayer-time">${esc(timeStr)}</span>
        ${isNext ? `<span class="prayer-badge">${lang === 'bn' ? 'পরবর্তী' : 'Next'}</span>` : ''}
      </div>`;
    }
    html += `</div>`;
    const reminderOn = store.get('prayerReminder') === '1';
    html += `<div style="margin-top:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <span style="font-size:var(--fs-sm);color:var(--ink-2)">${lang === 'bn' ? '৫ মিনিট আগে মনে করিয়ে দাও' : 'Remind 5 min before'}</span>
      <button class="night-mode-btn${reminderOn ? ' active' : ''}" id="prayerReminderBtn" onclick="togglePrayerReminder(this)">
        ${reminderOn ? (lang === 'bn' ? '✓ চালু' : '✓ On') : (lang === 'bn' ? 'বন্ধ' : 'Off')}
      </button>
    </div>`;
  }
  html += `<button class="btn" style="margin-top:14px;font-size:var(--fs-sm)" onclick="refreshPrayerTab()">
    ${lang === 'bn' ? '↻ রিফ্রেশ' : '↻ Refresh'}
  </button>`;
  return html;
}

function togglePrayerReminder(btn) {
  const on = store.get('prayerReminder') !== '1';
  store.set('prayerReminder', on ? '1' : '0');
  btn.classList.toggle('active', on);
  btn.textContent = on ? (lang === 'bn' ? '✓ চালু' : '✓ On') : (lang === 'bn' ? 'বন্ধ' : 'Off');
  if (on) startPrayerReminder(); else stopPrayerReminder();
}

async function refreshPrayerTab() {
  prayerTimesData = null;
  const body = document.getElementById('sideBody');
  if (body) body.innerHTML = `<section class="active">${renderPrayerTimes()}</section>`;
  const data = await fetchPrayerTimes();
  if (data && document.getElementById('sideBody'))
    document.getElementById('sideBody').innerHTML = `<section class="active">${renderPrayerTimes()}</section>`;
}

function startPrayerReminder() {
  stopPrayerReminder();
  _prayerCheckInterval = setInterval(() => {
    if (store.get('prayerReminder') !== '1' || !prayerTimesData) return;
    const now = new Date(), nowMin = now.getHours() * 60 + now.getMinutes();
    for (const key of ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha']) {
      const [h, m] = (prayerTimesData[key] || '00:00').split(':').map(Number);
      const diff = h * 60 + m - nowMin;
      if (diff >= 4 && diff <= 5 && _lastPrayerRemind !== key + now.getDate()) {
        _lastPrayerRemind = key + now.getDate();
        const name = PRAYER_NAMES[lang][key];
        if (window._toastFn) window._toastFn(`🕌 ${lang === 'bn' ? name + ' নামাজের ৫ মিনিট বাকি' : name + ' prayer in 5 minutes'}`, 'ok');
      }
    }
  }, 30000);
}
function stopPrayerReminder() {
  if (_prayerCheckInterval) { clearInterval(_prayerCheckInterval); _prayerCheckInterval = null; }
}

/* Start reminder on load if previously enabled */
if (store.get('prayerReminder') === '1') {
  fetchPrayerTimes().then(d => { if (d) startPrayerReminder(); });
}

/* ===== MADINAH ===== */
function renderMadinah() {
  const places = MADINAH[lang];
  let html = `<p style="font-size:var(--fs-sm);color:var(--ink-2);margin:0 0 14px">${lang === 'bn' ? 'মদিনা শরিফের গুরুত্বপূর্ণ স্থানসমূহ।' : 'Key places to visit in Madinah.'}</p>`;
  html += places.map(p =>
    `<div class="madinah-place">
      <span class="ar-name" lang="ar">${p.ar}</span>
      <h4>${esc(p.n)}</h4>
      <p>${esc(p.d)}</p>
    </div>`
  ).join('');
  return html;
}

/* ===== COMPARE ===== */
function renderCompare() {
  const c = COMPARE[lang];
  let html = `<div style="overflow-x:auto"><table class="compare-table"><thead><tr>`;
  html += c.heads.map(h => `<th>${esc(h)}</th>`).join('');
  html += `</tr></thead><tbody>`;
  html += c.rows.map(r =>
    `<tr>${r.map((cell, i) => `<td${i === 0 ? ' class="row-head"' : ''}>${esc(cell)}</td>`).join('')}</tr>`
  ).join('');
  html += `</tbody></table></div>`;
  return html;
}

/* ===== SETTINGS ===== */
function renderSettings() {
  const fsLabels = { en: { sm: 'Small', md: 'Normal', lg: 'Large', xl: 'Extra Large' }, bn: { sm: 'ছোট', md: 'স্বাভাবিক', lg: 'বড়', xl: 'অনেক বড়' } };
  const thLabels = { en: { auto: 'Auto', light: 'Light', dark: 'Dark' }, bn: { auto: 'অটো', light: 'হালকা', dark: 'গাঢ়' } };
  return `<div style="display:flex;flex-direction:column;gap:20px">
    <div>
      <div class="calc-label">${lang === 'bn' ? 'লেখার আকার' : 'Text Size'}</div>
      <div class="fs-picker">
        ${['sm', 'md', 'lg', 'xl'].map(fs =>
          `<button class="fs-opt${fontSize === fs ? ' active' : ''}" data-fs="${fs}">${esc(fsLabels[lang][fs])}</button>`
        ).join('')}
      </div>
    </div>
    <div>
      <div class="calc-label">${lang === 'bn' ? 'থিম' : 'Theme'}</div>
      <div class="theme-picker">
        ${['auto', 'light', 'dark'].map(th =>
          `<button class="theme-opt${theme === th ? ' active' : ''}" data-theme="${th}">${esc(thLabels[lang][th])}</button>`
        ).join('')}
      </div>
    </div>
    <div>
      <div class="calc-label">${lang === 'bn' ? 'অগ্রগতি শেয়ার' : 'Share Progress'}</div>
      <button class="btn primary" onclick="shareProgress(window._phase||'intro', window._walked||0)">
        ${lang === 'bn' ? 'শেয়ার করুন' : 'Share Progress'}
      </button>
    </div>
  </div>`;
}

/* ===== LIGHTWEIGHT MODE TOGGLE ===== */
function toggleLightMode(btn) {
  const on = store.get('lightMode') !== '1';
  store.set('lightMode', on ? '1' : '0');
  btn.classList.toggle('active', on);
  btn.textContent = on ? (lang === 'bn' ? '⚡ চালু' : '⚡ On') : (lang === 'bn' ? 'বন্ধ' : 'Off');
  if (typeof window.applyLightweight === 'function') window.applyLightweight(on);
}

/* ===== NIGHT MODE TOGGLE ===== */
function toggleNightMode(btn) {
  const on = store.get('nightMode') !== '1';
  store.set('nightMode', on ? '1' : '0');
  btn.classList.toggle('active', on);
  btn.textContent = on ? (lang === 'bn' ? '🌙 চালু' : '🌙 On') : (lang === 'bn' ? '☀️ বন্ধ' : '☀️ Off');
  if (typeof setNightMode === 'function') setNightMode(on);
}

/* ===== LANG SWITCH (called by game.js) ===== */
function refreshToolsLang() {
  if (!document.getElementById('sidePanel').hidden) {
    renderToolTabs();
    activateToolTab(store.get('lastTab') || 'checklist');
  }
}