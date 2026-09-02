<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/layout.php';

renderLayout('User Manual', 'manual', function () {
    ?>
    <section class="hero">
        <h1 data-i18n="manual.title">User Manual</h1>
        <p class="hero-subtitle" data-i18n="manual.subtitle">How to use the Currency Converter</p>
    </section>

    <div class="manual-grid">
        <div class="card manual-card">
            <h2 data-i18n="manual.converter.title">Currency Converter</h2>
            <ol class="manual-list">
                <li data-i18n="manual.converter.step1">Enter the amount you want to convert.</li>
                <li data-i18n="manual.converter.step2">Select the source currency (From) using the searchable picker.</li>
                <li data-i18n="manual.converter.step3">Select the target currency (To).</li>
                <li data-i18n="manual.converter.step4">Click the swap button to quickly reverse currencies.</li>
                <li data-i18n="manual.converter.step5">Press Convert to see the result instantly without page reload.</li>
            </ol>
        </div>

        <div class="card manual-card">
            <h2 data-i18n="manual.chart.title">Historical Chart</h2>
            <p data-i18n="manual.chart.desc">View exchange rate trends over different time periods: 7 days, 30 days, 90 days, 1 year, 5 years, or maximum available history.</p>
        </div>

        <div class="card manual-card">
            <h2 data-i18n="manual.rates.title">Exchange Rates Directory</h2>
            <p data-i18n="manual.rates.desc">Visit the Rates page to browse all supported currencies alphabetically. Use the A-Z filter or search by code, currency name, or country.</p>
        </div>

        <div class="card manual-card">
            <h2 data-i18n="manual.language.title">Language</h2>
            <p data-i18n="manual.language.desc">Switch between English and Chinese using the language buttons in the header. Your preference is saved automatically.</p>
        </div>

        <div class="card manual-card">
            <h2 data-i18n="manual.api.title">Data Sources</h2>
            <p data-i18n="manual.api.desc">Exchange rates are fetched from live APIs and cached for one hour. If the primary API is unavailable, a fallback source is used. Cached rates may be shown when live data is temporarily unavailable.</p>
        </div>

        <div class="card manual-card">
            <h2 data-i18n="manual.tips.title">Tips</h2>
            <ul class="manual-list">
                <li data-i18n="manual.tips.tip1">Rates are for informational purposes only.</li>
                <li data-i18n="manual.tips.tip2">Check the "Last updated" time for rate freshness.</li>
                <li data-i18n="manual.tips.tip3">Search currencies by typing code, name, or country.</li>
            </ul>
        </div>
    </div>
    <?php
});
