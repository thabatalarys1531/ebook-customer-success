<?php
declare(strict_types=1);

class Users
{
    public static function all(): array
    {
        $db = Database::get();
        return $db->query('SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at ASC')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT id, name, email, role, status FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function emailExists(string $email): bool
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function setStatus(int $id, string $status): void
    {
        $db = Database::get();
        $stmt = $db->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function count(): int
    {
        $db = Database::get();
        return (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}
