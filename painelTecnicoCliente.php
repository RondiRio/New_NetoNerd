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
        <div class="dashboard-header">
            <h4>Bem-vindo, Cliente</h4>
            
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
                <tr>
                    <td>1</td>
                    <td>João Silva</td>
                    <td>Computador não liga</td>
                    <td><span class="badge badge-danger">Alta</span></td>
                    <td><span class="badge badge-warning">Pendente</span></td>
                    <td>
                        <!-- <button class="btn btn-aceitar btn-sm" data-toggle="modal" data-target="#modalAceitar">Aceitar</button> -->
                        <button class="btn btn-visualizar btn-sm" data-toggle="modal" data-target="#modalVisualizar">Visualizar</button>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>João Silva</td>
                    <td>Computador não liga</td>
                    <td><span class="badge badge-danger">Alta</span></td>
                    <td><span class="badge badge-warning">Pendente</span></td>
                    <td>
                        <!-- <button class="btn btn-aceitar btn-sm" data-toggle="modal" data-target="#modalAceitar">Aceitar</button> -->
                        <button class="btn btn-visualizar btn-sm" data-toggle="modal" data-target="#modalVisualizar">Visualizar</button>
                    </td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>João Silva</td>
                    <td>Computador com biep intermitente</td>
                    <td><span class="badge badge-danger">Alta</span></td>
                    <td><span class="badge badge-warning">Pendente</span></td>
                    <td>
                        <!-- <button class="btn btn-aceitar btn-sm" data-toggle="modal" data-target="#modalAceitar">Aceitar</button> -->
                        <button class="btn btn-visualizar btn-sm" data-toggle="modal" data-target="#modalVisualizar">Visualizar</button>
                    </td>
                </tr>
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
            <tbody>
                <tr>
                    <td>1</td>
                    <td>João Silva</td>
                    <td>Computador não liga</td>
                    <td><span class="badge badge-danger">Alta</span></td>
                    <td><span class="badge badge-success">Atendido</span></td>
                    <td>
                        <!-- <button class="btn btn-aceitar btn-sm" data-toggle="modal" data-target="#modalAceitar">Aceitar</button> -->
                        <button class="btn btn-visualizar btn-sm" data-toggle="modal" data-target="#modalVisualizar">Visualizar</button>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>João Silva</td>
                    <td>Computador não liga</td>
                    <td><span class="badge badge-danger">Alta</span></td>
                    <td><span class="badge badge-success">Atendido</span></td>
                    <td>
                        <!-- <button class="btn btn-aceitar btn-sm" data-toggle="modal" data-target="#modalAceitar">Aceitar</button> -->
                        <button class="btn btn-visualizar btn-sm" data-toggle="modal" data-target="#modalVisualizar">Visualizar</button>
                    </td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>João Silva</td>
                    <td>Computador com biep intermitente</td>
                    <td><span class="badge badge-danger">Alta</span></td>
                    <td><span class="badge badge-success">Atendido</span></td>
                    <td>
                        <!-- <button class="btn btn-aceitar btn-sm" data-toggle="modal" data-target="#modalAceitar">Aceitar</button> -->
                        <button class="btn btn-visualizar btn-sm" data-toggle="modal" data-target="#modalVisualizar">Visualizar</button>
                    </td>
                </tr>
            </tbody>
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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
