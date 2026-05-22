<?php

declare(strict_types=1);

namespace App;

use App\Config\AppConfig;
use App\Http\ExceptionHandler;
use Dotenv\Dotenv;

final class Bootstrap
{
    private static bool $booted = false;

    public static function init(): AppConfig
    {
        if (self::$booted) {
            return AppConfig::getInstance();
        }

        $root = dirname(__DIR__, 2);

        if (is_file($root . '/vendor/autoload.php')) {
            require_once $root . '/vendor/autoload.php';
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Run composer install in the project root first.',
            ]);
            exit;
        }

        if (is_file($root . '/.env')) {
            Dotenv::createImmutable($root)->safeLoad();
        }

        self::$booted = true;

        return AppConfig::getInstance();
    }
}
