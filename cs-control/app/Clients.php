<?php
declare(strict_types=1);

class Clients
{
    // Garante que o nome do cliente exista na tabela clients (usada pelo dropdown
    // "Registrar atividade" e pela futura tela de Clientes). Chamado sempre que
    // uma atividade é criada com um nome de cliente novo.
    public static function ensure(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            return;
        }
        $db = Database::get();
        $stmt = $db->prepare('INSERT IGNORE INTO clients (name) VALUES (?)');
        $stmt->execute([$name]);
    }

    public static function all(): array
    {
        $db = Database::get();
        return $db->query('SELECT id, name FROM clients ORDER BY name ASC')->fetchAll();
    }
}
