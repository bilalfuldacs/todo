<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Contracts\TodoServiceInterface;

final class TodoController
{
    public function __construct(
        private readonly TodoServiceInterface $todoService,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $request->getAttribute('user');
        $todos = $this->todoService->list(
            (int) $user['id'],
            $request->query('completed'),
        );

        return Response::success(['todos' => $todos]);
    }

    public function show(Request $request): Response
    {
        $user = $request->getAttribute('user');
        $todo = $this->todoService->get((int) $request->getAttribute('id'), (int) $user['id']);

        return Response::success(['todo' => $todo]);
    }

    public function store(Request $request): Response
    {
        $user = $request->getAttribute('user');
        $todo = $this->todoService->create((int) $user['id'], $request->body);

        return Response::success(['todo' => $todo], 'Todo created', 201);
    }

    public function update(Request $request): Response
    {
        $user = $request->getAttribute('user');
        $todo = $this->todoService->update(
            (int) $request->getAttribute('id'),
            (int) $user['id'],
            $request->body,
        );

        return Response::success(['todo' => $todo], 'Todo updated');
    }

    public function toggle(Request $request): Response
    {
        $user = $request->getAttribute('user');
        $todo = $this->todoService->toggle(
            (int) $request->getAttribute('id'),
            (int) $user['id'],
        );

        return Response::success(['todo' => $todo], 'Todo toggled');
    }

    public function destroy(Request $request): Response
    {
        $user = $request->getAttribute('user');
        $this->todoService->delete((int) $request->getAttribute('id'), (int) $user['id']);

        return Response::success(message: 'Todo deleted');
    }
}
