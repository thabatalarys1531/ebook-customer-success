<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $host = env('DB_HOST', 'localhost');
            $name = env('DB_NAME');
            $user = env('DB_USER');
            $pass = env('DB_PASS');

            $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            // Muitos servidores cPanel rodam com o relógio em UTC. Sem isso, NOW()/CURDATE()
            // do MySQL ficam horas à frente do horário de Brasília usado pelo PHP, e cards
            // como "Reuniões hoje" contam errado perto da virada do dia.
            self::$instance->exec("SET time_zone = '-03:00'");
        }

        return self::$instance;
    }
}
