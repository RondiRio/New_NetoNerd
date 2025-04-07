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
    $matricula = trim($_POST['matricula']);
    $senha = trim($_POST['senha']);
    
    if (!empty($matricula) && !empty($senha)) {
        $stmt = $pdo->prepare("SELECT matricula, senha_hash, status_tecnico, id, nome FROM tecnicos WHERE matricula = :matricula");
        $stmt->bindParam(':matricula', $matricula, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Update the password hash if it's not hashed
        if ($usuario && $usuario['senha_hash'] === $senha) {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE tecnicos SET senha_hash = :hash WHERE matricula = :matricula");
            $updateStmt->execute(['hash' => $hash, 'matricula' => $matricula]);
            $usuario['senha_hash'] = $hash;
        }

        // Debug para verificar os valores
        echo "Senha fornecida: " . $senha . "<br>";
        echo "Hash no banco: " . $usuario['senha_hash'] . "<br>";
        echo "Resultado verify: " . (password_verify($senha, $usuario['senha_hash']) ? 'true' : 'false') . "<br>";

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            if ($usuario['status_tecnico'] === 'Ativo') {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_matricula'] = $usuario['matricula'];

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
            $erro = "Matrícula ou senha inválidos.";
        }
    } else {
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
