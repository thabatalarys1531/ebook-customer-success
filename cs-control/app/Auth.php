<?php
declare(strict_types=1);

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $db = Database::get();
        $stmt = $db->prepare("SELECT id, name, role, status, password_hash FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['status'] === 'ativo' && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }

        return false;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('login.php');
        }
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function name(): string
    {
        return $_SESSION['user_name'] ?? '';
    }

    public static function role(): string
    {
        return $_SESSION['user_role'] ?? 'membro';
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function hasUsers(): bool
    {
        $db = Database::get();
        return (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
    }

    public static function createUser(string $name, string $email, string $password, string $role = 'admin'): int
    {
        $db = Database::get();
        $stmt = $db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        $userId = (int) $db->lastInsertId();

        // Admin recebe todos os módulos liberados por padrão; membro começa sem nada
        // (quem administra libera depois na tela de Permissões).
        $allowed = $role === 'admin' ? 1 : 0;
        $db->exec("INSERT INTO user_permissions (user_id, permission_id, allowed)
                   SELECT {$userId}, id, {$allowed} FROM permissions");

        return $userId;
    }
}
