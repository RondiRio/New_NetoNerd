<?php

require_once __DIR__ . '/../../config/config.php';

$host = Config::get('APP_LEGACY_DB_HOST', 'localhost');
$dbname = Config::get('APP_LEGACY_DB_NAME', 'netonerd_chamados');
$username = Config::get('APP_LEGACY_DB_USERNAME', '');
$password = Config::get('APP_LEGACY_DB_PASSWORD', '');

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
// Tornar a conexão disponível para outros arquivos
if (!function_exists('getConnection')) {
    function getConnection() {
        global $conn;
        return $conn;
    }
}
// Agora você pode usar a variável $conn diretamente para interagir com o banco de dados.
?>
