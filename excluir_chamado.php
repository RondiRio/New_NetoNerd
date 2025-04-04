<?php
session_start();
require 'bandoDeDados/conexao.php';

$conn = getConnection();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id'])) {
    die("Usuário não autenticado.");
}

// Verifica se o ID do chamado foi enviado via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Chamado inválido.");
}

$chamado_id = $_GET['id'];
$usuario_id = $_SESSION['id'];

// Verifica se o chamado pertence ao usuário logado antes de excluir
$stmt = $conn->prepare("DELETE FROM chamados WHERE id = ? AND cliente_id = ?");
$stmt->bind_param("ii", $chamado_id, $usuario_id);

if ($stmt->execute()) {
    echo "<script>alert('Chamado excluído com sucesso!'); window.location.href='home.php';</script>";
} else {
    echo "<script>alert('Erro ao excluir chamado.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
