/**
 * TravHub Currency Handler
 * FILE PATH: /assets/js/currency-handler.js
 */
const CurrencyHandler = (() => {
    const CACHE_KEY = 'travhub_rates';
    const CACHE_TTL = 3600000;
    const FALLBACK  = { SAR: 1, USD: 0.27, BDT: 32.5 };
    const SYMBOLS   = { SAR: 'SR ', USD: '$', BDT: '৳' };

    let rates = { ...FALLBACK };
    let currentCurrency = localStorage.getItem('travhub_currency') || 'SAR';

    async function fetchRates() {
        const cached = localStorage.getItem(CACHE_KEY);
        if (cached) {
            try {
                const data = JSON.parse(cached);
                if (Date.now() - data.fetched_at < CACHE_TTL) {
                    rates = data.rates;
                    return;
                }
            } catch(e) {}
        }
        try {
            const base = (typeof window !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
            const res = await fetch(base + '/api/exchange-rates.php');
            if (res.ok) {
                const data = await res.json();
                if (data.SAR && data.USD && data.BDT) {
                    rates = { SAR: data.SAR, USD: data.USD, BDT: data.BDT };
                    localStorage.setItem(CACHE_KEY, JSON.stringify({ rates, fetched_at: Date.now() }));
                }
            }
        } catch (e) {
            console.warn('Exchange rate fetch failed, using fallback.');
        }
    }

    function convert(amountInSAR, toCurrency) {
        return amountInSAR * (rates[toCurrency] || 1);
    }

    function format(amountInSAR, currency) {
        currency = currency || currentCurrency;
        const converted = convert(amountInSAR, currency);
        const symbol = SYMBOLS[currency] || '';
        if (currency === 'BDT') {
            const n = Math.round(converted);
            const s = n.toString();
            if (s.length <= 3) return symbol + s;
            const last3 = s.slice(-3);
            const rest = s.slice(0, -3).replace(/(\d)(?=(\d{2})+$)/, '$1,');
            return symbol + rest + ',' + last3;
        }
        return symbol + Math.round(converted).toLocaleString();
    }

    function setCurrency(c) {
        currentCurrency = c;
        localStorage.setItem('travhub_currency', c);
        updateAllPrices();
        updateCurrencyButtons();
        // Dispatch event so package-builder can re-render
        document.dispatchEvent(new CustomEvent('currencyChanged', { detail: c }));
    }

    function getCurrency() { return currentCurrency; }
    function getRates()    { return { ...rates }; }

    function updateAllPrices() {
        document.querySelectorAll('[data-price-sar]').forEach(el => {
            const sar = parseFloat(el.getAttribute('data-price-sar'));
            el.textContent = format(sar);
        });
    }

    function updateCurrencyButtons() {
        document.querySelectorAll('.currency-btn').forEach(btn => {
            const c = btn.getAttribute('data-currency');
            if (c === currentCurrency) {
                btn.classList.add('bg-secondary', 'text-primary');
                btn.classList.remove('text-white/60', 'hover:text-white');
            } else {
                btn.classList.remove('bg-secondary', 'text-primary');
                btn.classList.add('text-white/60', 'hover:text-white');
            }
        });
    }

    function init() {
        fetchRates().then(() => {
            updateAllPrices();
            updateCurrencyButtons();
        });
        document.addEventListener('click', e => {
            if (e.target.classList.contains('currency-btn')) {
                const c = e.target.getAttribute('data-currency');
                if (c) setCurrency(c);
            }
        });
    }

    return { init, format, convert, setCurrency, getCurrency, getRates, updateAllPrices };
})();

document.addEventListener('DOMContentLoaded', CurrencyHandler.init);