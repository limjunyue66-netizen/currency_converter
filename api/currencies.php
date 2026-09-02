<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/rates.php';

setApiHeaders();
checkRateLimit();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$search = isset($_GET['q']) ? sanitizeSearch((string) $_GET['q']) : null;
$letter = isset($_GET['letter']) ? strtoupper(trim((string) $_GET['letter'])) : null;

if ($letter !== null && $letter !== '' && !preg_match('/^[A-Z]$/', $letter)) {
    jsonResponse(['success' => false, 'error' => 'Invalid letter filter.'], 400);
}

try {
    $currencies = getAllCurrencies($search, $letter);
    jsonResponse([
        'success'    => true,
        'count'      => count($currencies),
        'currencies' => $currencies,
    ]);
} catch (Throwable $e) {
    logError('currencies.php: ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Unable to load currencies.'], 503);
}
