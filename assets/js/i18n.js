/**
 * Internationalization (i18n) for Currency Converter
 * Supports English and Chinese
 */

const translations = {
    en: {
        'app.name': 'Currency Converter',
        'nav.converter': 'Converter',
        'nav.rates': 'Rates',
        'nav.manual': 'Manual',
        'footer.rights': 'All rights reserved.',
        'footer.disclaimer': 'Exchange rates are for informational purposes only.',

        'converter.title': 'Currency Converter',
        'converter.subtitle': 'Convert currencies with live exchange rates',
        'converter.amount': 'Amount',
        'converter.from': 'From',
        'converter.to': 'To',
        'converter.search': 'Search currency...',
        'converter.convert': 'Convert',
        'converter.lastUpdated': 'Last updated',
        'converter.source': 'Source',
        'converter.converting': 'Converting...',
        'converter.noResults': 'No currencies found',

        'chart.title': 'Historical Rates',
        'chart.loading': 'Loading chart...',
        'chart.noData': 'No historical data available',
        'chart.error': 'Unable to load chart data',

        'rates.title': 'Exchange Rates Directory',
        'rates.subtitle': 'Browse all currencies alphabetically',
        'rates.search': 'Search currencies',
        'rates.searchPlaceholder': 'Search by code, name, or country...',
        'rates.base': 'Base currency',
        'rates.loading': 'Loading rates...',
        'rates.count': '{count} currencies',
        'rates.updated': 'Last updated: {time}',
        'rates.col.code': 'Code',
        'rates.col.currency': 'Currency',
        'rates.col.country': 'Country',
        'rates.col.rate': 'Rate',
        'rates.noResults': 'No currencies found',
        'rates.error': 'Unable to load exchange rates',

        'manual.title': 'User Manual',
        'manual.subtitle': 'How to use the Currency Converter',
        'manual.converter.title': 'Currency Converter',
        'manual.converter.step1': 'Enter the amount you want to convert.',
        'manual.converter.step2': 'Select the source currency (From) using the searchable picker.',
        'manual.converter.step3': 'Select the target currency (To).',
        'manual.converter.step4': 'Click the swap button to quickly reverse currencies.',
        'manual.converter.step5': 'Press Convert to see the result instantly without page reload.',
        'manual.chart.title': 'Historical Chart',
        'manual.chart.desc': 'View exchange rate trends over different time periods: 7 days, 30 days, 90 days, 1 year, 5 years, or maximum available history.',
        'manual.rates.title': 'Exchange Rates Directory',
        'manual.rates.desc': 'Visit the Rates page to browse all supported currencies alphabetically. Use the A-Z filter or search by code, currency name, or country.',
        'manual.language.title': 'Language',
        'manual.language.desc': 'Switch between English and Chinese using the language buttons in the header. Your preference is saved automatically.',
        'manual.api.title': 'Data Sources',
        'manual.api.desc': 'Exchange rates are fetched from live APIs and cached for one hour. If the primary API is unavailable, a fallback source is used. Cached rates may be shown when live data is temporarily unavailable.',
        'manual.tips.title': 'Tips',
        'manual.tips.tip1': 'Rates are for informational purposes only.',
        'manual.tips.tip2': 'Check the "Last updated" time for rate freshness.',
        'manual.tips.tip3': 'Search currencies by typing code, name, or country.',

        'error.generic': 'Something went wrong. Please try again.',
        'error.network': 'Network error. Please check your connection.',
        'error.rateUnavailable': 'Live exchange rate is temporarily unavailable. Please try again later.',
    },
    zh: {
        'app.name': '货币转换器',
        'nav.converter': '转换',
        'nav.rates': '汇率',
        'nav.manual': '手册',
        'footer.rights': '版权所有。',
        'footer.disclaimer': '汇率仅供参考。',

        'converter.title': '货币转换器',
        'converter.subtitle': '使用实时汇率转换货币',
        'converter.amount': '金额',
        'converter.from': '从',
        'converter.to': '到',
        'converter.search': '搜索货币...',
        'converter.convert': '转换',
        'converter.lastUpdated': '最后更新',
        'converter.source': '来源',
        'converter.converting': '转换中...',
        'converter.noResults': '未找到货币',

        'chart.title': '历史汇率',
        'chart.loading': '加载图表中...',
        'chart.noData': '暂无历史数据',
        'chart.error': '无法加载图表数据',

        'rates.title': '汇率目录',
        'rates.subtitle': '按字母顺序浏览所有货币',
        'rates.search': '搜索货币',
        'rates.searchPlaceholder': '按代码、名称或国家搜索...',
        'rates.base': '基准货币',
        'rates.loading': '加载汇率中...',
        'rates.count': '{count} 种货币',
        'rates.updated': '最后更新：{time}',
        'rates.col.code': '代码',
        'rates.col.currency': '货币',
        'rates.col.country': '国家',
        'rates.col.rate': '汇率',
        'rates.noResults': '未找到货币',
        'rates.error': '无法加载汇率',

        'manual.title': '用户手册',
        'manual.subtitle': '如何使用货币转换器',
        'manual.converter.title': '货币转换器',
        'manual.converter.step1': '输入您要转换的金额。',
        'manual.converter.step2': '使用可搜索的选择器选择源货币（从）。',
        'manual.converter.step3': '选择目标货币（到）。',
        'manual.converter.step4': '点击交换按钮快速反转货币。',
        'manual.converter.step5': '按转换按钮即可即时查看结果，无需刷新页面。',
        'manual.chart.title': '历史图表',
        'manual.chart.desc': '查看不同时间段内的汇率趋势：7天、30天、90天、1年、5年或最长可用历史。',
        'manual.rates.title': '汇率目录',
        'manual.rates.desc': '访问汇率页面按字母顺序浏览所有支持的货币。使用A-Z筛选器或按代码、货币名称或国家搜索。',
        'manual.language.title': '语言',
        'manual.language.desc': '使用页眉中的语言按钮在英文和中文之间切换。您的偏好会自动保存。',
        'manual.api.title': '数据来源',
        'manual.api.desc': '汇率从实时API获取并缓存一小时。如果主API不可用，将使用备用来源。当实时数据暂时不可用时，可能会显示缓存汇率。',
        'manual.tips.title': '提示',
        'manual.tips.tip1': '汇率仅供参考。',
        'manual.tips.tip2': '查看"最后更新"时间以了解汇率新鲜度。',
        'manual.tips.tip3': '通过输入代码、名称或国家来搜索货币。',

        'error.generic': '出现错误，请重试。',
        'error.network': '网络错误，请检查您的连接。',
        'error.rateUnavailable': '实时汇率暂时不可用，请稍后再试。',
    },
};

const I18n = {
    currentLang: 'en',

    init() {
        const saved = localStorage.getItem('cc_lang');
        if (saved && translations[saved]) {
            this.currentLang = saved;
        }
        this.apply();
        this.bindLangSwitcher();
    },

    t(key, params = {}) {
        const lang = translations[this.currentLang] || translations.en;
        let text = lang[key] || translations.en[key] || key;
        Object.keys(params).forEach((k) => {
            text = text.replace(`{${k}}`, params[k]);
        });
        return text;
    },

    setLang(lang) {
        if (!translations[lang]) return;
        this.currentLang = lang;
        localStorage.setItem('cc_lang', lang);
        document.documentElement.lang = lang === 'zh' ? 'zh-CN' : 'en';
        this.apply();
        document.dispatchEvent(new CustomEvent('langchange', { detail: { lang } }));
    },

    apply() {
        document.querySelectorAll('[data-i18n]').forEach((el) => {
            const key = el.getAttribute('data-i18n');
            el.textContent = this.t(key);
        });

        document.querySelectorAll('[data-i18n-placeholder]').forEach((el) => {
            const key = el.getAttribute('data-i18n-placeholder');
            el.placeholder = this.t(key);
        });

        document.querySelectorAll('.lang-btn').forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.lang === this.currentLang);
        });
    },

    bindLangSwitcher() {
        document.querySelectorAll('.lang-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                this.setLang(btn.dataset.lang);
            });
        });
    },

    getCurrencyName(currency) {
        if (!currency) return '';
        return this.currentLang === 'zh' ? currency.currency_zh : currency.currency_en;
    },

    getCountryName(currency) {
        if (!currency) return '';
        return this.currentLang === 'zh' ? currency.country_zh : currency.country_en;
    },
};

document.addEventListener('DOMContentLoaded', () => I18n.init());
