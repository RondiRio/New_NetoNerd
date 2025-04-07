<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Técnico - NetoNerd</title>
    <!-- Link para o Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
        }
        .container {
            margin-top: 50px;
        }
        .dashboard-header {
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .dashboard-header h4 {
            margin: 0;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-aceitar {
            background-color: #28a745;
            color: #fff;
        }
        .btn-aceitar:hover {
            background-color: #218838;
        }
        .btn-visualizar {
            background-color: #007bff;
            color: #fff;
        }
        .btn-visualizar:hover {
            background-color: #0056b3;
        }
    </style>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    
    <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-custom bg-primary">
      <a class="navbar-brand" href="index.php">
        <img class="logo" src="imagens/logoNetoNerd.jpg" alt="Logo NetoNerd" >
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
    <div class="container">
        <!-- Cabeçalho do Dashboard -->
        <?php 
session_start();
include 'bandoDeDados/conexao.php'; // Certifique-se de que a conexão está correta

if (!isset($_SESSION['nome']) && isset($_SESSION["id"])) {
    $conn = getConnection();
    
    $sql = "SELECT nome FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION["id"]);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['nome'] = $row['nome']; // Armazena o nome na sessão
    }

    $stmt->close();
    $conn->close();
}

// Exibe o nome do usuário armazenado na sessão
?>
        <div class="dashboard-header">
            <?php
            include 'bandoDeDados/conexao.php';

            $conn = getConnection();

            if (!isset($_SESSION['id'])) {
                die("Usuário não autenticado.");
            }

            $usuario_id = $_SESSION['id'];

            $stmt = $conn->prepare("SELECT nome, genero FROM clientes WHERE id = ?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $nome_usuario = $row['nome'];
                $genero = $row['genero']; // Supondo que a coluna 'genero' exista no banco de dados
                
                if ($genero === 'Masculino') {
                    echo "<h4>Bem-vindo, " . htmlspecialchars($nome_usuario, ENT_QUOTES, 'UTF-8') . "</h4>";
                } elseif ($genero === 'Feminino') {
                    echo "<h4>Bem-vinda, " . htmlspecialchars($nome_usuario, ENT_QUOTES, 'UTF-8') . "</h4>";
                } else {
                    echo "<h4>Bem-vindo(a), " . htmlspecialchars($nome_usuario, ENT_QUOTES, 'UTF-8') . "</h4>";
                }
            } else {
                echo "<h4>Bem-vindo, Usuário</h4>";
            }

            $stmt->close();
            $conn->close();
            ?>
        </div>
        <!-- Botão para Abrir Chamado -->
        <div class="text-right mb-4">
          <a href="abrir_chamado.php" class="btn btn-primary">Abrir Chamado</a>
        </div>
        <!-- Tabela de Chamados -->
        <h4 class="mb-4">Chamados Pendentes</h4>
        <table class="table table-striped">
    <thead>
        <tr>
        
            <th>ID</th>
            <th>Cliente</th>
            <th>Descrição</th>
            <th>Prioridade</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php
    include 'bandoDeDados/conexao.php';
    /* The above code is a PHP script that starts a session using the `session_start()` function. This
    function initializes a session or resumes the current session based on a session identifier
    passed via a GET or POST request, or a cookie. Sessions are used in PHP to store and retrieve
    data across multiple pages for a single user. */
    // session_start();

    $conn = getConnection();

    if (!isset($_SESSION['id'])) {
        die("Usuário não autenticado.");
    }

    $usuario_id = $_SESSION['id'];

    $stmt = $conn->prepare("SELECT id, cliente_id, titulo, descricao, prioridade, status, protocolo, nome_usuario 
                            FROM chamados WHERE cliente_id = ? 
                            AND status NOT IN ('resolvido', 'cancelado')");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $nome_usuario = $row['nome_usuario'];
        $titulo = $row['titulo'];
        $prioridade = $row['prioridade'];
        $status = $row['status'];

        echo "<tr>
                <td>$id</td>
                <td>$nome_usuario</td>
                <td>$titulo</td>
                <td><span class='badge bg-" . getBadgeClass($prioridade) . "'>$prioridade</span></td>
                <td><span class='badge bg-" . getStatusClass($status) . "'>$status</span></td>
                <td>
                    
                    <a href='editar_chamado.php?id=$id' class='btn btn-warning btn-sm'>Editar</a>
                    <a href='excluir_chamado.php?id=$id' class='btn btn-danger btn-sm' onclick='return confirm(\"Tem certeza que deseja excluir este chamado?\")'>Excluir</a>
                </td>
              </tr>";
    }

    $stmt->close();
    $conn->close();
    ?>

    <?php
    function getBadgeClass($prioridade) {
        switch ($prioridade) {
            case 'alta': return 'danger';
            case 'media': return 'warning';
            case 'baixa': return 'info';
            case 'critica': return 'dark';
            default: return 'secondary';
        }
    }

    function getStatusClass($status) {
        switch ($status) {
            case 'aberto': return 'primary';
            case 'em andamento': return 'warning';
            case 'pendente': return 'secondary';
            case 'resolvido': return 'success';
            case 'cancelado': return 'danger';
            default: return 'light';
        }
    }
    ?>
    </tbody>
</table>


<br><br><br>
        <h4 class="mb-4">Chamados atendidos</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Descrição</th>
                    <th>Prioridade</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>



<!-- Chamados fechados -->
<?php


require 'bandoDeDados/conexao.php'; 

$conn = getConnection(); // Obtém a conexão MySQLi
$usuario_id = $_SESSION['id']; // Obtém o ID do usuário logado

// Consulta SQL para buscar apenas chamados do usuário logado com status 'resolvido' ou 'cancelado'
$stmt = $conn->prepare("SELECT id, cliente_id, titulo, descricao, prioridade, status, protocolo, nome_usuario 
                        FROM chamados 
                        WHERE cliente_id = ? 
                        AND (status = 'resolvido' OR status = 'cancelado')");
$stmt->bind_param("i", $usuario_id); // "i" indica que é um inteiro
$stmt->execute();
$result = $stmt->get_result();
?>

<table class="table table-striped">
    <tbody>
        <?php
        // Verifica se há resultados e preenche a tabela
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $nome_usuario = $row['nome_usuario'];
            $titulo = $row['titulo'];
            $prioridade = $row['prioridade'];
            $status = $row['status'];

            echo "<tr>
                    <td>$id</td>
                    <td>$nome_usuario</td>
                    <td>$titulo</td>
                    <td><span class='badge badge-" . getBadgeClass($prioridade) . "'>$prioridade</span></td>
                    <td><span class='badge badge-" . getStatusClass($status) . "'>$status</span></td>
                    <td>
                        <button class='btn btn-visualizar btn-sm' data-toggle='modal' data-target='#modalVisualizar' data-id='$id'>Visualizar</button>
                    </td>
                  </tr>";
        }
        ?>
    </tbody>
</table>


        </table>

        <!-- Modal de Aceitar Chamado -->
        <div class="modal fade" id="modalAceitar" tabindex="-1" role="dialog" aria-labelledby="modalAceitarLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAceitarLabel">Aceitar Chamado</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Tem certeza de que deseja aceitar o chamado?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-aceitar">Aceitar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Visualizar Chamado -->
        <div class="modal fade" id="modalVisualizar" tabindex="-1" role="dialog" aria-labelledby="modalVisualizarLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalVisualizarLabel">Visualizar Chamado</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5>Detalhes:</h5>
                        <p><strong>Cliente:</strong> João Silva</p>
                        <p><strong>Descrição:</strong> Computador não liga</p>
                        <p><strong>Prioridade:</strong> Alta</p>
                        <p><strong>Status:</strong> Atendido</p>
                        <textarea class="form-control" rows="3" placeholder="Mensagem para o cliente..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="button" class="btn btn-primary">Enviar Atualização</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
  <footer class="bg-primary text-white text-center py-4 mt-5">
    <div class="container">
      <p class="mb-2">© 2025 Four_BA - Todos os direitos reservados</p>
      <div class="footer-links mb-3">
        <a href="#atendimento" class="text-white mx-3">Atendimento</a>
        <a href="#planos" class="text-white mx-3">Planos</a>
        <a href="#contato" class="text-white mx-3">Contato</a>
        <a href="#login" class="text-white mx-3">Login</a>
      </div>
      <div class="social-links">
        <a href="https://facebook.com" target="_blank" class="text-white mx-3">
          <i class="bg-dark fab fa-facebook fa-2x"></i>
        </a>
        <a href="https://twitter.com" target="_blank" class="text-white mx-3">
          <i class="bg-dark fab fa-twitter fa-2x"></i>
        </a>
        <a href="https://instagram.com" target="_blank" class="text-white mx-3">
          <i class="bg-dark fab fa-instagram fa-2x"></i>
        </a>
      </div>
    </div>
  </footer>

    <!-- Scripts Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></scrip>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
