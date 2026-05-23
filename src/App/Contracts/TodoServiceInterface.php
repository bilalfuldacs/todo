<?php

declare(strict_types=1);

namespace App\Contracts;

interface TodoServiceInterface
{
    public function list(int $userId, ?string $completedFilter): array;

    public function get(int $id, int $userId): array;

    /** @param array<string, mixed> $input */
    public function create(int $userId, array $input): array;

    /** @param array<string, mixed> $input */
    public function update(int $id, int $userId, array $input): array;

    public function toggle(int $id, int $userId): array;

    public function delete(int $id, int $userId): void;
}
