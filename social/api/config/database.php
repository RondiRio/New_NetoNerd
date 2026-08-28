<?php
require_once __DIR__ . '/env.php';

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host    = env('DB_HOST', 'localhost');
        $port    = env('DB_PORT', '3306');
        $name    = env('DB_NAME', 'agenda_creche');
        $user    = env('DB_USER', 'root');
        $pass    = env('DB_PASS', '');

        $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro de conexão com o banco de dados.', 'codigo' => 'DB_ERROR']);
            exit;
        }

        return self::$instance;
    }
}
