<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/rates.php';

setApiHeaders();
checkRateLimit();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$from = strtoupper(trim((string) ($_GET['from'] ?? 'USD')));
$to = strtoupper(trim((string) ($_GET['to'] ?? 'EUR')));
$period = strtolower(trim((string) ($_GET['period'] ?? '30d')));

$allowedPeriods = ['7d', '30d', '90d', '1y', '5y', 'max'];
if (!in_array($period, $allowedPeriods, true)) {
    jsonResponse(['success' => false, 'error' => 'Invalid period.'], 400);
}

if (!isValidCurrencyCode($from) || !isValidCurrencyCode($to)) {
    jsonResponse(['success' => false, 'error' => 'Invalid currency code.'], 400);
}

try {
    $result = getHistoricalRates($from, $to, $period);

    if (!$result['success']) {
        jsonResponse($result, 503);
    }

    jsonResponse($result);
} catch (Throwable $e) {
    logError('history.php: ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Historical data is temporarily unavailable. Please try again later.'], 503);
}
