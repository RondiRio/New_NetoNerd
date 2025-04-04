<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Técnico - NetoNerd</title>
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
        .btn-visualizar {
            background-color: #007bff;
            color: #fff;
        }
        .btn-visualizar:hover {
            background-color: #0056b3;
        }
        .badge-verde {
            background-color: #28a745;
        }
        .badge-amarelo {
            background-color: #ffc107;
        }
        .badge-vermelho {
            background-color: #dc3545;
        }
        .badge-preto {
            background-color: #343a40;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <?php
                include 'bandoDeDados/conexao.php';

                $tecnicoId = 1; // Substitua pelo ID do técnico logado
                $query = "SELECT nome FROM tecnicos WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $tecnicoId);
                $stmt->execute();
                $stmt->bind_result($nomeTecnico);
                $stmt->fetch();
                $stmt->close();
            ?>
            <h4>Bem-vindo, Técnico <?php echo htmlspecialchars($nomeTecnico, ENT_QUOTES, 'UTF-8'); ?></h4>
            <?php
                $queryMatricula = "SELECT matricula FROM tecnicos WHERE id = ?";
                $stmtMatricula = $conn->prepare($queryMatricula);
                $stmtMatricula->bind_param("i", $tecnicoId);
                $stmtMatricula->execute();
                $stmtMatricula->bind_result($matriculaTecnico);
                $stmtMatricula->fetch();
                $stmtMatricula->close();
            ?>
            <?php
                $queryCarro = "SELECT carro_do_dia FROM tecnicos WHERE id = ?";
                $stmtCarro = $conn->prepare($queryCarro);
                $stmtCarro->bind_param("i", $tecnicoId);
                $stmtCarro->execute();
                $stmtCarro->bind_result($carroDoDia);
                $stmtCarro->fetch();
                $stmtCarro->close();
            ?>
            <p>Matrícula: <?php echo htmlspecialchars($matriculaTecnico, ENT_QUOTES, 'UTF-8'); ?> | Carro: <?php echo htmlspecialchars($carroDoDia, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <h4 class="mb-4">Chamados Recebidos</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Descrição</th>
                    <th>Prioridade</th>
                    <th>Status</th>
                    <th>Classificação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    $queryChamados = "SELECT id, nome_usuario, descricao, prioridade, status, categoria FROM chamados";
                    $resultChamados = $conn->query($queryChamados);

                    if (!$resultChamados) {
                        echo "<tr><td colspan='7'>Erro ao executar a consulta: " . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8') . "</td></tr>";
                    } elseif ($resultChamados->num_rows > 0) {
                        while ($row = $resultChamados->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['nome_usuario'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['descricao'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($row['prioridade'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td><span class='badge " . ($row['status'] == 'Pendente' ? 'badge-warning' : ($row['status'] == 'Em Andamento' ? 'badge-info' : 'badge-success')) . "'>" . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . "</span></td>";
                            echo "<td>
                                    <select class='form-control form-control-sm'>
                                        <option value='cancelado'" . ($row['categoria'] == 'cancelado' ? ' selected' : '') . " style='background-color: red; color: white;'>Cancelado</option>
                                        <option value='resolvido'" . ($row['categoria'] == 'resolvido' ? ' selected' : '') . " style='background-color: green; color: white;'>Resolvido</option>
                                        <option value='aberto'" . ($row['categoria'] == 'aberto' ? ' selected' : '') . " style='background-color: green; color: white;'>Aberto</option>
                                        <option value='pendente'" . ($row['categoria'] == 'pendente' ? ' selected' : '') . " style='background-color: yellow;'>Pendente</option>
                                    </select>
                                  </td>";
                            echo "<td>
                                    <button class='btn btn-success btn-sm' data-toggle='modal' data-target='#modalAlterar' 
                                            onclick='preencherModalAlterar(this)'>Alterar Chamado</button>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>Nenhum chamado encontrado.</td></tr>";
                    }
                    ?>

                        <!-- Modal de Alterar Chamado -->
                        <div class="modal fade" id="modalAlterar" tabindex="-1" role="dialog" aria-labelledby="modalAlterarLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalAlterarLabel">Alterar Chamado</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="POST" action="atualizarChamado.php">
                                        <div class="modal-body">
                                            <input type="hidden" name="chamado_id" id="alterarChamadoId" value="">
                                            <div class="form-group">
                                                <label for="alterarPrioridade">Prioridade:</label>
                                                <select class="form-control" name="prioridade" id="alterarPrioridade">
                                                    <option value="Baixa">Baixa</option>
                                                    <option value="Média">Média</option>
                                                    <option value="Alta">Alta</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="alterarStatus">Status:</label>
                                                <select class="form-control" name="status" id="alterarStatus">
                                                    <option value="Pendente">Pendente</option>
                                                    <option value="Em Andamento">Em Andamento</option>
                                                    <option value="Concluído">Concluído</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="alterarClassificacao">Classificação:</label>
                                                <select class="form-control" name="classificacao" id="alterarClassificacao">
                                                    <option value="verde">Fácil</option>
                                                    <option value="amarelo">Intermediário</option>
                                                    <option value="vermelho">Difícil</option>
                                                    <option value="preto">Substituição</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Modal de Visualizar Chamado -->
        <div class="modal fade" id="modalVisualizar" tabindex="-1" role="dialog" aria-labelledby="modalVisualizarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalVisualizarLabel">Visualizar Chamado</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5 class="mb-3">Detalhes do Chamado</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> <span id="modalCliente"></span></p>
                        <p><strong>Descrição:</strong> <span id="modalDescricao"></span></p>
                        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                        <p><strong>Prioridade:</strong> <span id="modalPrioridade"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Data de Fechamento:</strong> <span id="modalDataFechamento"></span></p>
                        <p><strong>Categoria:</strong> <span id="modalCategoria"></span></p>
                        <p><strong>Pagamento:</strong> <span id="modalPagamentoValor"></span> (<span id="modalPagamentoForma"></span>)</p>
                    </div>
                </div>
                <hr>
                <h5 class="mb-3">Atualizar Informações</h5>
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inputDataFechamento">Alterar Data de Fechamento:</label>
                                <input type="date" class="form-control" id="inputDataFechamento" name="data_fechamento">
                            </div>
                            <div class="form-group">
                                <label for="inputCategoria">Alterar Categoria:</label>
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
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inputPagamentoValor">Alterar Pagamento - Valor:</label>
                                <input type="number" class="form-control" id="inputPagamentoValor" name="pagamento_valor" step="0.01">
                            </div>
                            <div class="form-group">
                                <label for="inputPagamentoForma">Alterar Pagamento - Forma:</label>
                                <select class="form-control" id="inputPagamentoForma" name="pagamento_forma">
                                    <option value="PIX">PIX</option>
                                    <option value="Dinheiro">Dinheiro</option>
                                    <option value="Cartão Crédito">Cartão de Crédito</option>
                                    <option value="Cartão Débito">Cartão de Débito</option>
                                    <option value="Boleto">Boleto</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mensagemCliente">Mensagem para o cliente:</label>
                        <textarea class="form-control" id="mensagemCliente" rows="3" placeholder="Digite uma mensagem..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="fotoChamado">Adicionar Foto:</label>
                        <input type="file" class="form-control-file" id="fotoChamado">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary">Enviar Atualização</button>
            </div>
        </div>
    </div>
</div>

    </div>

    <script>
        function preencherModal(button) {
            const row = button.closest('tr');
            const cliente = row.cells[1].innerText;
            const descricao = row.cells[2].innerText;
            const prioridade = row.cells[3].querySelector('select').value;
            const status = row.cells[4].innerText;

            document.getElementById('modalCliente').innerText = cliente;
            document.getElementById('modalDescricao').innerText = descricao;
            document.getElementById('modalPrioridade').innerText = prioridade;
            document.getElementById('modalStatus').innerText = status;
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
