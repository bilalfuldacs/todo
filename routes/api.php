<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HealthController;
use App\Controllers\TodoController;
use App\Http\Router;
use App\Middleware\AuthMiddleware;
use App\Services\AuthService;
use App\Services\TodoService;

return static function (Router $router): void {
    $authService = new AuthService();
    $todoService = new TodoService();
    $authMiddleware = new AuthMiddleware($authService);

    $health = new HealthController();
    $auth = new AuthController($authService);
    $todos = new TodoController($todoService);

    $router->get('/api/v1', fn ($req) => $health->index($req));
    $router->get('/api', fn ($req) => $health->index($req));

    $router->post('/api/v1/auth/register', fn ($req) => $auth->register($req));
    $router->post('/api/v1/auth/login', fn ($req) => $auth->login($req));
    $router->post('/api/v1/auth/logout', fn ($req) => $auth->logout($req), [$authMiddleware]);
    $router->get('/api/v1/auth/me', fn ($req) => $auth->me($req), [$authMiddleware]);

    $router->get('/api/v1/todos', fn ($req) => $todos->index($req), [$authMiddleware]);
    $router->post('/api/v1/todos', fn ($req) => $todos->store($req), [$authMiddleware]);
    $router->get('/api/v1/todos/{id}', fn ($req) => $todos->show($req), [$authMiddleware]);
    $router->put('/api/v1/todos/{id}', fn ($req) => $todos->update($req), [$authMiddleware]);
    $router->patch('/api/v1/todos/{id}', fn ($req) => $todos->update($req), [$authMiddleware]);
    $router->patch('/api/v1/todos/{id}/toggle', fn ($req) => $todos->toggle($req), [$authMiddleware]);
    $router->post('/api/v1/todos/{id}/toggle', fn ($req) => $todos->toggle($req), [$authMiddleware]);
    $router->delete('/api/v1/todos/{id}', fn ($req) => $todos->destroy($req), [$authMiddleware]);

    // Legacy paths (without /v1) for backward compatibility
    $router->post('/api/auth/register', fn ($req) => $auth->register($req));
    $router->post('/api/auth/login', fn ($req) => $auth->login($req));
    $router->post('/api/auth/logout', fn ($req) => $auth->logout($req), [$authMiddleware]);
    $router->get('/api/auth/me', fn ($req) => $auth->me($req), [$authMiddleware]);
    $router->get('/api/todos', fn ($req) => $todos->index($req), [$authMiddleware]);
    $router->post('/api/todos', fn ($req) => $todos->store($req), [$authMiddleware]);
    $router->get('/api/todos/{id}', fn ($req) => $todos->show($req), [$authMiddleware]);
    $router->put('/api/todos/{id}', fn ($req) => $todos->update($req), [$authMiddleware]);
    $router->patch('/api/todos/{id}', fn ($req) => $todos->update($req), [$authMiddleware]);
    $router->patch('/api/todos/{id}/toggle', fn ($req) => $todos->toggle($req), [$authMiddleware]);
    $router->post('/api/todos/{id}/toggle', fn ($req) => $todos->toggle($req), [$authMiddleware]);
    $router->delete('/api/todos/{id}', fn ($req) => $todos->destroy($req), [$authMiddleware]);
};
