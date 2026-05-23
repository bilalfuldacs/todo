<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\TodoRepositoryInterface;
use App\Infrastructure\Database;
use PDO;

final class TodoRepository implements TodoRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** @return list<array<string, mixed>> */
    public function findAllForUser(int $userId, ?bool $completed = null): array
    {
        $sql = 'SELECT id, title, description, completed, created_at, updated_at
                FROM todos WHERE user_id = ?';
        $params = [$userId];

        if ($completed !== null) {
            $sql .= ' AND completed = ?';
            $params[] = $completed ? 1 : 0;
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, title, description, completed, created_at, updated_at
             FROM todos WHERE id = ? AND user_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(int $userId, string $title, ?string $description, bool $completed): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO todos (user_id, title, description, completed) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $title, $description, $completed ? 1 : 0]);

        return (int) $this->db->lastInsertId();
    }

    public function update(
        int $id,
        int $userId,
        string $title,
        ?string $description,
        bool $completed,
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE todos SET title = ?, description = ?, completed = ?
             WHERE id = ? AND user_id = ?'
        );

        return $stmt->execute([$title, $description, $completed ? 1 : 0, $id, $userId]);
    }

    public function toggle(int $id, int $userId, bool $completed): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE todos SET completed = ? WHERE id = ? AND user_id = ?'
        );

        return $stmt->execute([$completed ? 1 : 0, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM todos WHERE id = ? AND user_id = ?');

        return $stmt->execute([$id, $userId]);
    }
}
