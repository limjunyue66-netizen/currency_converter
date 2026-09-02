<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/rates.php';

setApiHeaders();
checkRateLimit();

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$amount = $_REQUEST['amount'] ?? null;
$from = strtoupper(trim((string) ($_REQUEST['from'] ?? 'USD')));
$to = strtoupper(trim((string) ($_REQUEST['to'] ?? 'EUR')));

$parsedAmount = parseAmount($amount);
if ($parsedAmount === null) {
    jsonResponse(['success' => false, 'error' => 'Please enter a valid amount.'], 400);
}

if (!isValidCurrencyCode($from) || !isValidCurrencyCode($to)) {
    jsonResponse(['success' => false, 'error' => 'Invalid currency code.'], 400);
}

if (!currencyExists($from) || !currencyExists($to)) {
    jsonResponse(['success' => false, 'error' => 'Currency not supported.'], 400);
}

$result = convertCurrency($parsedAmount, $from, $to);

if (!$result['success']) {
    jsonResponse($result, 503);
}

jsonResponse([
    'success'    => true,
    'amount'     => $result['amount'],
    'from'       => $result['from'],
    'to'         => $result['to'],
    'rate'       => $result['rate'],
    'result'     => $result['result'],
    'formatted'  => [
        'amount'  => formatAmount($result['amount']),
        'result'  => formatAmount($result['result']),
        'rate'    => formatRate($result['rate']),
        'summary' => formatAmount($result['amount']) . ' ' . $result['from']
            . ' = ' . formatAmount($result['result']) . ' ' . $result['to'],
        'unit'    => '1 ' . $result['from'] . ' = ' . formatRate($result['rate']) . ' ' . $result['to'],
    ],
    'fetched_at' => $result['fetched_at'],
    'source'     => $result['source'],
    'cached'     => $result['cached'],
    'stale'      => $result['stale'] ?? false,
    'warning'    => $result['warning'] ?? null,
]);
