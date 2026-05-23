<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\NotFoundException;
use App\Middleware\MiddlewareInterface;

final class Router
{
    /** @var list<array{methods: string[], pattern: string, handler: callable, middleware: MiddlewareInterface[]}> */
    private array $routes = [];

    /** @param callable(Request): Response $handler */
    public function add(
        array $methods,
        string $pattern,
        callable $handler,
        array $middleware = [],
    ): void {
        $this->routes[] = [
            'methods' => array_map('strtoupper', $methods),
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function get(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add(['GET'], $pattern, $handler, $middleware);
    }

    public function post(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add(['POST'], $pattern, $handler, $middleware);
    }

    public function put(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add(['PUT'], $pattern, $handler, $middleware);
    }

    public function patch(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add(['PATCH'], $pattern, $handler, $middleware);
    }

    public function delete(string $pattern, callable $handler, array $middleware = []): void
    {
        $this->add(['DELETE'], $pattern, $handler, $middleware);
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if (!in_array($request->method, $route['methods'], true)) {
                continue;
            }

            $params = $this->match($route['pattern'], $request->path);
            if ($params === null) {
                continue;
            }

            foreach ($params as $key => $value) {
                $request->setAttribute($key, $value);
            }

            $handler = $route['handler'];
            $pipeline = array_reduce(
                array_reverse($route['middleware']),
                static function (callable $next, MiddlewareInterface $middleware): callable {
                    return static fn (Request $req): Response => $middleware->handle($req, $next);
                },
                static fn (Request $req): Response => $handler($req),
            );

            return $pipeline($request);
        }

        throw new NotFoundException('Route not found');
    }

    /** @return ?array<string, string> */
    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
