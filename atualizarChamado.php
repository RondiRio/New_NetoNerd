<?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seu_banco_de_dados";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id']; // ID do chamado a ser atualizado
    $status = $_POST['status']; // Novo status do chamado
    $descricao = $_POST['descricao']; // Nova descrição do chamado

    // Validação básica
    if (!empty($id) && !empty($status) && !empty($descricao)) {
        // Atualiza o chamado no banco de dados
        $sql = "UPDATE chamados SET status = ?, descricao = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $descricao, $id);

        if ($stmt->execute()) {
            echo "Chamado atualizado com sucesso!";
        } else {
            echo "Erro ao atualizar o chamado: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Por favor, preencha todos os campos.";
    }
}

$conn->close();
?>

<!-- Formulário para atualizar o chamado -->
<form method="POST" action="atualizarChamado.php">
    <label for="id">ID do Chamado:</label>
    <input type="text" name="id" id="id" required><br>

    <label for="status">Status:</label>
    <input type="text" name="status" id="status" required><br>

    <label for="descricao">Descrição:</label>
    <textarea name="descricao" id="descricao" required></textarea><br>

    <button type="submit">Atualizar Chamado</button>
</form>