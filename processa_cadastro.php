<?php
require 'bandoDeDados/conexao.php'; // Arquivo de configuração do banco de dados

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $telefone = trim($_POST['telefone']);
    $endereco = trim($_POST['endereco']);
    $complemento = trim($_POST['complemento']);
    $cep = trim($_POST['cep']);

    // Conexão com o banco de dados
    $conn = getConnection(); // Função que retorna a conexão MySQLi

    if (!$conn) {
        die(json_encode(["status" => "erro", "mensagem" => "Erro na conexão com o banco de dados: " . mysqli_connect_error()]));
    }

    try {
        // Verificar se o e-mail já está cadastrado
        $stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(["status" => "erro", "mensagem" => "E-mail já cadastrado!"]);
            exit;
        }

        // Inserir cliente no banco
        $stmt = $conn->prepare("INSERT INTO clientes (nome, email, senha_hash, telefone, endereco, complemento, cep) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nome, $email, $senha, $telefone, $endereco, $complemento, $cep);

        if ($stmt->execute()) {
            header("Location: index.php?cadastro=sucesso");
            echo json_encode(["status" => "sucesso", "mensagem" => "Cadastro realizado com sucesso!"]);
        } else {
            echo json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar cliente."]);
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo json_encode(["status" => "erro", "mensagem" => "Erro no cadastro: " . $e->getMessage()]);
    }
}
?>
