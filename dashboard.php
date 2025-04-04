<?php session_start()?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Técnicos - NetoNerd</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <style>
        :root {
            --primary: #0A1128;
            --secondary: #001F54;
            --accent: #FDB827;
            --light: #F4F4F9;
            --dark: #061A40;
        }

        body {
            background-color: var(--light);
            color: var(--dark);
        }

        .sidebar {
            background-color: var(--primary);
            color: var(--light);
            height: 100vh;
        }

        .sidebar h2 {
            color: var(--accent);
        }

        .sidebar a {
            color: var(--light);
        }

        .sidebar a:hover {
            background-color: var(--secondary);
        }

        .card {
            border-left: 5px solid var(--accent);
        }

        .tecnicos .btn-edit {
            background-color: var(--secondary);
            color: var(--light);
        }

        .tecnicos .btn-edit:hover {
            background-color: var(--primary);
        }

        .tecnicos .btn-delete {
            background-color: #C0392B;
            color: var(--light);
        }

        .tecnicos .btn-delete:hover {
            background-color: #E74C3C;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3">
            <h2 class="text-center">NetoNerd</h2>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="#" class="nav-link">Dashboard</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Técnicos</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Chamados</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Relatórios</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Configurações</a></li>
                <li class="nav-item"><a class="nav-link btn btn-light text-white bg-dark ml-2" href="logoff.php">Sair</a></li>
            </ul>
        </div>
        <div class="main-content flex-grow-1 p-4">
            <header>
                <h1 class="mb-4">Dashboard Administração</h1>
            </header>
            <section class="stats row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <?php
                            include 'bandoDeDados/conexao.php';

                            $sql = "SELECT COUNT(*) AS total_chamados FROM chamados WHERE status = 'Aberto'";
                            $result = $conn->query($sql);

                            if ($result && $row = $result->fetch_assoc()) {
                                $totalChamados = $row['total_chamados'];
                            } else {
                                $totalChamados = 0;
                            }

                            $conn->close();
                        ?>
                        <div>Chamados Abertos: <span><?php echo htmlspecialchars($totalChamados); ?></span></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <?php
                            include 'bandoDeDados/conexao.php';

                            $today = date('Y-m-d');
                            $sql = "SELECT COUNT(*) AS atendimentos_hoje FROM chamados WHERE DATE(data_fechamento) = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('s', $today);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result && $row = $result->fetch_assoc()) {
                                $atendimentosHoje = $row['atendimentos_hoje'];
                            } else {
                                $atendimentosHoje = 0;
                            }

                            $stmt->close();
                            $conn->close();
                        ?>
                        <div>Atendimentos Hoje: <span><?php echo htmlspecialchars($atendimentosHoje); ?></span></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                    <?php
include 'bandoDeDados/conexao.php';

// Inicializa as contagens
$contagens = [
    'PIX' => 0,
    'Dinheiro' => 0,
    'Cartão Crédito' => 0,
    'Cartão Débito' => 0,
    'Boleto' => 0
];

// Consulta SQL para agrupar formas de pagamento corretamente
$sql = "SELECT TRIM(LOWER(pagamento_forma)) AS pagamento_forma, COUNT(*) AS total 
        FROM chamados 
        GROUP BY TRIM(LOWER(pagamento_forma))";
$result = $conn->query($sql);

// Mapeamento das formas de pagamento
$mapa_pagamento = [
    'pix' => 'PIX',
    'dinheiro' => 'Dinheiro',
    'cartão crédito' => 'Cartão Crédito',
    'cartão de crédito' => 'Cartão Crédito',  
    'cartão débito' => 'Cartão Débito',
    'cartão de débito' => 'Cartão Débito',
    'boleto' => 'Boleto'
];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $forma_banco = $row['pagamento_forma'];
        
        $forma_normalizada = $mapa_pagamento[$forma_banco] ?? null;

        if ($forma_normalizada && isset($contagens[$forma_normalizada])) {
            $contagens[$forma_normalizada] = $row['total'];
        } else {
            echo "Erro: Forma de pagamento desconhecida - " . $row['pagamento_forma'] . "<br>";
        }
    }
}

$conn->close();

// Exibe os valores corretamente
$pix = $contagens['PIX'];
$dinheiro = $contagens['Dinheiro'];
$cartaoCredito = $contagens['Cartão Crédito'];
$cartaoDebito = $contagens['Cartão Débito'];
$boleto = $contagens['Boleto'];
?>


                        <div>
                            <strong>Pagamento:</strong> PIX: <?php echo htmlspecialchars($pix); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <div>
                            <strong>Pagamento:</strong> Dinheiro: <?php echo htmlspecialchars($dinheiro); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <div>
                            <strong>Pagamento:</strong> Cartão de Crédito: <?php echo htmlspecialchars($cartaoCredito); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <div>
                            <strong>Pagamento:</strong> Cartão de Débito: <?php echo htmlspecialchars($cartaoDebito); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <div>
                            <strong>Pagamento:</strong> Boleto: <?php echo htmlspecialchars($boleto); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <div>Técnicos Ativos: <span>3</span></div>
                    </div>
                </div>
            </section>
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTecnicoModal">Adicionar Técnico</button>
            <section class="tecnicos">
                <h2 class="mb-3">Lista de Técnicos</h2>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Cadastro</th>
                            <th>Matricula</th>
                            <th>Veículo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php
                                // Inclui o arquivo de conexão
                                include 'bandoDeDados/conexao.php';

                                // Consulta corrigida
                                $sql = "SELECT id, nome, carro_do_dia, email, created_at, matricula, status_tecnico FROM tecnicos";
                                $result = $conn->query($sql);

                                if ($result === false) {
                                    echo "<tr><td colspan='8' class='text-center'>Erro na consulta: " . $conn->error . "</td></tr>";
                                } elseif ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['status_tecnico']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['matricula']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['carro_do_dia']) . "</td>";
                                        echo "<td>";
                                        echo "<button class='btn btn-sm btn-edit'>Editar</button>";
                                        echo "<button class='btn btn-sm btn-success'><i class='fas fa-file'></i> Relatório</button>";
                                        echo "<button class='btn btn-sm btn-delete'>Excluir</button>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center'>Nenhum técnico encontrado</td></tr>";
                                }

                                // Fecha a conexão
                                $conn->close();
                            ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </div>

    <!-- Modal para adicionar técnico -->
<div class="modal fade" id="addTecnicoModal" tabindex="-1" aria-labelledby="addTecnicoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTecnicoModalLabel">Adicionar Técnico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="processa_adicionar_tecnico.php" method="POST">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="status_technician" class="form-label">Status</label>
                        <select class="form-select" id="status_technician" name="status_technician" required>
                            <option value="Active">Ativo</option>
                            <option value="Inactive">Inativo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="registration" class="form-label">Matrícula</label>
                        <?php
                            // Verifica a última matrícula no banco de dados a partir de 2025F1000
                            // 2025: ano em que entrou para a empresa
                            // F1: Filial 1
                            // 000: dígito verificador
                            include 'bandoDeDados/conexao.php';

                            $sql = "SELECT matricula FROM tecnicos WHERE matricula LIKE '2025F1%' ORDER BY matricula DESC LIMIT 1";
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $lastMatricula = $row['matricula'];
                                $newMatricula = '2025F1' . str_pad((int)substr($lastMatricula, 6) + 1, 3, '0', STR_PAD_LEFT);
                            } else {
                                $newMatricula = '2025F1000';
                            }

                            
                        ?>
                        <input type="text" class="form-control" id="registration" name="registration" required placeholder="<?php echo '' . htmlspecialchars($newMatricula); ?>"/>
                        
                    </div>
                    <div class="mb-3">
                        <label for="vehicle_of_the_day" class="form-label">Veículo</label>
                        <input type="text" class="form-control" id="vehicle_of_the_day" name="vehicle_of_the_day" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
