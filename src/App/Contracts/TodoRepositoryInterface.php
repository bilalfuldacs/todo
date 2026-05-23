<?php

declare(strict_types=1);

namespace App\Contracts;

interface TodoRepositoryInterface
{
    /** @return list<array<string, mixed>> */
    public function findAllForUser(int $userId, ?bool $completed = null): array;

    public function findForUser(int $id, int $userId): ?array;

    public function create(int $userId, string $title, ?string $description, bool $completed): int;

    public function update(
        int $id,
        int $userId,
        string $title,
        ?string $description,
        bool $completed,
    ): bool;

    public function toggle(int $id, int $userId, bool $completed): bool;

    public function delete(int $id, int $userId): bool;
}
