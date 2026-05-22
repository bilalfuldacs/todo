<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Contracts\AuthServiceInterface;

final class AuthController
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {
    }

    public function register(Request $request): Response
    {
        $data = $this->authService->register($request->body);

        return Response::success($data, 'Registration successful', 201);
    }

    public function login(Request $request): Response
    {
        $data = $this->authService->login($request->body);

        return Response::success($data, 'Login successful');
    }

    public function logout(Request $request): Response
    {
        $user = $request->getAttribute('user');
        $this->authService->logout($request->bearerToken(), $user);

        return Response::success(message: 'Logged out successfully');
    }

    public function me(Request $request): Response
    {
        return Response::success(['user' => $request->getAttribute('user')]);
    }
}
