<?php
class AtividadesController
{
    private PDO $db;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->db = Database::connect();
    }

    // GET /atividades
    public function index(): void
    {
        $role   = AuthMiddleware::userRole();
        $userId = AuthMiddleware::userId();

        $where  = ['a.status != "cancelada" OR 1=1']; // removido abaixo
        $where  = [];
        $params = [];

        if ($role === 'responsavel') {
            // Vê atividades das turmas dos filhos aprovados + toda a creche
            $where[]  = '(a.turma_id IN (
                            SELECT al.turma_id FROM vinculos_responsavel vr
                            JOIN alunos al ON al.id = vr.aluno_id
                            WHERE vr.responsavel_id = ? AND vr.status = "aprovado"
                          ) OR a.turma_id IS NULL)';
            $params[] = $userId;
        } elseif ($role === 'professor') {
            $where[]  = '(a.turma_id IN (SELECT turma_id FROM professor_turmas WHERE professor_id = ?) OR a.turma_id IS NULL)';
            $params[] = $userId;
        }

        if (!empty($_GET['turma_id']))  { $where[] = 'a.turma_id = ?';    $params[] = (int) $_GET['turma_id']; }
        if (!empty($_GET['status']))    { $where[] = 'a.status = ?';      $params[] = $_GET['status']; }
        if (!empty($_GET['de']))        { $where[] = 'a.data_atividade >= ?'; $params[] = $_GET['de'] . ' 00:00:00'; }
        if (!empty($_GET['ate']))       { $where[] = 'a.data_atividade <= ?'; $params[] = $_GET['ate'] . ' 23:59:59'; }

        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $limite = min(50, max(1, (int) ($_GET['limite'] ?? 20)));
        $offset = ($pagina - 1) * $limite;

        $total = $this->db->prepare("SELECT COUNT(*) FROM atividades a $whereClause");
        $total->execute($params);
        $totalRows = (int) $total->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT a.id, a.titulo, a.descricao, a.data_atividade, a.urgente, a.status,
                    a.criado_em, a.atualizado_em,
                    t.id AS tid, t.nome AS tnome, t.ano_letivo,
                    u.id AS uid, u.nome AS unome
             FROM atividades a
             LEFT JOIN turmas t ON t.id = a.turma_id
             JOIN usuarios u ON u.id = a.criado_por
             $whereClause
             ORDER BY a.data_atividade ASC
             LIMIT $limite OFFSET $offset"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        Response::json([
            'atividades' => array_map(fn($r) => $this->format($r), $rows),
            'meta'       => ['total' => $totalRows, 'pagina' => $pagina, 'limite' => $limite],
        ]);
    }

    // POST /atividades
    public function store(): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)
            ->required('titulo', 'Título')
            ->required('data_atividade', 'Data da atividade')
            ->failOrContinue();

        $turmaId  = !empty($body['turma_id']) ? (int) $body['turma_id'] : null;
        $urgente  = !empty($body['urgente']) ? 1 : 0;
        $criadoPor = AuthMiddleware::userId();

        $this->db->prepare(
            'INSERT INTO atividades (titulo, descricao, data_atividade, urgente, turma_id, criado_por)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([
            trim($body['titulo']),
            trim($body['descricao'] ?? ''),
            $body['data_atividade'],
            $urgente,
            $turmaId,
            $criadoPor,
        ]);
        $id = (int) $this->db->lastInsertId();

        // Agenda notificações
        $this->agendarNotificacoes($id, $turmaId, $urgente);

        $this->showById($id, 201);
    }

    // GET /atividades/{id}
    public function show(int $id): void
    {
        $role   = AuthMiddleware::userRole();
        $userId = AuthMiddleware::userId();

        $stmt = $this->db->prepare(
            'SELECT a.id, a.titulo, a.descricao, a.data_atividade, a.urgente, a.status,
                    a.criado_em, a.atualizado_em,
                    t.id AS tid, t.nome AS tnome, t.ano_letivo,
                    u.id AS uid, u.nome AS unome
             FROM atividades a
             LEFT JOIN turmas t ON t.id = a.turma_id
             JOIN usuarios u ON u.id = a.criado_por
             WHERE a.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) Response::notFound('Atividade');

        $data = $this->format($row);

        if ($role === 'responsavel') {
            // Registra leitura
            $this->db->prepare(
                'INSERT IGNORE INTO leituras_atividade (atividade_id, responsavel_id) VALUES (?, ?)'
            )->execute([$id, $userId]);

            // Adiciona campo lida
            $leitura = $this->db->prepare(
                'SELECT lido_em FROM leituras_atividade WHERE atividade_id = ? AND responsavel_id = ?'
            );
            $leitura->execute([$id, $userId]);
            $l = $leitura->fetch();
            $data['lida']    = (bool) $l;
            $data['lida_em'] = $l['lido_em'] ?? null;
        }

        Response::json($data);
    }

    // PATCH /atividades/{id}
    public function update(int $id): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $stmt = $this->db->prepare('SELECT * FROM atividades WHERE id = ?');
        $stmt->execute([$id]);
        $at = $stmt->fetch();
        if (!$at) Response::notFound('Atividade');
        if ($at['status'] === 'cancelada') Response::error('Não é possível editar atividade cancelada.', 'ATIVIDADE_CANCELADA', 409);

        $sets = []; $params = [];
        $reagendou = false;
        $torouUrgente = false;

        if (!empty($body['titulo']))          { $sets[] = 'titulo = ?';         $params[] = trim($body['titulo']); }
        if (isset($body['descricao']))        { $sets[] = 'descricao = ?';      $params[] = trim($body['descricao']); }
        if (isset($body['turma_id']))         { $sets[] = 'turma_id = ?';       $params[] = $body['turma_id'] ? (int)$body['turma_id'] : null; }
        if (!empty($body['data_atividade'])) {
            $sets[]    = 'data_atividade = ?';
            $params[]  = $body['data_atividade'];
            if ($body['data_atividade'] !== $at['data_atividade']) {
                $sets[]   = 'status = ?';
                $params[] = 'reagendada';
                $reagendou = true;
            }
        }
        if (isset($body['urgente']) && $body['urgente'] && !$at['urgente']) {
            $sets[]       = 'urgente = ?';
            $params[]     = 1;
            $torouUrgente = true;
        }

        if (!empty($sets)) {
            $params[] = $id;
            $this->db->prepare('UPDATE atividades SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
        }

        $turmaId = $body['turma_id'] ?? $at['turma_id'];

        if ($reagendou || $torouUrgente) {
            // Cancela notificações antigas não enviadas
            $this->db->prepare(
                'DELETE FROM notificacoes_agendadas WHERE atividade_id = ? AND enviado = 0'
            )->execute([$id]);

            // Envia push imediato de reagendamento ou urgente
            $tokens = $this->getTokensDaTurma($turmaId);
            if ($reagendou) {
                Push::enviarAtividade($tokens, '🔄 Atividade reagendada', ""{$at['titulo']}" foi reagendada. Veja a nova data no app.", $id);
            } else {
                Push::enviarAtividade($tokens, '🚨 Aviso urgente', $at['titulo'], $id, true);
            }

            // Re-agenda
            $this->agendarNotificacoes($id, $turmaId, $torouUrgente ? 1 : (int)$at['urgente']);
        }

        $this->showById($id);
    }

    // DELETE /atividades/{id}
    public function cancel(int $id): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');

        $stmt = $this->db->prepare('SELECT * FROM atividades WHERE id = ?');
        $stmt->execute([$id]);
        $at = $stmt->fetch();
        if (!$at) Response::notFound('Atividade');

        $this->db->prepare('UPDATE atividades SET status = "cancelada" WHERE id = ?')->execute([$id]);
        $this->db->prepare('DELETE FROM notificacoes_agendadas WHERE atividade_id = ? AND enviado = 0')->execute([$id]);

        // Push imediato de cancelamento
        $tokens = $this->getTokensDaTurma($at['turma_id']);
        Push::enviarAtividade($tokens, '❌ Atividade cancelada', ""{$at['titulo']}" foi cancelada.", $id);

        Response::json(['mensagem' => 'Atividade cancelada.', 'id' => $id]);
    }

    // GET /atividades/{id}/leituras
    public function leituras(int $id): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');

        $stmt = $this->db->prepare('SELECT id, turma_id FROM atividades WHERE id = ?');
        $stmt->execute([$id]);
        $at = $stmt->fetch();
        if (!$at) Response::notFound('Atividade');

        // Total de responsáveis afetados
        $totalStmt = $this->db->prepare(
            'SELECT COUNT(DISTINCT vr.responsavel_id)
             FROM vinculos_responsavel vr
             JOIN alunos a ON a.id = vr.aluno_id
             WHERE vr.status = "aprovado"
             AND (a.turma_id = ? OR ? IS NULL)'
        );
        $totalStmt->execute([$at['turma_id'], $at['turma_id']]);
        $total = (int) $totalStmt->fetchColumn();

        $leramStmt = $this->db->prepare(
            'SELECT u.id, u.nome, al.nome AS aluno_nome, la.lido_em
             FROM leituras_atividade la
             JOIN usuarios u ON u.id = la.responsavel_id
             JOIN vinculos_responsavel vr ON vr.responsavel_id = la.responsavel_id
             JOIN alunos al ON al.id = vr.aluno_id
             WHERE la.atividade_id = ? AND vr.status = "aprovado"'
        );
        $leramStmt->execute([$id]);
        $leram = $leramStmt->fetchAll();

        Response::json([
            'total_responsaveis' => $total,
            'total_leram'        => count($leram),
            'leituras'           => array_map(fn($r) => [
                'responsavel' => ['id' => $r['id'], 'nome' => $r['nome']],
                'aluno'       => ['nome' => $r['aluno_nome']],
                'lido_em'     => $r['lido_em'],
            ], $leram),
        ]);
    }

    // ---------------------------------------------------------------
    private function agendarNotificacoes(int $atividadeId, ?int $turmaId, int $urgente): void
    {
        // Busca todos responsáveis aprovados afetados
        $stmt = $this->db->prepare(
            'SELECT DISTINCT vr.responsavel_id, vr.antecedencia_dias
             FROM vinculos_responsavel vr
             JOIN alunos a ON a.id = vr.aluno_id
             WHERE vr.status = "aprovado"
             AND (a.turma_id = ? OR ? IS NULL)'
        );
        $stmt->execute([$turmaId, $turmaId]);
        $responsaveis = $stmt->fetchAll();

        $cfg     = $this->db->query('SELECT antecedencia_padrao_dias, data_atividade FROM configuracoes_creche LIMIT 1')->fetch();
        $padrao  = (int) ($cfg['antecedencia_padrao_dias'] ?? 3);

        $dataAt  = $this->db->prepare('SELECT data_atividade FROM atividades WHERE id = ?');
        $dataAt->execute([$atividadeId]);
        $dataAtividade = $dataAt->fetchColumn();

        foreach ($responsaveis as $resp) {
            if ($urgente) {
                $enviarEm = date('Y-m-d H:i:s');
            } else {
                $dias     = $resp['antecedencia_dias'] !== null ? (int)$resp['antecedencia_dias'] : $padrao;
                $enviarEm = date('Y-m-d H:i:s', strtotime($dataAtividade) - $dias * 86400);
            }

            $this->db->prepare(
                'INSERT IGNORE INTO notificacoes_agendadas (atividade_id, responsavel_id, enviar_em)
                 VALUES (?, ?, ?)'
            )->execute([$atividadeId, $resp['responsavel_id'], $enviarEm]);
        }

        // Se urgente, dispara push agora
        if ($urgente) {
            $titleStmt = $this->db->prepare('SELECT titulo FROM atividades WHERE id = ?');
            $titleStmt->execute([$atividadeId]);
            $titulo = $titleStmt->fetchColumn();

            $tokens = $this->getTokensDaTurma($turmaId);
            Push::enviarAtividade($tokens, '🚨 Aviso urgente', $titulo, $atividadeId, true);
        }
    }

    private function getTokensDaTurma(?int $turmaId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT pt.token
             FROM push_tokens pt
             JOIN vinculos_responsavel vr ON vr.responsavel_id = pt.usuario_id
             JOIN alunos a ON a.id = vr.aluno_id
             WHERE pt.ativo = 1 AND vr.status = "aprovado"
             AND (a.turma_id = ? OR ? IS NULL)'
        );
        $stmt->execute([$turmaId, $turmaId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function showById(int $id, int $status = 200): void
    {
        $stmt = $this->db->prepare(
            'SELECT a.id, a.titulo, a.descricao, a.data_atividade, a.urgente, a.status,
                    a.criado_em, a.atualizado_em,
                    t.id AS tid, t.nome AS tnome, t.ano_letivo,
                    u.id AS uid, u.nome AS unome
             FROM atividades a
             LEFT JOIN turmas t ON t.id = a.turma_id
             JOIN usuarios u ON u.id = a.criado_por
             WHERE a.id = ?'
        );
        $stmt->execute([$id]);
        Response::json($this->format($stmt->fetch()), $status);
    }

    private function format(array $r): array
    {
        return [
            'id'             => (int) $r['id'],
            'titulo'         => $r['titulo'],
            'descricao'      => $r['descricao'],
            'data_atividade' => $r['data_atividade'],
            'urgente'        => (bool) $r['urgente'],
            'status'         => $r['status'],
            'turma'          => $r['tid'] ? ['id' => (int)$r['tid'], 'nome' => $r['tnome'], 'ano_letivo' => (int)$r['ano_letivo']] : null,
            'criado_por'     => ['id' => (int)$r['uid'], 'nome' => $r['unome']],
            'criado_em'      => $r['criado_em'],
            'atualizado_em'  => $r['atualizado_em'],
        ];
    }
}
