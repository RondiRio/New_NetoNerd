<?php
// session_start();
require 'bandoDeDados/conexao.php';
require_once "validador_acesso.php";

$conn = getConnection();

// Verifica se o ID do chamado foi enviado via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Chamado inválido.");
}

$chamado_id = $_GET['id'];
$usuario_id = $_SESSION['id'];

// Busca os dados do chamado
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
    <meta charset="utf-8" />
    <title>NetoNerd</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <style>
        .card-abrir-chamado {
            padding: 30px 0 0 0;
            width: 100%;
            margin: 0 auto;
        }
        .navbar-custom {
            background-color: #007bff;
        }
        .card-header-custom {
            background-color: #007bff;
            color: white;
        }
        .card-body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom bg-primary">
        <a class="navbar-brand" href="index.php">
            <img class="logo" src="imagens/logoNetoNerd.jpg" alt="Logo NetoNerd">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse LinksNav" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="atendimento.php">Atendimento</a></li>
                <li class="nav-item"><a class="nav-link" href="planos.php">Planos</a></li>
                <li class="nav-item"><a class="nav-link" href="contato.php">Contato</a></li>
                <li class="nav-item"><a class="nav-link" href="quemsomo.php">Quem somos</a></li>
                <li class="nav-item"><a class="nav-link btn btn-light text-white bg-dark ml-2" href="logoff.php">Sair</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="card-abrir-chamado">
                <div class="card bg-dark">
                    <div class="card-header-custom">
                        EDITAR CHAMADO
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <form action="salvar_edicao.php" method="POST">
                                    <input type="hidden" name="id" value="<?= $chamado_id ?>">

                                    <div class="form-group bg-light">
                                        <label>Título</label>
                                        <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($chamado['titulo']) ?>" required>
                                    </div>

                                    <div class="form-group bg-light">
                                        <label>Descrição</label>
                                        <textarea name="descricao" class="form-control" rows="3" required><?= htmlspecialchars($chamado['descricao']) ?></textarea>
                                    </div>

                                    <div class="form-group bg-light">
                                        <label>Prioridade</label>
                                        <select name="prioridade" class="form-control">
                                            <option value="baixa" <?= $chamado['prioridade'] == 'baixa' ? 'selected' : '' ?>>Baixa</option>
                                            <!-- <option value="media" <?= $chamado['prioridade'] == 'media' ? 'selected' : '' ?>>Média</option> -->
                                            <!-- <option value="alta" <?= $chamado['prioridade'] == 'alta' ? 'selected' : '' ?>>Alta</option> -->
                                            <!-- <option value="critica" <?= $chamado['prioridade'] == 'critica' ? 'selected' : '' ?>>Crítica</option> -->
                                        </select>
                                    </div>

                                    <div class="form-group bg-light">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="aberto" <?= $chamado['status'] == 'aberto' ? 'selected' : '' ?>>Aberto</option>
                                            <!-- <option value="em andamento" <?= $chamado['status'] == 'em andamento' ? 'selected' : '' ?>>Em Andamento</option> -->
                                            <!-- <option value="pendente" <?= $chamado['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option> -->
                                            <!-- <option value="resolvido" <?= $chamado['status'] == 'resolvido' ? 'selected' : '' ?>>Resolvido</option> -->
                                            <!-- <option value="cancelado" <?= $chamado['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option> -->
                                        </select>
                                    </div>

                                    <div class="row mt-5">
                                        <div class="col-6">
                                            <a href="home.php" class="btn btn-lg btn-warning btn-block">Voltar</a>
                                        </div>
                                        <div class="col-6">
                                            <button class="btn btn-lg btn-info btn-block" type="submit">Salvar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
