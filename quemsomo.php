<?php 
// require_once "validador_acesso.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title>Quem Somos - NetoNerd</title>
  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <style>
    .card-info {
      padding: 30px;
      margin-bottom: 30px;
    }
    .card-header {
      background-color: #007bff;
      color: white;
      text-align: center;
      font-size: 1.5rem;
      padding: 15px;
    }
    .card-body p {
      font-size: 1rem;
      line-height: 1.6;
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
        <li class="nav-item"><a class="nav-link btn btn-light text-white bg-dark ml-2" href="#login">Login</a></li>
      </ul>
    </div>
    </nav>

  <!-- Conteúdo Principal -->
  <div class="container mt-5">
    <h4 class="d-block w-100 p-4 bg-primary text-center text-white">Quem Somos</h4>

    <!-- Descrição da Empresa -->
    <div class="card card-info">
      <div class="card-header">
        <h5>Conheça a História da NetoNerd</h5>
      </div>
      <div class="card-body">
        <p>A NetoNerd tem suas raízes na cidade de Teresópolis, mais especificamente na UNIFESO, onde nasceu a Four_BA, uma empresa criada por quatro sócios. Após um ano de desenvolvimento, o sócio majoritário assumiu a liderança da empresa.</p>
        
        <p>Durante esse período, Rondineli, com sua visão e dedicação, criou o projeto NetoNerd, que logo se tornou a marca principal da empresa. Ligada à Four_BA, a NetoNerd tem como missão proporcionar <strong>independência tecnológica</strong> para todas as pessoas, com um forte compromisso com os valores cristãos.</p>

        <p>Hoje, a Four_BA já não é mais nossa identidade, pois a <strong>NetoNerd</strong> se consolidou como o nosso verdadeiro nome, representando nossa visão e nossos valores em cada atendimento e projeto que realizamos.</p>

        <p>A missão da NetoNerd é clara: <strong>Levar mais tecnologia, praticidade e confiança a cada pessoa e empresa que atendemos</strong>, sempre com respeito e comprometimento.</p>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-primary text-white text-center py-4 mt-5">
    <div class="container">
      <p class="mb-2">© 2025 NetoNerd - Todos os direitos reservados</p>
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

  <!-- FontAwesome -->
  <script src="https://kit.fontawesome.com/a076d05399.js"></script>

</body>
</html>
