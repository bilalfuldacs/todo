<?php

declare(strict_types=1);

namespace App\Http;

use App\Config\AppConfig;
use App\Exceptions\HttpException;
use PDOException;
use Throwable;

final class ExceptionHandler
{
    public static function handle(Throwable $e): void
    {
        $config = AppConfig::getInstance();

        if ($e instanceof HttpException) {
            $body = ['success' => false, 'message' => $e->getMessage()];
            if ($e->getErrors() !== null) {
                $body['errors'] = $e->getErrors();
            }
            Response::json($body, $e->getStatusCode())->send();

            return;
        }

        if ($e instanceof \InvalidArgumentException) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400)->send();

            return;
        }

        if ($e instanceof PDOException) {
            $message = $config->debug
                ? 'Database error: ' . $e->getMessage()
                : 'A database error occurred.';

            Response::json(['success' => false, 'message' => $message], 500)->send();

            return;
        }

        $message = $config->debug
            ? 'Server error: ' . $e->getMessage()
            : 'An unexpected error occurred.';

        Response::json(['success' => false, 'message' => $message], 500)->send();
    }
}
