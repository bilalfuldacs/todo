<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\TokenRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Exceptions\ConflictException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use App\Services\AuthService;
use App\Support\TokenHasher;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class AuthServiceTest extends TestCase
{
    private UserRepositoryInterface&MockObject $users;

    private TokenRepositoryInterface&MockObject $tokens;

    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->users = $this->createMosk(UserRepositoryInterface::class);
        $this->tokens = $this->createMock(TokenRepositoryInterface::class);
        $this->service = new AuthService($this->users, $this->tokens);
    }

    public function test_register_creates_user_and_returns_token(): void
    {
        $this->users->expects($this->once())
            ->method('emailExists')
            ->with('john@example.com')
            ->willReturn(false);

        $this->users->expects($this->once())
            ->method('create')
            ->with('John', 'john@example.com', $this->isType('string'))
            ->willReturn(1);

        $this->users->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn([
                'id' => 1,
                'name' => 'John',
                'email' => 'john@example.com',
                'created_at' => '2026-01-01 00:00:00',
            ]);

        $this->tokens->expects($this->once())
            ->method('create')
            ->with(
                1,
                $this->callback(static fn (string $hash): bool => strlen($hash) === 64),
                $this->matchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'),
            );

        $result = $this->service->register([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $this->assertSame('John', $result['user']['name']);
        $this->assertSame('Bearer', $result['token_type']);
        $this->assertSame(64, strlen($result['token']));
    }

    public function test_register_fails_when_email_exists(): void
    {
        $this->users->method('emailExists')->willReturn(true);

        $this->expectException(ConflictException::class);
        $this->service->register([
            'name' => 'John',
            'email' => 'taken@example.com',
            'password' => 'password123',
        ]);
    }

    public function test_register_validates_password_length(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->register([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'short',
        ]);
    }

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $hash = password_hash('password123', PASSWORD_DEFAULT);

        $this->users->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn([
                'id' => 1,
                'name' => 'John',
                'email' => 'john@example.com',
                'password' => $hash,
                'created_at' => '2026-01-01 00:00:00',
            ]);

        $this->tokens->expects($this->once())->method('create');

        $result = $this->service->login([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $this->assertArrayNotHasKey('password', $result['user']);
        $this->assertNotEmpty($result['token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->users->method('findByEmail')->willReturn([
            'id' => 1,
            'email' => 'john@example.com',
            'password' => password_hash('correct', PASSWORD_DEFAULT),
        ]);

        $this->expectException(UnauthorizedException::class);
        $this->service->login([
            'email' => 'john@example.com',
            'password' => 'wrong',
        ]);
    }

    public function test_authenticate_returns_user_for_valid_token(): void
    {
        $plain = bin2hex(random_bytes(16));

        $this->tokens->expects($this->once())
            ->method('findUserByTokenHash')
            ->with(TokenHasher::hash($plain))
            ->willReturn(['id' => 1, 'name' => 'John', 'email' => 'john@example.com']);

        $user = $this->service->authenticate($plain);
        $this->assertSame(1, $user['id']);
    }

    public function test_authenticate_fails_without_token(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->service->authenticate(null);
    }

    public function test_logout_revokes_token(): void
    {
        $plain = 'abc123token';

        $this->tokens->expects($this->once())
            ->method('revoke')
            ->with(TokenHasher::hash($plain), 1);

        $this->service->logout($plain, ['id' => 1]);
    }
}
