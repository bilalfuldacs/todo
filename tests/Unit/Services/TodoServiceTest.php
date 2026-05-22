<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\TodoRepositoryInterface;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Services\TodoService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class TodoServiceTest extends TestCase
{
    private TodoRepositoryInterface&MockObject $todos;

    private TodoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->todos = $this->createMock(TodoRepositoryInterface::class);
        $this->service = new TodoService($this->todos);
    }

    public function test_list_returns_todos_from_repository(): void
    {
        $expected = [['id' => 1, 'title' => 'Task', 'completed' => 0]];

        $this->todos->expects($this->once())
            ->method('findAllForUser')
            ->with(5, null)
            ->willReturn($expected);

        $this->assertSame($expected, $this->service->list(5, null));
    }

    public function test_list_filters_completed(): void
    {
        $this->todos->expects($this->once())
            ->method('findAllForUser')
            ->with(5, true)
            ->willReturn([]);

        $this->service->list(5, 'true');
    }

    public function test_create_persists_and_returns_todo(): void
    {
        $this->todos->expects($this->once())
            ->method('create')
            ->with(1, 'Buy milk', '2L', false)
            ->willReturn(10);

        $this->todos->expects($this->once())
            ->method('findForUser')
            ->with(10, 1)
            ->willReturn([
                'id' => 10,
                'title' => 'Buy milk',
                'description' => '2L',
                'completed' => 0,
            ]);

        $todo = $this->service->create(1, [
            'title' => 'Buy milk',
            'description' => '2L',
        ]);

        $this->assertSame(10, $todo['id']);
    }

    public function test_create_requires_title(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->create(1, []);
    }

    public function test_get_throws_when_not_found(): void
    {
        $this->todos->method('findForUser')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->get(99, 1);
    }

    public function test_toggle_flips_completed_status(): void
    {
        $this->todos->method('findForUser')
            ->willReturnOnConsecutiveCalls(
                ['id' => 1, 'title' => 'Task', 'description' => null, 'completed' => 0],
                ['id' => 1, 'title' => 'Task', 'description' => null, 'completed' => 1],
            );

        $this->todos->expects($this->once())
            ->method('toggle')
            ->with(1, 5, true);

        $result = $this->service->toggle(1, 5);
        $this->assertSame(1, (int) $result['completed']);
    }

    public function test_delete_removes_existing_todo(): void
    {
        $this->todos->method('findForUser')->willReturn(['id' => 1, 'title' => 'X', 'completed' => 0]);
        $this->todos->expects($this->once())->method('delete')->with(1, 5);

        $this->service->delete(1, 5);
        $this->addToAssertionCount(1);
    }
}
