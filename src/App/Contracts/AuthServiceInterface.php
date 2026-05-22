<?php

declare(strict_types=1);

namespace App\Contracts;

interface AuthServiceInterface
{
    /** @param array<string, mixed> $input */
    public function register(array $input): array;

    /** @param array<string, mixed> $input */
    public function login(array $input): array;

    public function logout(?string $plainToken, array $user): void;

    public function authenticate(?string $plainToken): array;
}
