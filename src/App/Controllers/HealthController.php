<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\AppConfig;
use App\Http\Request;
use App\Http\Response;

final class HealthController
{
    public function index(Request $request): Response
    {
        $config = AppConfig::getInstance();

        return Response::success([
            'name' => $config->name,
            'version' => '1.0.0',
            'environment' => $config->env,
            'endpoints' => [
                'POST /api/v1/auth/register' => 'Register',
                'POST /api/v1/auth/login' => 'Login',
                'POST /api/v1/auth/logout' => 'Logout (Bearer)',
                'GET /api/v1/auth/me' => 'Current user (Bearer)',
                'GET /api/v1/todos' => 'List todos',
                'POST /api/v1/todos' => 'Create todo',
                'GET /api/v1/todos/{id}' => 'Get todo',
                'PUT /api/v1/todos/{id}' => 'Update todo',
                'PATCH /api/v1/todos/{id}/toggle' => 'Toggle completed',
                'DELETE /api/v1/todos/{id}' => 'Delete todo',
            ],
        ], $config->name . ' is running');
    }
}
