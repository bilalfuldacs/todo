<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Controllers\AuthController;
use App\Http\Request;
use App\Contracts\AuthServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * API-layer tests: real controllers + HTTP request/response, mocked services (no database).
 */
final class AuthApiTest extends TestCase
{
    private AuthServiceInterface&MockObject $authService;

    private AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = $this->createMock(AuthServiceInterface::class);
        $this->controller = new AuthController($this->authService);
    }

    public function test_register_endpoint_returns_201_json(): void
    {
        $this->authService->expects($this->once())
            ->method('register')
            ->with([
                'name' => 'Jane',
                'email' => 'jane@example.com',
                'password' => 'password123',
            ])
            ->willReturn([
                'user' => ['id' => 1, 'name' => 'Jane', 'email' => 'jane@example.com'],
                'token' => str_repeat('a', 64),
                'token_type' => 'Bearer',
                'expires_in_days' => 7,
            ]);

        $request = new Request('POST', '/api/v1/auth/register', [], [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response = $this->controller->register($request);
        $body = $this->assertResponseSuccess($response, 201, 'Registration');

        $this->assertArrayHasKey('token', $body['data']);
        $this->assertSame('Jane', $body['data']['user']['name']);
    }

    public function test_login_endpoint_returns_user_and_token(): void
    {
        $this->authService->method('login')->willReturn([
            'user' => ['id' => 2, 'email' => 'user@test.com'],
            'token' => 'secret-token',
            'token_type' => 'Bearer',
            'expires_in_days' => 7,
        ]);

        $request = new Request('POST', '/api/v1/auth/login', [], [
            'email' => 'user@test.com',
            'password' => 'password123',
        ]);

        $response = $this->controller->login($request);
        $body = $this->assertResponseSuccess($response, 200, 'Login');

        $this->assertSame('secret-token', $body['data']['token']);
    }

    public function test_me_endpoint_returns_authenticated_user(): void
    {
        $request = new Request('GET', '/api/v1/auth/me', [], []);
        $request->setAttribute('user', ['id' => 3, 'name' => 'Me', 'email' => 'me@test.com']);

        $response = $this->controller->me($request);
        $body = $this->assertResponseSuccess($response);

        $this->assertSame(3, $body['data']['user']['id']);
    }

    public function test_logout_endpoint_calls_service(): void
    {
        $this->authService->expects($this->once())
            ->method('logout')
            ->with('my-token', ['id' => 1]);

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer my-token';

        $request = new Request('POST', '/api/v1/auth/logout', [], []);
        $request->setAttribute('user', ['id' => 1]);

        $response = $this->controller->logout($request);
        $this->assertResponseSuccess($response, 200, 'Logged out');

        unset($_SERVER['HTTP_AUTHORIZATION']);
    }
}
