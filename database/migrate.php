<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Mrfiliperoberto\MangaCatalogApi\Database\Connection;

$databasePath = __DIR__ . '/catalog.sqlite';
$pdo = Connection::make($databasePath);

$pdo->exec(<<<SQL
    CREATE TABLE IF NOT EXISTS manga (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        author TEXT NOT NULL,
        genre TEXT NOT NULL,
        status TEXT NOT NULL CHECK (status IN ('ongoing', 'completed')),
        volumes INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL
    )
SQL);

$pdo->exec(<<<SQL
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL
    )
SQL);

echo "Migration completed. Database created at: {$databasePath}\n";