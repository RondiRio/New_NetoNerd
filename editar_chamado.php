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

// Busca os dados do chamado para preencher o formulário
$stmt = $conn->prepare("SELECT titulo, descricao, prioridade, status FROM chamados WHERE id = ? AND cliente_id = ?");
$stmt->bind_param("ii", $chamado_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$chamado = $result->fetch_assoc();

if (!$chamado) {
    die("Chamado não encontrado.");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Chamado</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h2>Editar Chamado</h2>
    <form action="salvar_edicao.php" method="POST">
        <input type="hidden" name="id" value="<?= $chamado_id ?>">

        <label for="titulo">Título:</label>
        <input type="text" name="titulo" value="<?= htmlspecialchars($chamado['titulo']) ?>" required>

        <label for="descricao">Descrição:</label>
        <textarea name="descricao" required><?= htmlspecialchars($chamado['descricao']) ?></textarea>

        <label for="prioridade">Prioridade:</label>
        <select name="prioridade">
            <option value="baixa" <?= $chamado['prioridade'] == 'baixa' ? 'selected' : '' ?>>Baixa</option>
            <option value="media" <?= $chamado['prioridade'] == 'media' ? 'selected' : '' ?>>Média</option>
            <option value="alta" <?= $chamado['prioridade'] == 'alta' ? 'selected' : '' ?>>Alta</option>
            <option value="critica" <?= $chamado['prioridade'] == 'critica' ? 'selected' : '' ?>>Crítica</option>
        </select>

        <label for="status">Status:</label>
        <select name="status">
            <option value="aberto" <?= $chamado['status'] == 'aberto' ? 'selected' : '' ?>>Aberto</option>
            <option value="em andamento" <?= $chamado['status'] == 'em andamento' ? 'selected' : '' ?>>Em Andamento</option>
            <option value="pendente" <?= $chamado['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
            <option value="resolvido" <?= $chamado['status'] == 'resolvido' ? 'selected' : '' ?>>Resolvido</option>
            <option value="cancelado" <?= $chamado['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
        </select>

        <button type="submit">Salvar Alterações</button>
    </form>
</body>
</html>
