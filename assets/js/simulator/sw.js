const CACHE     = 'umrah-safar-v22';          // bump version = old cache cleared
const AUDIO_CACHE = 'umrah-rv-audio-v1';      // separate store for RV audio blobs

const ASSETS = [
  './index.html',
  './style.css',
  './data.js',
  './world.js',
  './game.js',
  './tools.js',
  'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js',
  'https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Hind+Siliguri:wght@400;500;600;700&family=Tiro+Bangla&display=swap'
];

/* ── Install: pre-cache app shell ─────────────────────────────────────── */
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE)
      .then(c => c.addAll(ASSETS))
      .then(() => self.skipWaiting())
  );
});

/* ── Activate: clean old caches ───────────────────────────────────────── */
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(k => k !== CACHE && k !== AUDIO_CACHE)
          .map(k => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

/* ── Fetch: route requests ────────────────────────────────────────────── */
self.addEventListener('fetch', e => {
  const url = e.request.url;

  /* ResponsiveVoice audio (getvoice.php) — cache-first, network on miss
     Audio files are large MP3s; once cached they work perfectly offline.  */
  if (url.includes('responsivevoice.org') && url.includes('getvoice')) {
    e.respondWith(serveAudio(e.request));
    return;
  }

  /* Aladhan prayer-times API — network-first, stale-while-revalidate     */
  if (url.includes('api.aladhan.com')) {
    e.respondWith(networkFirst(e.request));
    return;
  }

  /* Everything else (app shell, fonts, Three.js) — cache-first           */
  e.respondWith(cacheFirst(e.request));
});

/* ── Strategies ───────────────────────────────────────────────────────── */

async function serveAudio(req) {
  const c = await caches.open(AUDIO_CACHE);
  const cached = await c.match(req);
  if (cached) return cached;                        // ✅ offline hit

  try {
    const res = await fetch(req);
    if (res && res.status === 200) {
      c.put(req, res.clone());                      // cache for next time
    }
    return res;
  } catch (_) {
    // Offline and not cached — return empty 503; playDua() falls back to WS
    return new Response('', { status: 503, statusText: 'Offline' });
  }
}

async function cacheFirst(req) {
  const cached = await caches.match(req);
  if (cached) return cached;

  try {
    const res = await fetch(req);
    if (res && res.status === 200 && res.type !== 'opaque') {
      const c = await caches.open(CACHE);
      c.put(req, res.clone());
    }
    return res;
  } catch (_) {
    return cached || new Response('', { status: 503 });
  }
}

async function networkFirst(req) {
  try {
    const res = await fetch(req);
    if (res && res.status === 200) {
      const c = await caches.open(CACHE);
      c.put(req, res.clone());
    }
    return res;
  } catch (_) {
    const cached = await caches.match(req);
    return cached || new Response('', { status: 503 });
  }
}