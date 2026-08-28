<?php
class VinculosController
{
    private PDO $db;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->db = Database::connect();
    }

    // POST /vinculos
    public function store(): void
    {
        AuthMiddleware::requireRole('responsavel');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)->required('codigo', 'Código')->failOrContinue();

        $codigo    = strtoupper(trim($body['codigo']));
        $respId    = AuthMiddleware::userId();

        $stmt = $this->db->prepare(
            'SELECT cv.id, cv.aluno_id, cv.expira_em, cv.usado
             FROM codigos_vinculo cv WHERE cv.codigo = ?'
        );
        $stmt->execute([$codigo]);
        $cv = $stmt->fetch();

        if (!$cv)                                      Response::error('Código não encontrado.',  'CODIGO_NAO_ENCONTRADO', 404);
        if (strtotime($cv['expira_em']) < time())      Response::error('Código expirado.',         'CODIGO_EXPIRADO',       410);

        // Verifica se vínculo já existe
        $dup = $this->db->prepare('SELECT id, status FROM vinculos_responsavel WHERE responsavel_id = ? AND aluno_id = ?');
        $dup->execute([$respId, $cv['aluno_id']]);
        if ($dup->fetch()) Response::error('Você já possui um vínculo com este aluno.', 'VINCULO_JA_SOLICITADO', 409);

        $this->db->prepare(
            'INSERT INTO vinculos_responsavel (responsavel_id, aluno_id, codigo_vinculo_id, status)
             VALUES (?, ?, ?, ?)'
        )->execute([$respId, $cv['aluno_id'], $cv['id'], 'pendente']);

        $id = (int) $this->db->lastInsertId();

        // Retorna com dados do aluno
        $alunoStmt = $this->db->prepare(
            'SELECT a.id, a.nome, t.id AS tid, t.nome AS tnome, t.ano_letivo
             FROM alunos a JOIN turmas t ON t.id = a.turma_id WHERE a.id = ?'
        );
        $alunoStmt->execute([$cv['aluno_id']]);
        $aluno = $alunoStmt->fetch();

        Response::json([
            'id'           => $id,
            'aluno'        => ['id' => $aluno['id'], 'nome' => $aluno['nome'],
                               'turma' => ['id' => $aluno['tid'], 'nome' => $aluno['tnome'], 'ano_letivo' => $aluno['ano_letivo']]],
            'status'       => 'pendente',
            'solicitado_em'=> date('Y-m-d\TH:i:s'),
        ], 201);
    }

    // GET /vinculos
    public function index(): void
    {
        $role   = AuthMiddleware::userRole();
        $userId = AuthMiddleware::userId();

        if ($role === 'responsavel') {
            $stmt = $this->db->prepare(
                'SELECT vr.id, vr.status, vr.antecedencia_dias, vr.solicitado_em, vr.avaliado_em,
                        a.id AS aid, a.nome AS anome,
                        t.id AS tid, t.nome AS tnome, t.ano_letivo
                 FROM vinculos_responsavel vr
                 JOIN alunos a ON a.id = vr.aluno_id
                 JOIN turmas t ON t.id = a.turma_id
                 WHERE vr.responsavel_id = ?
                 ORDER BY vr.solicitado_em DESC'
            );
            $stmt->execute([$userId]);
        } else {
            AuthMiddleware::requireRole('admin', 'secretaria');
            $where = ['1=1']; $params = [];
            if (!empty($_GET['status']))   { $where[] = 'vr.status = ?';         $params[] = $_GET['status']; }
            if (!empty($_GET['turma_id'])) { $where[] = 'a.turma_id = ?';        $params[] = (int) $_GET['turma_id']; }

            $stmt = $this->db->prepare(
                'SELECT vr.id, vr.status, vr.antecedencia_dias, vr.solicitado_em, vr.avaliado_em,
                        u.id AS uid, u.nome AS unome, u.email AS uemail,
                        a.id AS aid, a.nome AS anome,
                        t.id AS tid, t.nome AS tnome, t.ano_letivo
                 FROM vinculos_responsavel vr
                 JOIN usuarios u ON u.id = vr.responsavel_id
                 JOIN alunos a   ON a.id = vr.aluno_id
                 JOIN turmas t   ON t.id = a.turma_id
                 WHERE ' . implode(' AND ', $where) . '
                 ORDER BY vr.solicitado_em DESC'
            );
            $stmt->execute($params);
        }

        $rows     = $stmt->fetchAll();
        $vinculos = array_map(fn($r) => $this->format($r, $role !== 'responsavel'), $rows);

        Response::json(['vinculos' => $vinculos]);
    }

    // PATCH /vinculos/{id}/avaliar
    public function avaliar(int $id): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)
            ->required('decisao', 'Decisão')
            ->in('decisao', ['aprovado', 'rejeitado'])
            ->failOrContinue();

        $stmt = $this->db->prepare('SELECT * FROM vinculos_responsavel WHERE id = ?');
        $stmt->execute([$id]);
        $v = $stmt->fetch();
        if (!$v) Response::notFound('Vínculo');
        if ($v['status'] !== 'pendente') Response::error('Vínculo já foi avaliado.', 'VINCULO_JA_AVALIADO', 409);

        $decisao  = $body['decisao'];
        $agora    = date('Y-m-d H:i:s');
        $avaliador = AuthMiddleware::userId();

        $this->db->prepare(
            'UPDATE vinculos_responsavel SET status = ?, avaliado_em = ?, avaliado_por = ? WHERE id = ?'
        )->execute([$decisao, $agora, $avaliador, $id]);

        // Se aprovado, agenda notificações pendentes para este responsável
        if ($decisao === 'aprovado') {
            $this->agendarNotificacoesPendentes($v['responsavel_id'], $v['aluno_id']);
        }

        Response::json(['id' => $id, 'status' => $decisao, 'avaliado_em' => $agora]);
    }

    // PATCH /vinculos/{id}/preferencia
    public function preferencia(int $id): void
    {
        AuthMiddleware::requireRole('responsavel');
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = AuthMiddleware::userId();

        $stmt = $this->db->prepare('SELECT * FROM vinculos_responsavel WHERE id = ?');
        $stmt->execute([$id]);
        $v = $stmt->fetch();
        if (!$v) Response::notFound('Vínculo');
        if ((int) $v['responsavel_id'] !== $userId) Response::error('Não é seu vínculo.', 'NAO_SEU_VINCULO', 403);

        $ant = $body['antecedencia_dias'] ?? null;
        if ($ant !== null && !in_array((int)$ant, [0, 1, 3, 7], true)) {
            Response::error('Valor inválido. Permitidos: 0, 1, 3, 7 ou null.', 'VALOR_INVALIDO', 400);
        }

        $this->db->prepare('UPDATE vinculos_responsavel SET antecedencia_dias = ? WHERE id = ?')
                 ->execute([$ant, $id]);

        Response::json(['antecedencia_dias' => $ant]);
    }

    // ---------------------------------------------------------------
    private function agendarNotificacoesPendentes(int $respId, int $alunoId): void
    {
        // Busca atividades futuras ativas da turma do aluno
        $stmt = $this->db->prepare(
            'SELECT a.id AS ativ_id, a.data_atividade, a.urgente
             FROM atividades a
             JOIN alunos al ON al.turma_id = a.turma_id OR a.turma_id IS NULL
             WHERE al.id = ? AND a.status = "ativa" AND a.data_atividade > NOW()'
        );
        $stmt->execute([$alunoId]);
        $atividades = $stmt->fetchAll();

        // Preferência do responsável
        $prefStmt = $this->db->prepare(
            'SELECT antecedencia_dias FROM vinculos_responsavel WHERE responsavel_id = ? AND aluno_id = ? AND status = "aprovado"'
        );
        $prefStmt->execute([$respId, $alunoId]);
        $pref = $prefStmt->fetchColumn();

        if ($pref === false) {
            $cfg  = $this->db->query('SELECT antecedencia_padrao_dias FROM configuracoes_creche LIMIT 1')->fetch();
            $dias = (int) $cfg['antecedencia_padrao_dias'];
        } else {
            $dias = (int) $pref;
        }

        foreach ($atividades as $at) {
            $enviarEm = $at['urgente']
                ? date('Y-m-d H:i:s')
                : date('Y-m-d H:i:s', strtotime($at['data_atividade']) - $dias * 86400);

            $this->db->prepare(
                'INSERT IGNORE INTO notificacoes_agendadas (atividade_id, responsavel_id, enviar_em)
                 VALUES (?, ?, ?)'
            )->execute([$at['ativ_id'], $respId, $enviarEm]);
        }
    }

    private function format(array $r, bool $comResponsavel): array
    {
        $base = [
            'id'               => (int) $r['id'],
            'aluno'            => ['id' => (int) $r['aid'], 'nome' => $r['anome'],
                                   'turma' => ['id' => (int) $r['tid'], 'nome' => $r['tnome'], 'ano_letivo' => (int) $r['ano_letivo']]],
            'status'           => $r['status'],
            'antecedencia_dias'=> $r['antecedencia_dias'] !== null ? (int) $r['antecedencia_dias'] : null,
            'solicitado_em'    => $r['solicitado_em'],
            'avaliado_em'      => $r['avaliado_em'],
        ];

        if ($comResponsavel) {
            $base['responsavel'] = ['id' => (int) $r['uid'], 'nome' => $r['unome'], 'email' => $r['uemail']];
        }

        return $base;
    }
}
