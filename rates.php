<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/layout.php';

renderLayout('Exchange Rates', 'rates', function () {
    $letters = range('A', 'Z');
    ?>
    <section class="hero">
        <h1 data-i18n="rates.title">Exchange Rates Directory</h1>
        <p class="hero-subtitle" data-i18n="rates.subtitle">Browse all currencies alphabetically</p>
    </section>

    <div class="card rates-page-card">
        <div class="rates-toolbar">
            <div class="form-group rates-search-group">
                <label for="rates-search" data-i18n="rates.search">Search currencies</label>
                <input type="text" id="rates-search" placeholder="Search by code, name, or country..." data-i18n-placeholder="rates.searchPlaceholder">
            </div>
            <div class="form-group base-select-group">
                <label for="rates-base" data-i18n="rates.base">Base currency</label>
                <div class="base-select-wrap">
                    <img id="rates-base-flag" class="flag-icon" src="https://flagcdn.com/w40/us.png" width="24" height="18" alt="">
                    <select id="rates-base">
                        <option value="USD">USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                        <option value="MYR">MYR - Malaysian Ringgit</option>
                        <option value="CNY">CNY - Chinese Yuan</option>
                        <option value="JPY">JPY - Japanese Yen</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="alpha-nav" id="alpha-nav" role="navigation" aria-label="Alphabetical filter">
            <button type="button" class="alpha-btn active" data-letter="">All</button>
            <?php foreach ($letters as $letter): ?>
                <button type="button" class="alpha-btn" data-letter="<?= $letter ?>"><?= $letter ?></button>
            <?php endforeach; ?>
        </div>

        <div class="rates-meta">
            <span id="rates-count"></span>
            <span id="rates-updated"></span>
        </div>

        <div id="rates-loading" class="rates-loading hidden">
            <div class="spinner"></div>
            <span data-i18n="rates.loading">Loading rates...</span>
        </div>

        <div id="rates-error" class="alert alert-error hidden" role="alert"></div>

        <div class="rates-table-wrapper">
            <table class="rates-table" id="rates-table">
                <thead>
                    <tr>
                        <th data-i18n="rates.col.code">Code</th>
                        <th data-i18n="rates.col.currency">Currency</th>
                        <th data-i18n="rates.col.country">Country</th>
                        <th data-i18n="rates.col.rate">Rate</th>
                    </tr>
                </thead>
                <tbody id="rates-tbody"></tbody>
            </table>
        </div>
    </div>
    <?php
});
