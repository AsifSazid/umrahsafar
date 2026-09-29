/**
 * TravHub Prayer Times Widget
 * FILE PATH: /assets/js/prayer-times.js
 * Fixed: next-prayer calculation uses UTC+3 (KSA time), not local browser time
 */
const PrayerWidget = (() => {

    const CITIES = {
        makkah:  { name: 'Makkah',  lat: 21.3891, lng: 39.8579, tz: 3, ianaTz: 'Asia/Riyadh' },
        madinah: { name: 'Madinah', lat: 24.5247, lng: 39.5692, tz: 3, ianaTz: 'Asia/Riyadh' },
        dhaka:   { name: 'Dhaka',   lat: 23.8103, lng: 90.4125, tz: 6, ianaTz: 'Asia/Dhaka' },
    };

    const PRAYERS = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];

    async function fetchTimes(city) {
        const c = CITIES[city];
        // Use KSA date, not local date
        const ksaNow = new Date(new Date().getTime() + c.tz * 3600000);
        const dd   = String(ksaNow.getUTCDate()).padStart(2,'0');
        const mm   = String(ksaNow.getUTCMonth()+1).padStart(2,'0');
        const yyyy = ksaNow.getUTCFullYear();
        const cacheKey = `travhub_prayer_${city}_${yyyy}${mm}${dd}`;
        const cached = sessionStorage.getItem(cacheKey);
        if (cached) { try { return JSON.parse(cached); } catch(e) {} }
        try {
            const url = `https://api.aladhan.com/v1/timings/${dd}-${mm}-${yyyy}?latitude=${c.lat}&longitude=${c.lng}&method=4&timezonestring=${c.ianaTz}`;
            const res  = await fetch(url);
            const data = await res.json();
            if (data.code === 200 && data.data?.timings) {
                sessionStorage.setItem(cacheKey, JSON.stringify(data.data.timings));
                return data.data.timings;
            }
        } catch(e) { console.warn('Prayer time fetch failed:', e); }
        return null;
    }

    function getNextPrayer(timings, tzOffset) {
        if (!timings) return null;
        // Current time in city's timezone
        const now = new Date();
        const localMs  = now.getTime() + now.getTimezoneOffset() * 60000; // UTC ms
        const cityMs   = localMs + tzOffset * 3600000;
        const cityDate = new Date(cityMs);
        const nowMin   = cityDate.getHours() * 60 + cityDate.getMinutes();

        for (const name of PRAYERS) {
            const t = timings[name];
            if (!t) continue;
            const [h, m] = t.split(':').map(Number);
            if (h * 60 + m > nowMin) {
                return { name, time: t, minsLeft: (h * 60 + m) - nowMin };
            }
        }
        // All prayers passed — next is Fajr tomorrow
        const fajr = timings[PRAYERS[0]];
        const [fh, fm] = fajr ? fajr.split(':').map(Number) : [0, 0];
        const minsLeft = (24 * 60 - nowMin) + fh * 60 + fm;
        return { name: PRAYERS[0], time: fajr, minsLeft };
    }

    // Formats "HH:MM:SS" for a city's real timezone (not the browser's local time)
    function cityTimeStr(tzOffset) {
        const now      = new Date();
        const localMs  = now.getTime() + now.getTimezoneOffset() * 60000; // UTC ms
        const cityDate = new Date(localMs + tzOffset * 3600000);
        const hh = String(cityDate.getHours()).padStart(2, '0');
        const mm = String(cityDate.getMinutes()).padStart(2, '0');
        const ss = String(cityDate.getSeconds()).padStart(2, '0');
        return `${hh}:${mm}:${ss}`;
    }

    // Keeps every rendered clock ticking every second without re-fetching or
    // re-rendering the whole widget (prayer times/date don't need refetching
    // every second — only the clock digits do).
    let clockTimer = null;
    function startClockTicker() {
        if (clockTimer) return; // already running
        clockTimer = setInterval(() => {
            document.querySelectorAll('[data-clock-city]').forEach(el => {
                const tz = parseInt(el.dataset.clockTz, 10);
                el.textContent = cityTimeStr(tz);
            });
        }, 1000);
    }

    function renderWidget(containerId, city, timings) {
        const el = document.getElementById(containerId);
        if (!el) return;
        const c    = CITIES[city];
        const next = getNextPrayer(timings, c.tz);

        if (!timings) {
            el.innerHTML = `<div class="text-white/30 text-xs p-4 text-center">Prayer times unavailable</div>`;
            return;
        }

        // KSA date string
        const ksaNow = new Date(new Date().getTime() + c.tz * 3600000);
        const dateStr = ksaNow.toLocaleDateString('en-BD', { weekday:'short', day:'numeric', month:'short', timeZone:'UTC' });

        el.innerHTML = `
            <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-secondary"></i>
                        <span class="font-bold text-sm">${c.name} Prayer Times</span>
                    </div>
                    <span class="text-[10px] text-white/30">${dateStr}</span>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] text-white/30">${c.name} Time</span>
                    <span class="text-lg font-bold text-secondary tabular-nums" data-clock-city="${city}" data-clock-tz="${c.tz}">${cityTimeStr(c.tz)}</span>
                </div>
                ${next ? `
                <div class="bg-secondary/10 border border-secondary/20 rounded-xl px-4 py-2.5 mb-4 flex items-center justify-between">
                    <span class="text-xs text-white/60">Next: <strong class="text-secondary">${next.name}</strong></span>
                    <span class="text-xs font-bold text-secondary">${next.time}
                      <span class="text-white/40 font-normal">(${next.minsLeft < 60
                        ? next.minsLeft + 'min'
                        : Math.floor(next.minsLeft/60) + 'h ' + (next.minsLeft%60) + 'min'})</span>
                    </span>
                </div>` : ''}
                <div class="grid grid-cols-3 gap-2">
                    ${PRAYERS.map(name => {
                        const t      = timings[name] || '--:--';
                        const isNext = next && next.name === name;
                        return `<div class="text-center py-2 px-1 rounded-lg ${isNext ? 'bg-secondary/10 border border-secondary/20' : 'bg-white/3'}">
                            <p class="text-[9px] text-white/40 uppercase tracking-wider">${name}</p>
                            <p class="text-xs font-bold mt-0.5 ${isNext ? 'text-secondary' : ''}">${t}</p>
                        </div>`;
                    }).join('')}
                </div>
            </div>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    async function init(makkahContainerId, madinahContainerId, dhakaContainerId) {
        const [makkahTimes, madinahTimes, dhakaTimes] = await Promise.all([
            fetchTimes('makkah'),
            fetchTimes('madinah'),
            dhakaContainerId ? fetchTimes('dhaka') : Promise.resolve(null),
        ]);
        if (makkahContainerId)  renderWidget(makkahContainerId,  'makkah',  makkahTimes);
        if (madinahContainerId) renderWidget(madinahContainerId, 'madinah', madinahTimes);
        if (dhakaContainerId)   renderWidget(dhakaContainerId,   'dhaka',   dhakaTimes);
        startClockTicker();
    }

    return { init, fetchTimes, renderWidget };
})();