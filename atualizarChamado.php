<?php
include_once("bandoDeDados/conexao.php");
session_start(); // Certifique-se de ter iniciado a sessão

$conn = new mysqli($host, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
$_SESSION['tipo_usuario'] = 'cliente';
// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    print_r($_SESSION); // Debug: imprime os dados recebidos');
    echo "</pre>";
    print_r($_POST); // Debug: imprime os dados recebidos
    $id = $_POST['chamado_id'];
    $user_type = $_SESSION['tipo_usuario']; // Assume que você tem o tipo de usuário na sessão
    $error = false;

    // Validação por tipo de usuário
    if ($user_type === 'tecnico') {
        // Técnico só pode atualizar status
        if (isset($_POST['status']) && !empty($_POST['status'])) {
            $status = $_POST['status'];
            $sql = "UPDATE chamados SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $id);
        } else {
            $error = true;
            echo "Técnico só pode atualizar o status do chamado.";
        }
    } 
    elseif ($user_type === 'cliente') {
        // Cliente só pode atualizar título e descrição
        if (isset($_POST['titulo']) && isset($_POST['descricao']) && 
            !empty($_POST['titulo']) && !empty($_POST['descricao'])) {
            $titulo = $_POST['titulo'];
            $descricao = $_POST['descricao'];
            $sql = "UPDATE chamados SET titulo = ?, descricao = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $titulo, $descricao, $id);
        } else {
            $error = true;
            echo "Cliente só pode atualizar título e descrição.";
        }
    } else {
        $error = true;
        echo "Tipo de usuário não autorizado.";
    }

    if (!$error) {
        if ($stmt->execute()) {
            echo "Chamado atualizado com sucesso!";
        } else {
            echo "Erro ao atualizar o chamado: " . $conn->error;
        }
        $stmt->close();
    }

    header("Location: painelTecnicoCliente.php?atualizado=sucesso"); // Redireciona para o painel após a atualização
}

$conn->close();
?>