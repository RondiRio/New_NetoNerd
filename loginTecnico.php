<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Login Técnico - NetoNerd</title>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="css/main.css">
        <style>
            .card-login {
                padding: 30px 0 0 0;
                width: 350px;
                margin: 0 auto;
            }
            .alinhamentoFooter{
                position: fixed;
                bottom: 0;
                width: 100%;
                text-align: center;
                background-color: #0d6efd;
                color: white;
                padding: 10px 0;
            }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-custom bg-primary">
            <a class="navbar-brand" href="#">
                <img class="logo" src="imagens/logoNetoNerd.jpg" alt="Logo NetoNerd">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse LinksNav" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="atendimento.php">Atendimento</a></li>
                    <!-- <li class="nav-item"><a class="nav-link" href="planos.php">Planos</a></li>
                    <li class="nav-item"><a class="nav-link" href="contato.php">Contato</a></li>
                    <li class="nav-item"><a class="nav-link" href="quemsomo.php">Quem somos</a></li> -->
                    <!-- <li class="nav-item"><a class="nav-link btn btn-light text-white bg-dark ml-2" href="login.php">Login</a></li> -->
                    <!-- <li class="nav-item"><a class="nav-link btn btn-light text-white bg-dark ml-2" href="loginTecnico.php">Técnico</a></li> -->
                </ul>
            </div>
        </nav>
        <div class="text-center mt-4" style="color: #555; font-size: 1.5rem; font-weight: bold;">
            Vamos ver os chamados de hoje
        </div>
        <div class="container">
            <div class="row">
                <div class="card-login">
                    <div class="card">
                        <div class="card-header bg-primary text-white">Login Técnico</div>
                        <div class="card-body bg-white">
                            <form action="valida_loginTecnico.php" method="post">
                                <div class="form-group">
                                    <input name="matricula" type="text" class="form-control bg-light" placeholder="Matrícula" required>
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input name="senha" id="senha" type="password" class="form-control bg-light" placeholder="Senha" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility()">👁️</button>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    function togglePasswordVisibility() {
                                        const senhaInput = document.getElementById('senha');
                                        if (senhaInput.type === 'password') {
                                            senhaInput.type = 'text';
                                        } else {
                                            senhaInput.type = 'password';
                                        }
                                    }
                                </script>
                                <?php if(isset($_GET['login']) && $_GET['login'] == 'erro'){ ?>
                                <div class="text-danger">Matrícula ou senha inválido(s)</div>
                                <?php } ?>
                                <?php if(isset($_GET['login']) && $_GET['login'] == 'erro2'){ ?>
                                <div class="text-danger">Faça login para verificar os chamados de hoje.</div>
                                <?php } ?>
                                <button class="btn btn-lg btn-info btn-block" type="submit">Entrar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="alinhamentoFooter">
            <div class="">
                <p class="">© 2025 Four_BA - Todos os direitos reservados</p>
            </div>
        </footer>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
    </body>
</html></head>