<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Controllers\TodoController;
use App\Exceptions\NotFoundException;
use App\Http\Request;
use App\Contracts\TodoServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class TodoApiTest extends TestCase
{
    private TodoServiceInterface&MockObject $todoService;

    private TodoController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->todoService = $this->createMock(TodoServiceInterface::class);
        $this->controller = new TodoController($this->todoService);
    }

    private function authenticatedRequest(string $method, string $path, array $body = []): Request
    {
        $request = new Request($method, $path, [], $body);
        $request->setAttribute('user', ['id' => 10, 'name' => 'Tester', 'email' => 't@test.com']);

        return $request;
    }

    public function test_index_returns_todos_list(): void
    {
        $this->todoService->expects($this->once())
            ->method('list')
            ->with(10, null)
            ->willReturn([
                ['id' => 1, 'title' => 'First', 'completed' => 0],
            ]);

        $response = $this->controller->index($this->authenticatedRequest('GET', '/api/v1/todos'));
        $body = $this->assertResponseSuccess($response);

        $this->assertCount(1, $body['data']['todos']);
    }

    public function test_store_returns_201_with_created_todo(): void
    {
        $this->todoService->expects($this->once())
            ->method('create')
            ->with(10, ['title' => 'New task', 'description' => 'Details'])
            ->willReturn([
                'id' => 5,
                'title' => 'New task',
                'description' => 'Details',
                'completed' => 0,
            ]);

        $request = $this->authenticatedRequest('POST', '/api/v1/todos', [
            'title' => 'New task',
            'description' => 'Details',
        ]);

        $response = $this->controller->store($request);
        $body = $this->assertResponseSuccess($response, 201, 'Todo created');

        $this->assertSame(5, $body['data']['todo']['id']);
    }

    public function test_show_returns_single_todo(): void
    {
        $this->todoService->method('get')->willReturn([
            'id' => 7,
            'title' => 'One',
            'completed' => 1,
        ]);

        $request = $this->authenticatedRequest('GET', '/api/v1/todos/7');
        $request->setAttribute('id', '7');

        $response = $this->controller->show($request);
        $body = $this->assertResponseSuccess($response);

        $this->assertSame(7, $body['data']['todo']['id']);
    }

    public function test_toggle_returns_updated_todo(): void
    {
        $this->todoService->expects($this->once())
            ->method('toggle')
            ->with(3, 10)
            ->willReturn(['id' => 3, 'title' => 'Task', 'completed' => 1]);

        $request = $this->authenticatedRequest('PATCH', '/api/v1/todos/3/toggle');
        $request->setAttribute('id', '3');

        $response = $this->controller->toggle($request);
        $body = $this->assertResponseSuccess($response, 200, 'Todo toggled');

        $this->assertSame(1, (int) $body['data']['todo']['completed']);
    }

    public function test_destroy_returns_success_message(): void
    {
        $this->todoService->expects($this->once())->method('delete')->with(2, 10);

        $request = $this->authenticatedRequest('DELETE', '/api/v1/todos/2');
        $request->setAttribute('id', '2');

        $response = $this->controller->destroy($request);
        $this->assertResponseSuccess($response, 200, 'Todo deleted');
    }

    public function test_show_propagates_not_found_from_service(): void
    {
        $this->todoService->method('get')->willThrowException(new NotFoundException('Todo not found'));

        $request = $this->authenticatedRequest('GET', '/api/v1/todos/999');
        $request->setAttribute('id', '999');

        $this->expectException(NotFoundException::class);
        $this->controller->show($request);
    }
}
