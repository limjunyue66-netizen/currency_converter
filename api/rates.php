<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/rates.php';

setApiHeaders();
checkRateLimit();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$base = strtoupper(trim((string) ($_GET['base'] ?? DEFAULT_BASE)));
$search = isset($_GET['q']) ? sanitizeSearch((string) $_GET['q']) : null;
$letter = isset($_GET['letter']) ? strtoupper(trim((string) $_GET['letter'])) : null;

if (!isValidCurrencyCode($base)) {
    jsonResponse(['success' => false, 'error' => 'Invalid base currency code.'], 400);
}

if ($letter !== null && $letter !== '' && !preg_match('/^[A-Z]$/', $letter)) {
    jsonResponse(['success' => false, 'error' => 'Invalid letter filter.'], 400);
}

try {
    $result = getAllRatesForBase($base);

    if (!$result['success']) {
        jsonResponse($result, 503);
    }

    $rates = $result['rates'];

    if ($search !== null && $search !== '') {
        $searchLower = mb_strtolower($search);
        $rates = array_values(array_filter($rates, function (array $item) use ($searchLower) {
            return str_contains(mb_strtolower($item['code']), $searchLower)
                || str_contains(mb_strtolower($item['currency_en']), $searchLower)
                || str_contains(mb_strtolower($item['currency_zh']), $searchLower)
                || str_contains(mb_strtolower($item['country_en']), $searchLower)
                || str_contains(mb_strtolower($item['country_zh']), $searchLower);
        }));
    }

    if ($letter !== null && $letter !== '') {
        $rates = array_values(array_filter($rates, fn(array $item) => str_starts_with($item['code'], $letter)));
    }

    $formatted = array_map(function (array $item) use ($base) {
        return array_merge($item, [
            'formatted_rate' => formatRate((float) $item['rate']),
            'display'        => '1 ' . $base . ' = ' . formatRate((float) $item['rate']) . ' ' . $item['code'],
        ]);
    }, $rates);

    jsonResponse([
        'success'    => true,
        'base'       => $result['base'],
        'count'      => count($formatted),
        'rates'      => $formatted,
        'fetched_at' => $result['fetched_at'],
        'source'     => $result['source'],
        'cached'     => $result['cached'],
        'warning'    => $result['warning'] ?? null,
    ]);
} catch (Throwable $e) {
    logError('rates.php: ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Unable to load exchange rates.'], 503);
}
