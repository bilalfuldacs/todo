<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\AppConfig;
use App\Contracts\AuthServiceInterface;
use App\Contracts\TokenRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Exceptions\ConflictException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use App\Repositories\TokenRepository;
use App\Repositories\UserRepository;
use App\Support\TokenHasher;
use App\Support\Validator;
use DateTimeImmutable;

final class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users = new UserRepository(),
        private readonly TokenRepositoryInterface $tokens = new TokenRepository(),
    ) {
    }

    /** @param array<string, mixed> $input */
    public function register(array $input): array
    {
        Validator::required($input, ['name', 'email', 'password']);

        $name = trim((string) $input['name']);
        $email = strtolower(trim((string) $input['email']));
        $password = (string) $input['password'];

        Validator::email($email);
        Validator::password($password);

        if ($this->users->emailExists($email)) {
            throw new ConflictException('Email is already registered');
        }

        $userId = $this->users->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
        $auth = $this->issueToken($userId);

        return [
            'user' => $this->users->findById($userId),
            ...$auth,
        ];
    }

    /** @param array<string, mixed> $input */
    public function login(array $input): array
    {
        Validator::required($input, ['email', 'password']);

        $email = strtolower(trim((string) $input['email']));
        $password = (string) $input['password'];

        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new UnauthorizedException('Invalid email or password');
        }

        unset($user['password']);
        $auth = $this->issueToken((int) $user['id']);

        return [
            'user' => $user,
            ...$auth,
        ];
    }

    public function logout(?string $plainToken, array $user): void
    {
        if ($plainToken !== null) {
            $this->tokens->revoke(TokenHasher::hash($plainToken), (int) $user['id']);
        }
    }

    public function authenticate(?string $plainToken): array
    {
        if ($plainToken === null || $plainToken === '') {
            throw new UnauthorizedException(
                'Authentication required. Send Authorization: Bearer {token}'
            );
        }

        $user = $this->tokens->findUserByTokenHash(TokenHasher::hash($plainToken));

        if ($user === null) {
            throw new UnauthorizedException('Invalid or expired token');
        }

        return $user;
    }

    private function issueToken(int $userId): array
    {
        $plainToken = bin2hex(random_bytes(32));
        $ttlDays = AppConfig::getInstance()->tokenTtlDays;
        $expiresAt = (new DateTimeImmutable("+{$ttlDays} days"))->format('Y-m-d H:i:s');

        $this->tokens->create($userId, TokenHasher::hash($plainToken), $expiresAt);

        return [
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_in_days' => $ttlDays,
        ];
    }
}
