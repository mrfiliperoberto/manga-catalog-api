<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi\Database;

use PDO;
use PDOException;

final class Connection
{
    private static ?PDO $instance = null;

    public static function make(string $databasePath): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        try {
            $pdo = new PDO('sqlite:' . $databasePath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA foreign_keys = ON;');
            } catch (PDOException $exception){
                throw new PDOException('Could not connect to database: ' . $exception->getMessage());
            }
        self::$instance = $pdo;

        return self::$instance;
    }
}    