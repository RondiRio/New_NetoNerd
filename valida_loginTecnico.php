<?php
session_start(); // Inicia a sessão

include("bandoDeDados/conexao.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = trim($_POST['matricula'] ?? ''); // Agora verifica pela matrícula
    $senha = trim($_POST['senha'] ?? '');

    if (!empty($matricula) && !empty($senha)) {
        // Consulta o usuário no banco de dados pela matrícula
        $stmt = $pdo->prepare("SELECT matricula, senha_hash, status_tecnico, id, nome FROM tecnicos WHERE matricula = :matricula");
        $stmt->bindParam(':matricula', $matricula, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            if ($usuario['status_tecnico'] === 'Ativo') {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_matricula'] = $usuario['matricula'];

                // Verifica se a matrícula contém "ADM" para redirecionamento
                if (strpos($usuario['matricula'], 'ADM') !== false) {
                    header("Location: dashboard.php");
                } else {
                    header("Location: paineltecnico.php");
                }
                exit;
            } else {
                $erro = "Usuário inativo. Entre em contato com a gerência.";
            }
        } else {
            print_r($_POST);
            $erro = "Matrícula ou senha inválidos.";
        }
    } else {
        print_r($_POST);
        $erro = "Preencha todos os campos.";
    }
}

// Exibir erro caso exista
if (isset($erro)) {
    echo "<script>alert('$erro');</script>";
}


if (isset($erro)) {
    echo "<p style='color: red;'>$erro</p>";
}
?>
