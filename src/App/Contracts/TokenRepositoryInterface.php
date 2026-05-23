<?php

declare(strict_types=1);

namespace App\Contracts;

interface TokenRepositoryInterface
{
    public function create(int $userId, string $tokenHash, string $expiresAt): void;

    public function findUserByTokenHash(string $tokenHash): ?array;

    public function revoke(string $tokenHash, int $userId): void;
}
