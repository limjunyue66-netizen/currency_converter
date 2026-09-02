<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/layout.php';

renderLayout('Currency Converter', 'index', function () {
    ?>
    <section class="hero">
        <h1 data-i18n="converter.title">Currency Converter</h1>
        <p class="hero-subtitle" data-i18n="converter.subtitle">Convert currencies with live exchange rates</p>
    </section>

    <div class="converter-grid">
        <div class="card converter-card">
            <form id="converter-form" class="converter-form" novalidate>
                <div class="form-group">
                    <label for="amount" data-i18n="converter.amount">Amount</label>
                    <input type="number" id="amount" name="amount" value="100" min="0" step="any"
                           placeholder="0.00" required autocomplete="off">
                </div>

                <div class="currency-row">
                    <div class="form-group currency-picker-group">
                        <label data-i18n="converter.from">From</label>
                        <div class="currency-picker" data-picker="from">
                            <input type="text" class="currency-search" placeholder="Search currency..." data-i18n-placeholder="converter.search">
                            <button type="button" class="currency-selected" data-code="USD">
                                <img class="flag-icon" src="https://flagcdn.com/w40/us.png" width="28" height="21" alt="USD">
                                <span class="currency-info">
                                    <span class="currency-code">USD</span>
                                    <span class="currency-name">US Dollar</span>
                                </span>
                            </button>
                            <div class="currency-dropdown hidden" role="listbox"></div>
                        </div>
                        <input type="hidden" id="from-currency" name="from" value="USD">
                    </div>

                    <button type="button" id="swap-btn" class="swap-btn" title="Swap currencies" aria-label="Swap currencies">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M7 16V4M7 4L3 8M7 4L11 8"/>
                            <path d="M17 8V20M17 20L21 16M17 20L13 16"/>
                        </svg>
                    </button>

                    <div class="form-group currency-picker-group">
                        <label data-i18n="converter.to">To</label>
                        <div class="currency-picker" data-picker="to">
                            <input type="text" class="currency-search" placeholder="Search currency..." data-i18n-placeholder="converter.search">
                            <button type="button" class="currency-selected" data-code="MYR">
                                <img class="flag-icon" src="https://flagcdn.com/w40/my.png" width="28" height="21" alt="MYR">
                                <span class="currency-info">
                                    <span class="currency-code">MYR</span>
                                    <span class="currency-name">Malaysian Ringgit</span>
                                </span>
                            </button>
                            <div class="currency-dropdown hidden" role="listbox"></div>
                        </div>
                        <input type="hidden" id="to-currency" name="to" value="MYR">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="convert-btn">
                    <span data-i18n="converter.convert">Convert</span>
                </button>
            </form>

            <div id="conversion-result" class="conversion-result hidden">
                <div class="result-flags" id="result-flags"></div>
                <div class="result-main" id="result-summary"></div>
                <div class="result-rate" id="result-rate"></div>
                <div class="result-meta">
                    <span id="result-updated"></span>
                    <span id="result-source"></span>
                </div>
            </div>

            <div id="converter-error" class="alert alert-error hidden" role="alert"></div>
            <div id="converter-warning" class="alert alert-warning hidden" role="status"></div>
        </div>

        <div class="card chart-card">
            <div class="chart-header">
                <h2 data-i18n="chart.title">Historical Rates</h2>
                <div class="period-tabs" role="tablist">
                    <button type="button" class="period-btn" data-period="7d">7D</button>
                    <button type="button" class="period-btn active" data-period="30d">30D</button>
                    <button type="button" class="period-btn" data-period="90d">90D</button>
                    <button type="button" class="period-btn" data-period="1y">1Y</button>
                    <button type="button" class="period-btn" data-period="5y">5Y</button>
                    <button type="button" class="period-btn" data-period="max">MAX</button>
                </div>
            </div>
            <div class="chart-wrapper">
                <canvas id="history-chart" aria-label="Historical exchange rate chart"></canvas>
                <div id="chart-loading" class="chart-loading hidden">
                    <div class="spinner"></div>
                </div>
                <div id="chart-error" class="chart-error hidden"></div>
            </div>
        </div>
    </div>
    <?php
});
