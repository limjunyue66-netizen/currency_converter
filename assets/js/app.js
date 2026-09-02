/**
 * Currency Converter - Main Application
 */

const App = {
    basePath: (() => {
        const path = window.location.pathname;
        const idx = path.lastIndexOf('/');
        if (idx <= 0) return '';
        const dir = path.substring(0, idx);
        const segments = dir.split('/').filter(Boolean);
        const last = segments[segments.length - 1] || '';
        if (['api', 'assets', 'includes'].includes(last)) {
            segments.pop();
        }
        return segments.length ? '/' + segments.join('/') : '';
    })(),

    currencies: [],
    chartPeriod: '30d',
    chartInstance: null,
    resizeTimer: null,

    apiUrl(endpoint) {
        return `${this.basePath}/api/${endpoint}`;
    },

    async fetchJson(url) {
        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
            cache: 'no-store',
        });
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || I18n.t('error.generic'));
        }
        return data;
    },

    debounce(fn, delay) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    },

    init() {
        this.bindMobileMenu();
        this.detectPage();
    },

    detectPage() {
        if (document.getElementById('converter-form')) {
            this.initConverter();
        }
        if (document.getElementById('rates-table')) {
            this.initRatesPage();
        }
    },

    bindMobileMenu() {
        const toggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('.main-nav');
        if (!toggle || !nav) return;

        toggle.addEventListener('click', () => {
            const open = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', String(open));
        });
    },

    /* ─── Flag Helpers ─── */

    updateSelectedCurrency(pickerEl, currency) {
        const selectedBtn = pickerEl.querySelector('.currency-selected');
        const code = currency.code;
        selectedBtn.dataset.code = code;

        let flagImg = selectedBtn.querySelector('.flag-icon');
        if (!flagImg) {
            flagImg = document.createElement('img');
            flagImg.className = 'flag-icon';
            flagImg.width = 28;
            flagImg.height = 21;
            selectedBtn.prepend(flagImg);
        }
        flagImg.src = Flags.getUrl(code, 40);
        flagImg.alt = code;

        let info = selectedBtn.querySelector('.currency-info');
        if (!info) {
            info = document.createElement('span');
            info.className = 'currency-info';
            info.innerHTML = '<span class="currency-code"></span><span class="currency-name"></span>';
            selectedBtn.appendChild(info);
        }
        info.querySelector('.currency-code').textContent = code;
        info.querySelector('.currency-name').textContent = I18n.getCurrencyName(currency);
    },

    /* ─── Currency Picker ─── */

    async loadCurrencies() {
        if (this.currencies.length) return this.currencies;
        const data = await this.fetchJson(this.apiUrl('currencies.php'));
        this.currencies = data.currencies || [];
        return this.currencies;
    },

    initConverter() {
        this.loadCurrencies().then(() => {
            document.querySelectorAll('.currency-picker').forEach((picker) => {
                const type = picker.dataset.picker;
                const code = document.getElementById(`${type}-currency`).value;
                const currency = this.currencies.find((c) => c.code === code);
                if (currency) this.updateSelectedCurrency(picker, currency);
            });
            this.setupPickers();
            this.setupConverterForm();
            this.setupSwapButton();
            this.setupChart();
            this.performConversion();
        }).catch((err) => {
            this.showConverterError(err.message);
        });

        document.addEventListener('langchange', () => {
            this.refreshPickerLabels();
            this.loadHistoryChart();
        });
    },

    setupPickers() {
        document.querySelectorAll('.currency-picker').forEach((picker) => {
            this.initPicker(picker);
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.currency-picker')) {
                document.querySelectorAll('.currency-dropdown').forEach((d) => d.classList.add('hidden'));
                document.querySelectorAll('.currency-picker').forEach((p) => p.classList.remove('is-open'));
            }
        });
    },

    initPicker(pickerEl) {
        const type = pickerEl.dataset.picker;
        const hiddenInput = document.getElementById(`${type}-currency`);
        const selectedBtn = pickerEl.querySelector('.currency-selected');
        const searchInput = pickerEl.querySelector('.currency-search');
        const dropdown = pickerEl.querySelector('.currency-dropdown');

        const showDropdown = (query) => {
            document.querySelectorAll('.currency-picker').forEach((p) => p.classList.remove('is-open'));
            document.querySelectorAll('.currency-dropdown').forEach((d) => {
                if (d !== dropdown) d.classList.add('hidden');
            });
            pickerEl.classList.add('is-open');
            dropdown.classList.remove('hidden');
            this.renderDropdownOptions(dropdown, query ?? searchInput.value);
        };

        const selectCurrency = (code) => {
            const currency = this.currencies.find((c) => c.code === code);
            if (!currency) return;

            hiddenInput.value = code;
            this.updateSelectedCurrency(pickerEl, currency);
            searchInput.value = '';
            dropdown.classList.add('hidden');
            pickerEl.classList.remove('is-open');

            if (type === 'from' || type === 'to') {
                this.loadHistoryChart();
            }
        };

        selectedBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            searchInput.value = '';
            showDropdown('');
            searchInput.focus();
        });

        searchInput.addEventListener('focus', () => showDropdown(searchInput.value));
        searchInput.addEventListener('click', (e) => {
            e.stopPropagation();
            showDropdown(searchInput.value);
        });

        searchInput.addEventListener('input', () => showDropdown(searchInput.value));

        searchInput.addEventListener('keydown', (e) => {
            const options = dropdown.querySelectorAll('.currency-option:not(.no-results)');
            let highlighted = dropdown.querySelector('.highlighted');
            let idx = Array.from(options).indexOf(highlighted);

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (dropdown.classList.contains('hidden')) showDropdown(searchInput.value);
                idx = Math.min(idx + 1, options.length - 1);
                options.forEach((o) => o.classList.remove('highlighted'));
                if (options[idx]) options[idx].classList.add('highlighted');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                idx = Math.max(idx - 1, 0);
                options.forEach((o) => o.classList.remove('highlighted'));
                if (options[idx]) options[idx].classList.add('highlighted');
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (dropdown.classList.contains('hidden')) showDropdown(searchInput.value);
                const opts = dropdown.querySelectorAll('.currency-option:not(.no-results)');
                const target = dropdown.querySelector('.highlighted') || opts[0];
                if (target) selectCurrency(target.dataset.code);
            } else if (e.key === 'Escape') {
                dropdown.classList.add('hidden');
                pickerEl.classList.remove('is-open');
            }
        });

        dropdown.addEventListener('click', (e) => {
            const option = e.target.closest('.currency-option');
            if (!option || option.classList.contains('no-results')) return;
            selectCurrency(option.dataset.code);
        });
    },

    filterCurrencies(query) {
        const q = (query || '').toLowerCase().trim();
        if (!q) return [...this.currencies];

        const aliases = {
            malaysia: 'MYR', msia: 'MYR', singapore: 'SGD', usa: 'USD', us: 'USD',
            america: 'USD', 'united states': 'USD', uk: 'GBP', britain: 'GBP',
            england: 'GBP', china: 'CNY', japan: 'JPY', korea: 'KRW',
            'south korea': 'KRW', thailand: 'THB', indonesia: 'IDR',
            philippines: 'PHP', vietnam: 'VND', india: 'INR', australia: 'AUD',
            canada: 'CAD', taiwan: 'TWD', 'hong kong': 'HKD', macau: 'MOP',
            euro: 'EUR', europe: 'EUR', russia: 'RUB', brazil: 'BRL',
            mexico: 'MXN', switzerland: 'CHF', norway: 'NOK', sweden: 'SEK',
            denmark: 'DKK', 'new zealand': 'NZD', 'south africa': 'ZAR',
            'saudi arabia': 'SAR', uae: 'AED', dubai: 'AED', qatar: 'QAR',
            turkey: 'TRY', egypt: 'EGP', nigeria: 'NGN', pakistan: 'PKR',
            bangladesh: 'BDT', 'sri lanka': 'LKR', nepal: 'NPR', cambodia: 'KHR',
            laos: 'LAK', myanmar: 'MMK', brunei: 'BND', argentina: 'ARS',
            chile: 'CLP', colombia: 'COP', peru: 'PEN', israel: 'ILS',
            iraq: 'IQD', iran: 'IRR', ukraine: 'UAH', poland: 'PLN',
            马来西亚: 'MYR', 新加坡: 'SGD', 中国: 'CNY', 日本: 'JPY',
            韩国: 'KRW', 泰国: 'THB', 印尼: 'IDR', 印度尼西亚: 'IDR',
            菲律宾: 'PHP', 越南: 'VND', 印度: 'INR', 澳大利亚: 'AUD',
            加拿大: 'CAD', 台湾: 'TWD', 香港: 'HKD', 澳门: 'MOP',
            欧元: 'EUR', 英国: 'GBP', 美国: 'USD', 沙特: 'SAR',
        };

        const words = q.split(/\s+/).filter(Boolean);

        let filtered = this.currencies.filter((c) => {
            const haystack = [
                c.code,
                c.currency_en,
                c.currency_zh,
                c.country_en,
                c.country_zh,
                c.symbol,
            ].join(' ').toLowerCase();

            return words.every((word) => haystack.includes(word));
        });

        const aliasCode = aliases[q];
        if (aliasCode) {
            const match = this.currencies.find((c) => c.code === aliasCode);
            if (match) {
                filtered = [match, ...filtered.filter((c) => c.code !== aliasCode)];
            }
        }

        return filtered;
    },

    renderDropdownOptions(dropdown, query) {
        const filtered = this.filterCurrencies(query);

        dropdown.innerHTML = '';

        if (!filtered.length) {
            const empty = document.createElement('button');
            empty.type = 'button';
            empty.className = 'currency-option no-results';
            empty.textContent = I18n.t('converter.noResults');
            dropdown.appendChild(empty);
            return;
        }

        filtered.forEach((c, index) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'currency-option' + (index === 0 ? ' highlighted' : '');
            btn.dataset.code = c.code;
            btn.innerHTML = `
                ${Flags.imgHtml(c.code, 22, 'flag-icon flag-icon-sm')}
                <span class="opt-text">
                    <span class="opt-code">${c.code} — ${I18n.getCurrencyName(c)}</span>
                    <span class="opt-detail">${I18n.getCountryName(c)}</span>
                </span>
            `;
            dropdown.appendChild(btn);
        });
    },

    refreshPickerLabels() {
        document.querySelectorAll('.currency-picker').forEach((picker) => {
            const type = picker.dataset.picker;
            const code = document.getElementById(`${type}-currency`).value;
            const currency = this.currencies.find((c) => c.code === code);
            if (!currency) return;
            this.updateSelectedCurrency(picker, currency);
        });
    },

    setupSwapButton() {
        const swapBtn = document.getElementById('swap-btn');
        if (!swapBtn) return;

        swapBtn.addEventListener('click', () => {
            const fromInput = document.getElementById('from-currency');
            const toInput = document.getElementById('to-currency');
            const temp = fromInput.value;
            fromInput.value = toInput.value;
            toInput.value = temp;

            this.refreshPickerLabels();
            ['from', 'to'].forEach((type) => {
                const picker = document.querySelector(`[data-picker="${type}"]`);
                const code = document.getElementById(`${type}-currency`).value;
                const currency = this.currencies.find((c) => c.code === code);
                if (picker && currency) {
                    this.updateSelectedCurrency(picker, currency);
                }
            });

            this.loadHistoryChart();
        });
    },

    setupConverterForm() {
        const form = document.getElementById('converter-form');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.performConversion();
        });
    },

    async performConversion() {
        const amount = document.getElementById('amount').value;
        const from = document.getElementById('from-currency').value;
        const to = document.getElementById('to-currency').value;
        const btn = document.getElementById('convert-btn');
        const resultEl = document.getElementById('conversion-result');
        const errorEl = document.getElementById('converter-error');
        const warningEl = document.getElementById('converter-warning');

        errorEl.classList.add('hidden');
        warningEl.classList.add('hidden');
        btn.disabled = true;

        try {
            const url = `${this.apiUrl('convert.php')}?amount=${encodeURIComponent(amount)}&from=${from}&to=${to}`;
            const data = await this.fetchJson(url);

            resultEl.classList.remove('hidden');

            const flagsEl = document.getElementById('result-flags');
            if (flagsEl) {
                flagsEl.innerHTML = `
                    ${Flags.imgHtml(data.from, 36, 'flag-icon flag-icon-lg')}
                    <span class="flag-arrow">→</span>
                    ${Flags.imgHtml(data.to, 36, 'flag-icon flag-icon-lg')}
                `;
            }

            document.getElementById('result-summary').textContent = data.formatted.summary;
            document.getElementById('result-rate').textContent = data.formatted.unit;
            document.getElementById('result-updated').textContent =
                `${I18n.t('converter.lastUpdated')}: ${data.fetched_at}`;
            document.getElementById('result-source').textContent =
                `${I18n.t('converter.source')}: ${data.source}`;

            if (data.warning) {
                warningEl.textContent = data.warning;
                warningEl.classList.remove('hidden');
            }
        } catch (err) {
            resultEl.classList.add('hidden');
            this.showConverterError(err.message);
        } finally {
            btn.disabled = false;
        }
    },

    showConverterError(message) {
        const errorEl = document.getElementById('converter-error');
        if (!errorEl) return;
        errorEl.textContent = message || I18n.t('error.rateUnavailable');
        errorEl.classList.remove('hidden');
    },

    /* ─── Historical Chart ─── */

    setupChart() {
        const periodBtns = document.querySelectorAll('.period-btn');
        periodBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                periodBtns.forEach((b) => b.classList.remove('active'));
                btn.classList.add('active');
                this.chartPeriod = btn.dataset.period;
                this.loadHistoryChart();
            });
        });

        window.addEventListener('resize', () => {
            clearTimeout(this.resizeTimer);
            this.resizeTimer = setTimeout(() => {
                if (this.chartData) {
                    this.drawChart(this.chartData);
                }
            }, 250);
        });

        this.loadHistoryChart();
    },

    async loadHistoryChart() {
        const canvas = document.getElementById('history-chart');
        const loading = document.getElementById('chart-loading');
        const errorEl = document.getElementById('chart-error');
        if (!canvas) return;

        const from = document.getElementById('from-currency')?.value || 'USD';
        const to = document.getElementById('to-currency')?.value || 'EUR';

        loading?.classList.remove('hidden');
        errorEl?.classList.add('hidden');

        try {
            const url = `${this.apiUrl('history.php')}?from=${from}&to=${to}&period=${this.chartPeriod}`;
            const data = await this.fetchJson(url);

            if (!data.points || !data.points.length) {
                errorEl.textContent = I18n.t('chart.noData');
                errorEl.classList.remove('hidden');
                this.chartData = null;
                return;
            }

            this.chartData = data;
            this.drawChart(data);
        } catch (err) {
            errorEl.textContent = err.message || I18n.t('chart.error');
            errorEl.classList.remove('hidden');
            this.chartData = null;
        } finally {
            loading?.classList.add('hidden');
        }
    },

    drawChart(data) {
        const canvas = document.getElementById('history-chart');
        if (!canvas || !data.points?.length) return;

        const wrapper = canvas.parentElement;
        const dpr = window.devicePixelRatio || 1;
        const rect = wrapper.getBoundingClientRect();
        const width = rect.width;
        const height = rect.height;

        canvas.width = width * dpr;
        canvas.height = height * dpr;
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;

        const ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);
        ctx.clearRect(0, 0, width, height);

        const padding = { top: 20, right: 20, bottom: 40, left: 60 };
        const chartW = width - padding.left - padding.right;
        const chartH = height - padding.top - padding.bottom;

        const points = data.points;
        const rates = points.map((p) => p.rate);
        const minRate = Math.min(...rates);
        const maxRate = Math.max(...rates);
        const range = maxRate - minRate || 1;
        const yMin = minRate - range * 0.05;
        const yMax = maxRate + range * 0.05;

        const getX = (i) => padding.left + (i / (points.length - 1 || 1)) * chartW;
        const getY = (rate) => padding.top + chartH - ((rate - yMin) / (yMax - yMin)) * chartH;

        // Grid lines
        ctx.strokeStyle = 'rgba(90, 60, 130, 0.45)';
        ctx.lineWidth = 1;
        const gridLines = 5;
        for (let i = 0; i <= gridLines; i++) {
            const y = padding.top + (chartH / gridLines) * i;
            ctx.beginPath();
            ctx.moveTo(padding.left, y);
            ctx.lineTo(padding.left + chartW, y);
            ctx.stroke();

            const val = yMax - ((yMax - yMin) / gridLines) * i;
            ctx.fillStyle = '#7a6a96';
            ctx.font = '11px Plus Jakarta Sans, sans-serif';
            ctx.textAlign = 'right';
            ctx.fillText(val.toFixed(4), padding.left - 8, y + 4);
        }

        // Gradient fill
        const gradient = ctx.createLinearGradient(0, padding.top, 0, padding.top + chartH);
        gradient.addColorStop(0, 'rgba(139, 92, 246, 0.4)');
        gradient.addColorStop(1, 'rgba(139, 92, 246, 0)');

        ctx.beginPath();
        ctx.moveTo(getX(0), getY(points[0].rate));
        for (let i = 1; i < points.length; i++) {
            ctx.lineTo(getX(i), getY(points[i].rate));
        }
        ctx.lineTo(getX(points.length - 1), padding.top + chartH);
        ctx.lineTo(getX(0), padding.top + chartH);
        ctx.closePath();
        ctx.fillStyle = gradient;
        ctx.fill();

        // Line
        ctx.beginPath();
        ctx.moveTo(getX(0), getY(points[0].rate));
        for (let i = 1; i < points.length; i++) {
            ctx.lineTo(getX(i), getY(points[i].rate));
        }
        ctx.strokeStyle = '#a78bfa';
        ctx.lineWidth = 2.5;
        ctx.stroke();

        // X-axis labels
        ctx.fillStyle = '#7a6a96';
        ctx.font = '10px Plus Jakarta Sans, sans-serif';
        ctx.textAlign = 'center';
        const labelCount = Math.min(6, points.length);
        for (let i = 0; i < labelCount; i++) {
            const idx = Math.round((i / (labelCount - 1 || 1)) * (points.length - 1));
            const x = getX(idx);
            const date = points[idx].date;
            ctx.fillText(date.substring(5), x, height - 10);
        }

        // Title
        ctx.fillStyle = '#b8a8d4';
        ctx.font = '12px Plus Jakarta Sans, sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(`1 ${data.from} → ${data.to}`, padding.left, 14);
    },

    /* ─── Rates Page ─── */

    initRatesPage() {
        const searchInput = document.getElementById('rates-search');
        const baseSelect = document.getElementById('rates-base');
        const alphaNav = document.getElementById('alpha-nav');

        let currentLetter = '';

        const loadRates = this.debounce(() => {
            this.loadRatesData(baseSelect.value, searchInput.value, currentLetter);
        }, 300);

        searchInput?.addEventListener('input', loadRates);
        baseSelect?.addEventListener('change', () => {
            const flagEl = document.getElementById('rates-base-flag');
            if (flagEl) flagEl.src = Flags.getUrl(baseSelect.value, 40);
            loadRates();
        });

        alphaNav?.addEventListener('click', (e) => {
            const btn = e.target.closest('.alpha-btn');
            if (!btn) return;
            alphaNav.querySelectorAll('.alpha-btn').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            currentLetter = btn.dataset.letter || '';
            loadRates();
        });

        document.addEventListener('langchange', () => {
            if (this.lastRatesData) {
                this.renderRatesTable(this.lastRatesData);
            }
        });

        this.loadRatesData(baseSelect?.value || 'USD', '', '');
    },

    async loadRatesData(base, search, letter) {
        const loading = document.getElementById('rates-loading');
        const errorEl = document.getElementById('rates-error');
        const tbody = document.getElementById('rates-tbody');

        loading?.classList.remove('hidden');
        errorEl?.classList.add('hidden');

        try {
            let url = `${this.apiUrl('rates.php')}?base=${encodeURIComponent(base)}`;
            if (search) url += `&q=${encodeURIComponent(search)}`;
            if (letter) url += `&letter=${encodeURIComponent(letter)}`;

            const data = await this.fetchJson(url);
            this.lastRatesData = data;
            this.renderRatesTable(data);

            const countEl = document.getElementById('rates-count');
            const updatedEl = document.getElementById('rates-updated');
            if (countEl) countEl.textContent = I18n.t('rates.count', { count: data.count });
            if (updatedEl) updatedEl.textContent = I18n.t('rates.updated', { time: data.fetched_at });
        } catch (err) {
            if (tbody) tbody.innerHTML = '';
            errorEl.textContent = err.message || I18n.t('rates.error');
            errorEl.classList.remove('hidden');
        } finally {
            loading?.classList.add('hidden');
        }
    },

    renderRatesTable(data) {
        const tbody = document.getElementById('rates-tbody');
        if (!tbody) return;

        if (!data.rates?.length) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:2rem;">${I18n.t('rates.noResults')}</td></tr>`;
            return;
        }

        tbody.innerHTML = data.rates.map((r) => `
            <tr>
                <td class="code-cell">
                    <div class="code-with-flag">
                        ${Flags.imgHtml(r.code, 24)}
                        <span class="code-text">${r.code}</span>
                    </div>
                </td>
                <td>${I18n.currentLang === 'zh' ? r.currency_zh : r.currency_en}</td>
                <td>${I18n.currentLang === 'zh' ? r.country_zh : r.country_en}</td>
                <td class="rate-cell">${r.formatted_rate || r.rate}</td>
            </tr>
        `).join('');
    },
};

document.addEventListener('DOMContentLoaded', () => App.init());
