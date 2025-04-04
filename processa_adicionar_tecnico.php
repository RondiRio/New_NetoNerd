<?php
// Inclui o arquivo de conexão com o banco de dados
require_once 'bandoDeDados/conexao.php';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados enviados pelo formulário
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $status = isset($_POST['status_technician']) ? trim($_POST['status_technician']) : '';
    $ativo = '1'; // Assuming 'ativo' is always active
    $matricula = isset($_POST['registration']) ? trim($_POST['registration']) : '';
    $veiculo = isset($_POST['vehicle_of_the_day']) ? trim($_POST['vehicle_of_the_day']) : '';
    $senha = isset($_POST['password']) ? password_hash(trim($_POST['password']), PASSWORD_DEFAULT) : '';

    // Valida se os campos obrigatórios foram preenchidos
    if (empty($nome) || empty($email) || empty($status) || empty($matricula) || empty($veiculo) || empty($senha)) {
        echo'<pre>';
        print_r($_POST);
        echo '</pre>';
        die('Por favor, preencha todos os campos obrigatórios.');
    }

    // Prepara a consulta SQL para inserir os dados na tabela chamados
    $sql = "INSERT INTO tecnicos (nome, email, status_tecnico, Ativo, matricula, carro_do_dia, senha_hash) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Prepara a declaração
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Vincula os parâmetros
        $stmt->bind_param('sssssss', $nome, $email, $status, $ativo, $matricula, $veiculo, $senha);

        // Executa a consulta
        if ($stmt->execute()) {
            echo 'Técnico adicionado com sucesso!';
            // Redireciona para a página de sucesso ou outra página desejada
            header('Location: dashboard.php');
        } else {
            echo 'Erro ao adicionar técnico: ' . $stmt->error;
        }

        // Fecha a declaração
        $stmt->close();
    } else {
        echo 'Erro na preparação da consulta: ' . $conn->error;
    }

    // Fecha a conexão com o banco de dados
    $conn->close();
} else {
    echo 'Método de requisição inválido.';
}
?>