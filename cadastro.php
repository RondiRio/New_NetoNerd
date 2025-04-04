<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Cadastro de Cliente - NetoNerd</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
      .card-cadastro {
        padding: 30px 0;
        width: 400px;
        margin: 0 auto;
      }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-custom bg-primary">
      <a class="navbar-brand" href="#">
        <img class="logo" src="imagens/logoNetoNerd.jpg" alt="Logo NetoNerd">
      </a>
    </nav>

    <div class="container mt-5">
      <div class="card-cadastro">
        <div class="card">
          <div class="card-header bg-primary text-white">Cadastre-se</div>
          <div class="card-body">
            <form action="processa_cadastro.php" method="post">
              <div class="form-group">
                <label>Nome Completo</label>
                <input name="nome" type="text" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Email</label>
                <input name="email" type="email" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Telefone</label>
                <input name="telefone" type="number" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Senha</label>
                <input name="senha" type="password" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Endereço</label>
                <input name="endereco" type="text" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Complemento</label>
                <input name="complemento" type="text" class="form-control">
              </div>
              <div class="form-group">
                <label>CEP</label>
                <input name="cep" type="text" class="form-control" required>
              </div>
              <button class="btn btn-lg btn-primary btn-block" type="submit">Cadastrar</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
