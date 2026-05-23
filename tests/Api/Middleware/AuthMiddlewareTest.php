<?php

declare(strict_types=1);

namespace Tests\Api\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Middleware\AuthMiddleware;
use App\Contracts\AuthServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class AuthMiddlewareTest extends TestCase
{
    public function test_attaches_user_to_request_when_token_valid(): void
    {
        $auth = $this->createMock(AuthServiceInterface::class);
        $auth->expects($this->once())
            ->method('authenticate')
            ->with('valid-token')
            ->willReturn(['id' => 1, 'email' => 'u@test.com']);

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid-token';

        $middleware = new AuthMiddleware($auth);
        $request = new Request('GET', '/api/v1/todos', [], []);

        $nextCalled = false;
        $response = $middleware->handle($request, function (Request $req) use (&$nextCalled): Response {
            $nextCalled = true;
            $this->assertSame(1, $req->getAttribute('user')['id']);

            return Response::success();
        });

        $this->assertTrue($nextCalled);
        $this->assertSame(200, $response->getStatus());

        unset($_SERVER['HTTP_AUTHORIZATION']);
    }
}
