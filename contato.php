<?php 
// require_once "validador_acesso.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title>Contato - NetoNerd</title>
  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <style>
    .contact-info {
      text-align: center;
      margin-top: 50px;
    }
    .btn-whatsapp {
      background-color: #25d366;
      color: white;
      padding: 15px;
      font-size: 1.25rem;
      width: 100%;
      border-radius: 5px;
      text-align: center;
    }
    /* Adicionando mais espaço abaixo da área de conteúdo */
    .content {
      margin-bottom: 100px;
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
  <div class="container mt-5 content">
    <h4 class="d-block w-100 p-4 bg-primary text-center text-white">Contato com a NetoNerd</h4>

    <div class="contact-info">
      <h5>💬 Entre em contato conosco!</h5>
      <p>Estamos prontos para te ajudar com o que for necessário. Para agilizar seu atendimento, basta clicar no número abaixo e iniciar uma conversa no WhatsApp:</p>
      
      <a href="https://wa.me/5521977395867?text=Olá,%20quero%20mais%20informações%20sobre%20os%20serviços%20da%20NetoNerd." class="btn btn-whatsapp">
        <i class="fab fa-whatsapp"></i> Iniciar conversa no WhatsApp
      </a>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-primary text-white text-center ">
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
