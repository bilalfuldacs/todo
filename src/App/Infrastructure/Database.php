<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Config\AppConfig;
use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $config = AppConfig::getInstance()->database;

            try {
                self::$connection = new PDO(
                    $config->dsn(),
                    $config->username,
                    $config->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new PDOException('Database connection failed.', (int) $e->getCode(), $e);
            }
        }

        return self::$connection;
    }
}
