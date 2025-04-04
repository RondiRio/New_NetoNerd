<?php
session_start();

include("bandoDeDados/conexao.php"); // Arquivo de configuração do banco de dados
$conn = getConnection(); // Obtém a conexão MySQLi

if (!$conn) {
    die("Erro na conexão com o banco de dados: " . mysqli_connect_error());
}

// Variáveis de autenticação
$usuario_autenticado = false;
$usuario_id = null;
// $usuario_perfil_id = null;
$usuario_id_usuario = null;

// Obtendo dados do formulário
$email = $_POST['email'];
$senha = $_POST['senha'];

$query = "SELECT id, nome FROM clientes WHERE email = ? AND senha_hash = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Erro ao preparar a query: " . $conn->error);
}

$stmt->bind_param("ss", $email, $senha);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    $usuario_autenticado = true;
    $usuario_id = $usuario['id'];
    // $usuario_perfil_id = $usuario['perfil_id'];
    $usuario_nome = $usuario['nome']; // Agora o nome também é recuperado
}

$stmt->close();
$conn->close();


if ($usuario_autenticado) {
    $_SESSION['autenticado'] = 'SIM';
    $_SESSION['id'] = $usuario_id;
    // $_SESSION['perfil_id'] = $usuario_perfil_id;
    $_SESSION['nome'] = $usuario_nome;  // Aqui o nome é armazenado na sessão
    header('Location: home.php');
    exit();
} else {
    $_SESSION['autenticado'] = 'NÃO';
    header('Location: index.php?login=erro');
    exit();
}

?>
