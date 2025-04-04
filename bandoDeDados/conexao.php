<?php 

$host = "localhost";
$dbname = "netonerd_chamados";
$username = "root";
$password = "";

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
