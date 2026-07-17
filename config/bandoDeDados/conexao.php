<?php

// Carregar configurações do .env
require_once __DIR__ . '/../config.php';

// Obter configurações do banco de dados
$dbConfig = Config::database();
$host = $dbConfig['host'];
$dbname = $dbConfig['name'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];

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
// Função para obter dados do cliente
function obterDadosCliente() {
    $conn = getConnection();
    
    // Verificar se usuário está logado
    if (!isset($_SESSION['id'])) {
        return null;
    }
    
    $usuario_id = $_SESSION['id'];

    // Busca dados do cliente
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
    if (!$stmt) {
        die("Erro na preparação da query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $cliente = $resultado->fetch_assoc();
    $stmt->close();
    
    return $cliente;
}

?>