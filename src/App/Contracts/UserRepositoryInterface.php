<?php

declare(strict_types=1);

namespace App\Contracts;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?array;

    public function findById(int $id): ?array;

    public function emailExists(string $email): bool;

    public function create(string $name, string $email, string $passwordHash): int;
}
