<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\TokenRepositoryInterface;
use App\Infrastructure\Database;
use PDO;

final class TokenRepository implements TokenRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $userId, string $tokenHash, string $expiresAt): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO api_tokens (user_id, token, expires_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $tokenHash, $expiresAt]);
    }

    public function findUserByTokenHash(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.name, u.email, u.created_at
             FROM api_tokens t
             INNER JOIN users u ON u.id = t.user_id
             WHERE t.token = ? AND t.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function revoke(string $tokenHash, int $userId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM api_tokens WHERE user_id = ? AND token = ?'
        );
        $stmt->execute([$userId, $tokenHash]);
    }

    public function revokeAllForUser(int $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM api_tokens WHERE user_id = ?');
        $stmt->execute([$userId]);
    }
}
