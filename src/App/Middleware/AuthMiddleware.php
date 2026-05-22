<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Contracts\AuthServiceInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        $user = $this->authService->authenticate($request->bearerToken());
        $request->setAttribute('user', $user);

        return $next($request);
    }
}
