<?php
session_start();
require 'bandoDeDados/conexao.php';

$conn = getConnection();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id'])) {
    die("Usuário não autenticado.");
}

// Verifica se os campos obrigatórios foram enviados
if (!isset($_POST['id'], $_POST['titulo'], $_POST['descricao'], $_POST['prioridade'], $_POST['status'])) {
    die("Dados incompletos.");
}

$chamado_id = $_POST['id'];
$titulo = $_POST['titulo'];
$descricao = $_POST['descricao'];
$prioridade = $_POST['prioridade'];
$status = $_POST['status'];
$usuario_id = $_SESSION['id'];

// Atualiza o chamado no banco de dados
$stmt = $conn->prepare("UPDATE chamados SET titulo = ?, descricao = ?, prioridade = ?, status = ? WHERE id = ? AND cliente_id = ?");
$stmt->bind_param("ssssii", $titulo, $descricao, $prioridade, $status, $chamado_id, $usuario_id);

if ($stmt->execute()) {
    echo "<script>alert('Chamado atualizado com sucesso!'); window.location.href='home.php';</script>";
} else {
    echo "<script>alert('Erro ao atualizar chamado.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
