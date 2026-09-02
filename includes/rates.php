<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

/**
 * Fetch all currencies from database.
 */
function getAllCurrencies(?string $search = null, ?string $letter = null): array
{
    $pdo = getDb();
    $sql = 'SELECT id, code, country_en, country_zh, currency_en, currency_zh, symbol
            FROM currencies WHERE 1=1';
    $params = [];

    if ($search !== null && $search !== '') {
        $sql .= ' AND (code LIKE :search OR currency_en LIKE :search OR currency_zh LIKE :search
                 OR country_en LIKE :search OR country_zh LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    if ($letter !== null && $letter !== '' && preg_match('/^[A-Z]$/', $letter)) {
        $sql .= ' AND code LIKE :letter';
        $params['letter'] = $letter . '%';
    }

    $sql .= ' ORDER BY code ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Check if currency code exists in database.
 */
function currencyExists(string $code): bool
{
    $pdo = getDb();
    $stmt = $pdo->prepare('SELECT 1 FROM currencies WHERE code = :code LIMIT 1');
    $stmt->execute(['code' => $code]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Get cached rates for a base currency if still fresh.
 */
function getCachedRates(string $base): ?array
{
    $pdo = getDb();
    $cutoff = date('Y-m-d H:i:s', time() - CACHE_TTL);
    $stmt = $pdo->prepare(
        'SELECT target_currency, rate, source, fetched_at
         FROM rate_cache
         WHERE base_currency = :base
           AND fetched_at >= :cutoff
         ORDER BY target_currency ASC'
    );
    $stmt->execute(['base' => $base, 'cutoff' => $cutoff]);

    $rows = $stmt->fetchAll();
    if (empty($rows)) {
        return null;
    }

    $rates = [];
    $source = $rows[0]['source'];
    $fetchedAt = $rows[0]['fetched_at'];

    foreach ($rows as $row) {
        $rates[$row['target_currency']] = (float) $row['rate'];
    }

    return [
        'base'       => $base,
        'rates'      => $rates,
        'source'     => $source,
        'fetched_at' => $fetchedAt,
        'cached'     => true,
    ];
}

/**
 * Get stale cached rates (any age) as last resort.
 */
function getStaleCachedRates(string $base): ?array
{
    $pdo = getDb();
    $stmt = $pdo->prepare(
        'SELECT target_currency, rate, source, fetched_at
         FROM rate_cache
         WHERE base_currency = :base
         ORDER BY fetched_at DESC, target_currency ASC'
    );
    $stmt->execute(['base' => $base]);
    $rows = $stmt->fetchAll();

    if (empty($rows)) {
        return null;
    }

    $rates = [];
    $source = $rows[0]['source'];
    $fetchedAt = $rows[0]['fetched_at'];

    foreach ($rows as $row) {
        if (!isset($rates[$row['target_currency']])) {
            $rates[$row['target_currency']] = (float) $row['rate'];
        }
    }

    return [
        'base'       => $base,
        'rates'      => $rates,
        'source'     => $source,
        'fetched_at' => $fetchedAt,
        'cached'     => true,
        'stale'      => true,
    ];
}

/**
 * Save rates to cache.
 */
function saveRatesToCache(string $base, array $rates, string $source): void
{
    $pdo = getDb();
    $pdo->beginTransaction();

    try {
        $delete = $pdo->prepare('DELETE FROM rate_cache WHERE base_currency = :base');
        $delete->execute(['base' => $base]);

        $insert = $pdo->prepare(
            'INSERT INTO rate_cache (base_currency, target_currency, rate, source, fetched_at)
             VALUES (:base, :target, :rate, :source, NOW())'
        );

        foreach ($rates as $target => $rate) {
            if (!is_string($target) || !is_numeric($rate)) {
                continue;
            }
            $insert->execute([
                'base'   => $base,
                'target' => strtoupper($target),
                'rate'   => (float) $rate,
                'source' => $source,
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        logError('Failed to save rate cache: ' . $e->getMessage());
    }
}

/**
 * Fetch live rates from primary API (open.er-api.com).
 */
function fetchFromPrimaryApi(string $base): ?array
{
    $url = API_PRIMARY . urlencode($base);
    $data = httpGet($url);

    if (!$data || ($data['result'] ?? '') !== 'success' || empty($data['rates'])) {
        return null;
    }

    return [
        'base'       => strtoupper($data['base_code'] ?? $base),
        'rates'      => $data['rates'],
        'source'     => 'open.er-api.com',
        'fetched_at' => date('Y-m-d H:i:s', (int) ($data['time_last_update_unix'] ?? time())),
        'cached'     => false,
    ];
}

/**
 * Fetch live rates from fallback API (frankfurter.app).
 */
function fetchFromFallbackApi(string $base): ?array
{
    $url = API_FALLBACK . urlencode($base);
    $data = httpGet($url);

    if (!$data || empty($data['rates'])) {
        return null;
    }

    $rates = $data['rates'];
    $rates[$base] = 1.0;

    return [
        'base'       => strtoupper($data['base'] ?? $base),
        'rates'      => $rates,
        'source'     => 'frankfurter.app',
        'fetched_at' => date('Y-m-d H:i:s'),
        'cached'     => false,
    ];
}

/**
 * Get exchange rates with caching and fallback chain.
 */
function getExchangeRates(string $base): array
{
    $base = strtoupper($base);

    if (!currencyExists($base)) {
        return ['success' => false, 'error' => 'Invalid currency code.'];
    }

    $cached = getCachedRates($base);
    if ($cached !== null) {
        return ['success' => true, 'data' => $cached];
    }

    $live = fetchFromPrimaryApi($base);
    if ($live === null) {
        $live = fetchFromFallbackApi($base);
    }

    if ($live !== null) {
        saveRatesToCache($base, $live['rates'], $live['source']);
        return ['success' => true, 'data' => $live];
    }

    $stale = getStaleCachedRates($base);
    if ($stale !== null) {
        return ['success' => true, 'data' => $stale, 'warning' => 'Using cached rates. Live data temporarily unavailable.'];
    }

    return [
        'success' => false,
        'error'   => 'Live exchange rate is temporarily unavailable. Please try again later.',
    ];
}

/**
 * Get exchange rates for a bridge currency (USD has the most complete data).
 */
function getBridgeRates(string $bridge = 'USD'): array
{
    $bridge = strtoupper($bridge);
    return getExchangeRates($bridge);
}

/**
 * Calculate cross-rate between two currencies using a bridge (e.g. USD).
 * API rates are quoted as: 1 BRIDGE = X TARGET
 */
function calculateCrossRate(string $from, string $to, array $bridgeRates, string $bridge = 'USD'): ?float
{
    $from = strtoupper($from);
    $to = strtoupper($to);
    $bridge = strtoupper($bridge);

    if ($from === $to) {
        return 1.0;
    }

    if ($from === $bridge) {
        return isset($bridgeRates[$to]) ? (float) $bridgeRates[$to] : null;
    }

    if ($to === $bridge) {
        return isset($bridgeRates[$from]) && (float) $bridgeRates[$from] !== 0.0
            ? 1 / (float) $bridgeRates[$from]
            : null;
    }

    if (!isset($bridgeRates[$from], $bridgeRates[$to])) {
        return null;
    }

    $fromRate = (float) $bridgeRates[$from];
    $toRate = (float) $bridgeRates[$to];

    if ($fromRate === 0.0) {
        return null;
    }

    return $toRate / $fromRate;
}

/**
 * Resolve a rate via bridge currencies (USD, EUR, GBP).
 */
function getRateViaBridge(string $from, string $to): ?array
{
    $bridges = ['USD', 'EUR', 'GBP'];

    foreach ($bridges as $bridge) {
        $result = getBridgeRates($bridge);
        if (!$result['success']) {
            continue;
        }

        $rates = $result['data']['rates'];
        $rates[$bridge] = 1.0;

        $rate = calculateCrossRate($from, $to, $rates, $bridge);
        if ($rate !== null) {
            return [
                'success' => true,
                'rate'    => $rate,
                'data'    => $result['data'],
                'warning' => $result['warning'] ?? null,
                'method'  => 'cross-' . strtolower($bridge),
            ];
        }
    }

    return null;
}

/**
 * Get a single exchange rate between two currencies.
 */
function getRate(string $from, string $to): array
{
    $from = strtoupper($from);
    $to = strtoupper($to);

    if ($from === $to) {
        return [
            'success' => true,
            'rate'    => 1.0,
            'data'    => [
                'base'       => $from,
                'rates'      => [$to => 1.0],
                'source'     => 'internal',
                'fetched_at' => date('Y-m-d H:i:s'),
                'cached'     => true,
            ],
        ];
    }

    if (!currencyExists($from) || !currencyExists($to)) {
        return ['success' => false, 'error' => 'Invalid currency code.'];
    }

    // 1. Direct rate from source currency base
    $result = getExchangeRates($from);
    if ($result['success'] && isset($result['data']['rates'][$to])) {
        return [
            'success' => true,
            'rate'    => (float) $result['data']['rates'][$to],
            'data'    => $result['data'],
            'warning' => $result['warning'] ?? null,
            'method'  => 'direct',
        ];
    }

    // 2. Inverse rate from target currency base
    $inverse = getExchangeRates($to);
    if ($inverse['success'] && isset($inverse['data']['rates'][$from])) {
        $fromRate = (float) $inverse['data']['rates'][$from];
        if ($fromRate !== 0.0) {
            return [
                'success' => true,
                'rate'    => 1 / $fromRate,
                'data'    => $inverse['data'],
                'warning' => $result['warning'] ?? $inverse['warning'] ?? null,
                'method'  => 'inverse',
            ];
        }
    }

    // 3. Cross-rate via USD / EUR / GBP bridge (covers all currency pairs)
    $bridged = getRateViaBridge($from, $to);
    if ($bridged !== null) {
        return $bridged;
    }

    return ['success' => false, 'error' => 'Exchange rate not available for this currency pair.'];
}

/**
 * Convert amount between currencies.
 */
function convertCurrency(float $amount, string $from, string $to): array
{
    $rateResult = getRate($from, $to);
    if (!$rateResult['success']) {
        return $rateResult;
    }

    $rate = $rateResult['rate'];
    $converted = $amount * $rate;

    return [
        'success'    => true,
        'amount'     => $amount,
        'from'       => strtoupper($from),
        'to'         => strtoupper($to),
        'rate'       => $rate,
        'result'     => $converted,
        'fetched_at' => $rateResult['data']['fetched_at'],
        'source'     => $rateResult['data']['source'],
        'cached'     => $rateResult['data']['cached'] ?? false,
        'stale'      => $rateResult['data']['stale'] ?? false,
        'warning'    => $rateResult['warning'] ?? null,
    ];
}

/**
 * Fetch historical rates from frankfurter.app.
 */
function getHistoricalRates(string $from, string $to, string $period): array
{
    $from = strtoupper($from);
    $to = strtoupper($to);

    if (!currencyExists($from) || !currencyExists($to)) {
        return ['success' => false, 'error' => 'Invalid currency code.'];
    }

    $end = new DateTimeImmutable('today');
    $start = match ($period) {
        '7d'    => $end->modify('-7 days'),
        '30d'   => $end->modify('-30 days'),
        '90d'   => $end->modify('-90 days'),
        '1y'    => $end->modify('-1 year'),
        '5y'    => $end->modify('-5 years'),
        'max'   => $end->modify('-20 years'),
        default => $end->modify('-30 days'),
    };

    $startStr = $start->format('Y-m-d');
    $endStr = $end->format('Y-m-d');

    $url = API_HISTORY . $startStr . '..' . $endStr
        . '?from=' . urlencode($from) . '&to=' . urlencode($to);

    $data = httpGet($url);

    if (!$data || empty($data['rates'])) {
        $singleUrl = API_HISTORY . $endStr . '?from=' . urlencode($from) . '&to=' . urlencode($to);
        $single = httpGet($singleUrl);
        if ($single && isset($single['rates'][$to])) {
            return [
                'success' => true,
                'from'    => $from,
                'to'      => $to,
                'period'  => $period,
                'points'  => [
                    ['date' => $endStr, 'rate' => (float) $single['rates'][$to]],
                ],
                'warning' => 'Limited historical data available.',
            ];
        }
        return [
            'success' => false,
            'error'   => 'Historical data is temporarily unavailable. Please try again later.',
        ];
    }

    $points = [];
    foreach ($data['rates'] as $date => $rates) {
        if (isset($rates[$to])) {
            $points[] = ['date' => $date, 'rate' => (float) $rates[$to]];
        }
    }

    usort($points, fn($a, $b) => strcmp($a['date'], $b['date']));

    return [
        'success' => true,
        'from'    => $from,
        'to'      => $to,
        'period'  => $period,
        'points'  => $points,
    ];
}

/**
 * Get rates for all currencies relative to a base.
 */
function getAllRatesForBase(string $base): array
{
    $base = strtoupper($base);

    if (!currencyExists($base)) {
        return ['success' => false, 'error' => 'Invalid currency code.'];
    }

    $pdo = getDb();
    $stmt = $pdo->query('SELECT code, currency_en, currency_zh, country_en, country_zh, symbol FROM currencies ORDER BY code');
    $currencies = $stmt->fetchAll();

    $result = getExchangeRates($base);
    $directRates = $result['success'] ? $result['data']['rates'] : [];
    $meta = $result['success'] ? $result['data'] : null;

    $list = [];
    $warnings = [];

    foreach ($currencies as $currency) {
        $code = $currency['code'];

        if ($code === $base) {
            $list[] = array_merge($currency, ['rate' => 1.0]);
            continue;
        }

        if (isset($directRates[$code])) {
            $list[] = array_merge($currency, ['rate' => (float) $directRates[$code]]);
            continue;
        }

        $rateResult = getRate($base, $code);
        if ($rateResult['success']) {
            $list[] = array_merge($currency, ['rate' => (float) $rateResult['rate']]);
            if (!empty($rateResult['warning'])) {
                $warnings[] = $rateResult['warning'];
            }
            if ($meta === null && isset($rateResult['data'])) {
                $meta = $rateResult['data'];
            }
            continue;
        }
    }

    if (empty($list)) {
        return [
            'success' => false,
            'error'   => 'Live exchange rate is temporarily unavailable. Please try again later.',
        ];
    }

    return [
        'success'    => true,
        'base'       => $base,
        'rates'      => $list,
        'fetched_at' => $meta['fetched_at'] ?? date('Y-m-d H:i:s'),
        'source'     => $meta['source'] ?? 'cross-rate',
        'cached'     => $meta['cached'] ?? false,
        'warning'    => $warnings[0] ?? ($result['warning'] ?? null),
    ];
}
