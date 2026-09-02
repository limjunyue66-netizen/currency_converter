<?php
declare(strict_types=1);

/**
 * Database configuration example.
 *
 * Copy this file to config.local.php (recommended) or use these values
 * when setting up your local / hosting environment.
 *
 *   cp includes/database.example.php includes/config.local.php
 *
 * Then edit config.local.php with your real credentials.
 * Never commit config.local.php to version control.
 */

// Database connection
define('DB_HOST', 'localhost');
define('DB_NAME', 'currency_converter');
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');
define('DB_CHARSET', 'utf8mb4');

// Application URL path — auto-detected by default.
// Only set if installed in a subfolder (e.g. '/currency'):
// define('APP_URL', '/currency');

define('FORCE_HTTPS', false);
define('DEBUG_MODE', false);
