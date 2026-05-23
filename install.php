<?php

declare(strict_types=1);

/**
 * One-time setup: http://localhost/todo/install.php
 * Delete this file after successful setup.
 */

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

if (is_file(__DIR__ . '/.env')) {
    Dotenv::createImmutable(__DIR__)->safeLoad();
}

header('Content-Type: text/html; charset=utf-8');

function runStatement(PDO $pdo, string $statement): void
{
    $lines = preg_split('/\r\n|\n|\r/', trim($statement));
    $lines = array_filter($lines, static fn (string $line): bool => !preg_match('/^\s*--/', $line));
    $statement = trim(implode("\n", $lines));

    if ($statement !== '') {
        $pdo->exec($statement);
    }
}

try {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port = (int) ($_ENV['DB_PORT'] ?? 3306);
    $db = $_ENV['DB_DATABASE'] ?? 'todo';
    $user = $_ENV['DB_USERNAME'] ?? 'root';
    $pass = $_ENV['DB_PASSWORD'] ?? '';

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $db);
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $statements = preg_split('/;\s*[\r\n]+/', $sql);

    foreach ($statements as $statement) {
        runStatement($pdo, $statement);
    }

    echo '<h1>Todo API setup complete</h1>';
    echo '<p>Tables are ready. API: <a href="api/v1">http://localhost/todo/api/v1</a></p>';
    echo '<p><strong>Delete install.php</strong> after setup.</p>';
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Setup failed</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
