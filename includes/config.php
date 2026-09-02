<?php
declare(strict_types=1);

/**
 * Application configuration.
 * Copy config.local.example.php to config.local.php for local overrides.
 */

if (is_file(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

/**
 * Auto-detect URL base path from the current script location.
 * Works on XAMPP subfolders and cPanel public_html root.
 */
function detectAppUrl(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $dir = str_replace('\\', '/', dirname($script));

    if ($dir === '/' || $dir === '.' || $dir === '') {
        return '';
    }

    return rtrim($dir, '/');
}

if (!defined('APP_NAME')) define('APP_NAME', 'Currency Converter');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');
if (!defined('APP_BASE_PATH')) define('APP_BASE_PATH', dirname(__DIR__));
if (!defined('APP_URL')) define('APP_URL', detectAppUrl());

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'currency_converter');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

if (!defined('API_PRIMARY')) define('API_PRIMARY', 'https://open.er-api.com/v6/latest/');
if (!defined('API_FALLBACK')) define('API_FALLBACK', 'https://api.frankfurter.app/latest?from=');
if (!defined('API_HISTORY')) define('API_HISTORY', 'https://api.frankfurter.app/');

if (!defined('CACHE_TTL')) define('CACHE_TTL', 3600);
if (!defined('RATE_LIMIT_MAX')) define('RATE_LIMIT_MAX', 120);
if (!defined('RATE_LIMIT_WINDOW')) define('RATE_LIMIT_WINDOW', 60);
if (!defined('DEFAULT_BASE')) define('DEFAULT_BASE', 'USD');
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);
if (!defined('FORCE_HTTPS')) define('FORCE_HTTPS', false);

date_default_timezone_set('UTC');

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

if (FORCE_HTTPS && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
    $redirect = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . $redirect, true, 301);
    exit;
}
