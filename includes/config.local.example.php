<?php
declare(strict_types=1);

/**
 * Local configuration overrides.
 *
 * Setup:
 *   1. Copy database.example.php OR this file to config.local.php
 *   2. Update your database credentials below
 *   3. Never commit config.local.php to git
 */

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'currency_converter');
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');
define('DB_CHARSET', 'utf8mb4');

// APP_URL is auto-detected. Only uncomment if you install in a subfolder:
// define('APP_URL', '/your-subfolder');

define('FORCE_HTTPS', false);
define('DEBUG_MODE', false);
