<?php
declare(strict_types=1);

class Permissions
{
    public static function all(): array
    {
        $db = Database::get();
        return $db->query('SELECT * FROM permissions ORDER BY id ASC')->fetchAll();
    }

    public static function forUser(int $userId): array
    {
        $db = Database::get();
        $stmt = $db->prepare("SELECT p.key_name, p.label, up.allowed
                               FROM permissions p
                               LEFT JOIN user_permissions up ON up.permission_id = p.id AND up.user_id = ?
                               ORDER BY p.id ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // Admin sempre tem acesso a tudo. Membro só acessa o que foi liberado.
    public static function userCan(int $userId, string $key): bool
    {
        $db = Database::get();
        $stmt = $db->prepare("SELECT u.role, up.allowed
                               FROM users u
                               LEFT JOIN permissions p ON p.key_name = ?
                               LEFT JOIN user_permissions up ON up.permission_id = p.id AND up.user_id = u.id
                               WHERE u.id = ?");
        $stmt->execute([$key, $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            return false;
        }
        if ($row['role'] === 'admin') {
            return true;
        }
        return (bool) $row['allowed'];
    }

    public static function requireAccess(string $key): void
    {
        $userId = Auth::id();
        if ($userId === null || !self::userCan($userId, $key)) {
            http_response_code(403);
            echo 'Você não tem permissão para acessar esta página. Fale com um administrador.';
            exit;
        }
    }

    public static function updateForUser(int $userId, array $allowedKeys): void
    {
        $db = Database::get();
        $all = self::all();

        $db->beginTransaction();
        $stmt = $db->prepare('UPDATE user_permissions SET allowed = ? WHERE user_id = ? AND permission_id = ?');
        foreach ($all as $perm) {
            $allowed = in_array($perm['key_name'], $allowedKeys, true) ? 1 : 0;
            $stmt->execute([$allowed, $userId, $perm['id']]);
        }
        $db->commit();
    }
}
