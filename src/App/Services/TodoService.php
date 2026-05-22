<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TodoRepositoryInterface;
use App\Contracts\TodoServiceInterface;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\TodoRepository;
use App\Support\Validator;

final class TodoService implements TodoServiceInterface
{
    public function __construct(
        private readonly TodoRepositoryInterface $todos = new TodoRepository(),
    ) {
    }

    public function list(int $userId, ?string $completedFilter): array
    {
        $completed = null;
        if ($completedFilter !== null && $completedFilter !== '') {
            $completed = in_array(strtolower($completedFilter), ['1', 'true', 'yes'], true);
        }

        return $this->todos->findAllForUser($userId, $completed);
    }

    public function get(int $id, int $userId): array
    {
        return $this->findOrFail($id, $userId);
    }

    /** @param array<string, mixed> $input */
    public function create(int $userId, array $input): array
    {
        Validator::required($input, ['title']);

        $title = trim((string) $input['title']);
        $description = isset($input['description']) ? trim((string) $input['description']) : null;
        $completed = !empty($input['completed']);

        if ($title === '') {
            throw new ValidationException(errors: ['title' => 'Title is required']);
        }

        $id = $this->todos->create(
            $userId,
            $title,
            $description !== '' ? $description : null,
            $completed,
        );

        return $this->findOrFail($id, $userId);
    }

    /** @param array<string, mixed> $input */
    public function update(int $id, int $userId, array $input): array
    {
        $existing = $this->findOrFail($id, $userId);

        $title = array_key_exists('title', $input)
            ? trim((string) $input['title'])
            : $existing['title'];
        $description = array_key_exists('description', $input)
            ? trim((string) $input['description'])
            : $existing['description'];
        $completed = array_key_exists('completed', $input)
            ? (bool) $input['completed']
            : (bool) $existing['completed'];

        if ($title === '') {
            throw new ValidationException(errors: ['title' => 'Title is required']);
        }

        $this->todos->update(
            $id,
            $userId,
            $title,
            $description !== '' ? $description : null,
            $completed,
        );

        return $this->findOrFail($id, $userId);
    }

    public function toggle(int $id, int $userId): array
    {
        $existing = $this->findOrFail($id, $userId);
        $newCompleted = !((int) $existing['completed'] === 1);

        $this->todos->toggle($id, $userId, $newCompleted);

        return $this->findOrFail($id, $userId);
    }

    public function delete(int $id, int $userId): void
    {
        $this->findOrFail($id, $userId);
        $this->todos->delete($id, $userId);
    }

    private function findOrFail(int $id, int $userId): array
    {
        $todo = $this->todos->findForUser($id, $userId);

        if ($todo === null) {
            throw new NotFoundException('Todo not found');
        }

        return $todo;
    }
}
