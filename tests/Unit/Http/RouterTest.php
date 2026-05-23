<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Exceptions\NotFoundException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use Tests\TestCase;

final class RouterTest extends TestCase
{
    public function test_dispatches_matching_route_with_params(): void
    {
        $router = new Router();
        $router->get('/api/v1/todos/{id}', static function (Request $request): Response {
            return Response::success(['id' => $request->getAttribute('id')]);
        });

        $request = new Request('GET', '/api/v1/todos/42', [], []);
        $response = $router->dispatch($request);

        $this->assertSame(200, $response->getStatus());
        $this->assertSame('42', $response->getBody()['data']['id']);
    }

    public function test_throws_when_route_not_found(): void
    {
        $router = new Router();

        $this->expectException(NotFoundException::class);
        $router->dispatch(new Request('GET', '/unknown', [], []));
    }

    public function test_method_must_match(): void
    {
        $router = new Router();
        $router->post('/api/v1/auth/login', static fn (): Response => Response::success());

        $this->expectException(NotFoundException::class);
        $router->dispatch(new Request('GET', '/api/v1/auth/login', [], []));
    }
}
