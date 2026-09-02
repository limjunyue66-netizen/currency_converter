<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Send a JSON response and exit.
 */
function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Escape HTML output.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Get client IP address.
 */
function getClientIp(): string
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', (string) $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * Basic API rate limiting using a temp file store.
 */
function checkRateLimit(): void
{
    $ip = getClientIp();
    $dir = sys_get_temp_dir() . '/cc_rate_limit';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }

    $file = $dir . '/' . hash('sha256', $ip) . '.json';
    $now = time();
    $data = ['count' => 0, 'window_start' => $now];

    if (is_file($file)) {
        $raw = @file_get_contents($file);
        $decoded = $raw ? json_decode($raw, true) : null;
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }

    if (($now - (int) $data['window_start']) >= RATE_LIMIT_WINDOW) {
        $data = ['count' => 0, 'window_start' => $now];
    }

    $data['count'] = (int) $data['count'] + 1;
    @file_put_contents($file, json_encode($data), LOCK_EX);

    if ($data['count'] > RATE_LIMIT_MAX) {
        jsonResponse([
            'success' => false,
            'error'   => 'Too many requests. Please try again later.',
        ], 429);
    }
}

/**
 * Validate a 3-letter currency code.
 */
function isValidCurrencyCode(string $code): bool
{
    return (bool) preg_match('/^[A-Z]{3}$/', $code);
}

/**
 * Validate a positive numeric amount.
 */
function parseAmount(mixed $value): ?float
{
    if ($value === null || $value === '') {
        return null;
    }
    if (!is_numeric($value)) {
        return null;
    }
    $amount = (float) $value;
    if ($amount < 0 || $amount > 999999999999) {
        return null;
    }
    return $amount;
}

/**
 * Perform an HTTP GET request with timeout.
 */
function httpGet(string $url): ?array
{
    $context = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => 15,
            'header'  => "Accept: application/json\r\nUser-Agent: CurrencyConverter/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer'      => true,
            'verify_peer_name' => true,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return null;
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : null;
}

/**
 * Format a rate for display.
 */
function formatRate(float $rate, int $decimals = 4): string
{
    if ($rate >= 1000) {
        return number_format($rate, 2, '.', ',');
    }
    if ($rate >= 1) {
        return number_format($rate, 4, '.', ',');
    }
    return number_format($rate, 6, '.', ',');
}

/**
 * Format amount for display.
 */
function formatAmount(float $amount, int $decimals = 2): string
{
    return number_format($amount, $decimals, '.', ',');
}

/**
 * Get allowed CORS origin for API (same origin only by default).
 */
function setApiHeaders(): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
}

/**
 * Sanitize search query.
 */
function sanitizeSearch(string $query): string
{
    $query = trim($query);
    $query = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $query) ?? '';
    return mb_substr($query, 0, 100);
}

/**
 * Log internal errors without exposing to users.
 */
function logError(string $message): void
{
    error_log('[CurrencyConverter] ' . $message);
}
