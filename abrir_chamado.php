<?php 
require_once "validador_acesso.php";
?>
<html>
  <head>
    <meta charset="utf-8" />
    <title>NetoNerd</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
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

    <div class="container mt-5">
      <!-- Conteúdo Principal -->
      <div class="row">
        <div class="card-abrir-chamado">
          <div class="card bg-dark">
            <div class="card-header-custom">
              ABRIR CHAMADO
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col">
                  <form method="POST" action="registra_chamado.php">
                    <div class="form-group bg-light">
                      <label>Usuário</label>
                      <input name="usuario" type="text" class="form-control" placeholder="Usuário">
                    </div>

                    <div class="form-group bg-light">
                      <label>Título</label>
                      <input name="titulo" type="text" class="form-control" placeholder="Título">
                    </div>
                    
                    <div class="form-group bg-light">
                      <label>Categoria</label>
                        <select class="form-control" name="categoria">
                        <option value="selected">Selecione uma opção</option>
                        <option>Antivírus</option>
                        <option>Armazenamento</option>
                        <option>Atualizações</option>
                        <option>Auditoria</option>
                        <option>Automação</option>
                        <option>Backup</option>
                        <option>Banco de Dados</option>
                        <option>Big Data</option>
                        <option>Blockchain</option>
                        <option>Cloud Computing</option>
                        <option>Comércio Eletrônico</option>
                        <option>Compliance</option>
                        <option>Configuração</option>
                        <option>Consultoria</option>
                        <option>Conexão à Internet</option>
                        <option>Criptografia</option>
                        <option>Design de Interface</option>
                        <option>DevOps</option>
                        <option>Documentação</option>
                        <option>Equipamentos</option>
                        <option>Erros de Sistema</option>
                        <option>Experiência do Usuário</option>
                        <option>Firewall</option>
                        <option>Gerenciamento de Projetos</option>
                        <option>Gestão de TI</option>
                        <option>Governança</option>
                        <option>Hardware</option>
                        <option>Impressão</option>
                        <option>Integração</option>
                        <option>Inteligência Artificial</option>
                        <option>IoT (Internet das Coisas)</option>
                        <option>Licenciamento</option>
                        <option>Machine Learning</option>
                        <option>Manutenção</option>
                        <option>Marketing Digital</option>
                        <option>Monitoramento</option>
                        <option>Outros</option>
                        <option>Outsourcing</option>
                        <option>Planejamento Estratégico</option>
                        <option>Problemas com Conta</option>
                        <option>Programas</option>
                        <option>Realidade Aumentada</option>
                        <option>Realidade Virtual</option>
                        <option>Recuperação de Dados</option>
                        <option>Rede Local</option>
                        <option>Segurança</option>
                        <option>SEO</option>
                        <option>Servidores</option>
                        <option>Software</option>
                        <option>Suporte Técnico</option>
                        <option>Testes de Software</option>
                        <option>Treinamento</option>
                        <option>VPN</option>
                        <option>Wi-Fi</option>
                        </select>
                    </div>
                    
                    <div class="form-group bg-light">
                      <label>Descrição</label>
                      <textarea name="descricao" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="row mt-5">
                      <div class="col-6">
                        <a href="home.php" class="btn btn-lg btn-warning btn-block">Voltar</a>
                      </div>

                      <div class="col-6">
                        <button class="btn btn-lg btn-info btn-block" type="submit">Abrir</button>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap -->
  </body>
</html>
 