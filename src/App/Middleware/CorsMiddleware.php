<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Config\AppConfig;
use App\Http\Request;
use App\Http\Response;

final class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $origins = AppConfig::getInstance()->corsOrigins;
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ($origins === '*') {
            header('Access-Control-Allow-Origin: *');
        } elseif ($origin !== '' && $this->isOriginAllowed($origin, $origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');

        if ($request->method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        return $next($request);
    }

    private function isOriginAllowed(string $origin, string $allowed): bool
    {
        $list = array_map('trim', explode(',', $allowed));

        return in_array($origin, $list, true);
    }
}
