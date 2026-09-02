<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;charset=%s', DB_HOST, DB_CHARSET),
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `' . DB_NAME . '`');

    $schema = file_get_contents(__DIR__ . '/includes/schema.sql');

    // Execute CREATE TABLE statements
    foreach (['currencies', 'rate_cache'] as $table) {
        if (preg_match('/CREATE TABLE IF NOT EXISTS ' . $table . '.*?ENGINE=InnoDB/s', $schema, $match)) {
            $pdo->exec($match[0]);
            echo "Table '$table' ready.\n";
        }
    }

    // Execute INSERT seed data
    if (preg_match('/INSERT INTO currencies.*?ON DUPLICATE KEY UPDATE.*?symbol = VALUES\(symbol\);/s', $schema, $match)) {
        $pdo->exec($match[0]);
        echo "Currency data seeded.\n";
    }

    $count = $pdo->query('SELECT COUNT(*) FROM `' . DB_NAME . '`.currencies')->fetchColumn();
    echo "Database setup complete. Currencies: {$count}\n";
} catch (PDOException $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}
