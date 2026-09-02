<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Get a shared PDO database connection.
 */
function getDb(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            throw $e;
        }
        error_log('Database connection failed: ' . $e->getMessage());
        http_response_code(503);
        die(json_encode(['success' => false, 'error' => 'Service temporarily unavailable.']));
    }

    return $pdo;
}
