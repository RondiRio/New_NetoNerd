<?php
class OcorrenciasController
{
    private PDO $db;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->db = Database::connect();
    }

    // POST /ocorrencias
    public function store(): void
    {
        AuthMiddleware::requireRole('professor');
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $professorId = AuthMiddleware::userId();

        Validator::make($body)
            ->required('aluno_id',   'Aluno')
            ->required('tipo',       'Tipo')
            ->required('gravidade',  'Gravidade')
            ->required('descricao',  'Descrição')
            ->integer('aluno_id')
            ->in('tipo',      ['comportamento', 'saude', 'acidente', 'outro'])
            ->in('gravidade', ['leve', 'moderada', 'grave'])
            ->failOrContinue();

        // Verifica se professor tem acesso à turma do aluno
        $alunoStmt = $this->db->prepare(
            'SELECT a.id, a.nome, a.turma_id FROM alunos a WHERE a.id = ? AND a.ativo = 1'
        );
        $alunoStmt->execute([(int) $body['aluno_id']]);
        $aluno = $alunoStmt->fetch();
        if (!$aluno) Response::notFound('Aluno');

        $check = $this->db->prepare(
            'SELECT 1 FROM professor_turmas WHERE professor_id = ? AND turma_id = ?'
        );
        $check->execute([$professorId, $aluno['turma_id']]);
        if (!$check->fetch()) Response::forbidden();

        $this->db->prepare(
            'INSERT INTO ocorrencias (aluno_id, professor_id, tipo, gravidade, descricao)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            (int) $body['aluno_id'],
            $professorId,
            $body['tipo'],
            $body['gravidade'],
            trim($body['descricao']),
        ]);
        $id = (int) $this->db->lastInsertId();

        $this->showById($id, 201);
    }

    // GET /ocorrencias
    public function index(): void
    {
        $role   = AuthMiddleware::userRole();
        $userId = AuthMiddleware::userId();

        $where  = ['1=1'];
        $params = [];

        if ($role === 'professor') {
            $where[]  = 'o.professor_id = ?';
            $params[] = $userId;
        } elseif ($role === 'responsavel') {
            // Vê ocorrências dos filhos vinculados aprovados
            $where[]  = 'o.aluno_id IN (
                            SELECT vr.aluno_id FROM vinculos_responsavel vr
                            WHERE vr.responsavel_id = ? AND vr.status = "aprovado"
                         )';
            $params[] = $userId;
            // Responsável só vê aprovadas
            $where[]  = 'o.status = "aprovada"';
        } else {
            AuthMiddleware::requireRole('admin', 'secretaria');
        }

        if (!empty($_GET['aluno_id']))  { $where[] = 'o.aluno_id = ?';   $params[] = (int) $_GET['aluno_id']; }
        if (!empty($_GET['status']))    { $where[] = 'o.status = ?';     $params[] = $_GET['status']; }
        if (!empty($_GET['tipo']))      { $where[] = 'o.tipo = ?';       $params[] = $_GET['tipo']; }
        if (!empty($_GET['gravidade'])) { $where[] = 'o.gravidade = ?';  $params[] = $_GET['gravidade']; }
        if (!empty($_GET['de']))        { $where[] = 'o.criado_em >= ?'; $params[] = $_GET['de'] . ' 00:00:00'; }
        if (!empty($_GET['ate']))       { $where[] = 'o.criado_em <= ?'; $params[] = $_GET['ate'] . ' 23:59:59'; }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $limite = min(50, max(1, (int) ($_GET['limite'] ?? 20)));
        $offset = ($pagina - 1) * $limite;

        $total = $this->db->prepare("SELECT COUNT(*) FROM ocorrencias o $whereClause");
        $total->execute($params);
        $totalRows = (int) $total->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT o.id, o.tipo, o.gravidade, o.descricao, o.status,
                    o.criado_em, o.avaliado_em,
                    a.id AS aid, a.nome AS anome,
                    t.id AS tid, t.nome AS tnome, t.ano_letivo,
                    up.id AS pid, up.nome AS pnome,
                    ua.id AS avid, ua.nome AS avnome
             FROM ocorrencias o
             JOIN alunos a       ON a.id = o.aluno_id
             JOIN turmas t       ON t.id = a.turma_id
             JOIN usuarios up    ON up.id = o.professor_id
             LEFT JOIN usuarios ua ON ua.id = o.avaliado_por
             $whereClause
             ORDER BY o.criado_em DESC
             LIMIT $limite OFFSET $offset"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        Response::json([
            'ocorrencias' => array_map(fn($r) => $this->format($r), $rows),
            'meta'        => ['total' => $totalRows, 'pagina' => $pagina, 'limite' => $limite],
        ]);
    }

    // GET /ocorrencias/{id}
    public function show(int $id): void
    {
        $role   = AuthMiddleware::userRole();
        $userId = AuthMiddleware::userId();

        $stmt = $this->db->prepare(
            'SELECT o.id, o.tipo, o.gravidade, o.descricao, o.status,
                    o.criado_em, o.avaliado_em,
                    a.id AS aid, a.nome AS anome,
                    t.id AS tid, t.nome AS tnome, t.ano_letivo,
                    up.id AS pid, up.nome AS pnome,
                    ua.id AS avid, ua.nome AS avnome
             FROM ocorrencias o
             JOIN alunos a       ON a.id = o.aluno_id
             JOIN turmas t       ON t.id = a.turma_id
             JOIN usuarios up    ON up.id = o.professor_id
             LEFT JOIN usuarios ua ON ua.id = o.avaliado_por
             WHERE o.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) Response::notFound('Ocorrência');

        // Controle de acesso por role
        if ($role === 'professor' && (int) $row['pid'] !== $userId) {
            Response::forbidden();
        }
        if ($role === 'responsavel') {
            if ($row['status'] !== 'aprovada') Response::forbidden();
            $check = $this->db->prepare(
                'SELECT 1 FROM vinculos_responsavel WHERE responsavel_id = ? AND aluno_id = ? AND status = "aprovado"'
            );
            $check->execute([$userId, $row['aid']]);
            if (!$check->fetch()) Response::forbidden();
        }

        Response::json($this->format($row));
    }

    // PATCH /ocorrencias/{id}/avaliar
    public function avaliar(int $id): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)
            ->required('decisao', 'Decisão')
            ->in('decisao', ['aprovada', 'rejeitada'])
            ->failOrContinue();

        $stmt = $this->db->prepare(
            'SELECT o.*, a.nome AS aluno_nome FROM ocorrencias o
             JOIN alunos a ON a.id = o.aluno_id
             WHERE o.id = ?'
        );
        $stmt->execute([$id]);
        $oc = $stmt->fetch();
        if (!$oc) Response::notFound('Ocorrência');
        if ($oc['status'] !== 'pendente') Response::error('Ocorrência já foi avaliada.', 'OCORRENCIA_JA_AVALIADA', 409);

        $decisao   = $body['decisao'];
        $agora     = date('Y-m-d H:i:s');
        $avaliador = AuthMiddleware::userId();

        $this->db->prepare(
            'UPDATE ocorrencias SET status = ?, avaliado_em = ?, avaliado_por = ? WHERE id = ?'
        )->execute([$decisao, $agora, $avaliador, $id]);

        // Se aprovada, notifica todos os responsáveis aprovados do aluno
        if ($decisao === 'aprovada') {
            $tokens = $this->getTokensDoAluno($oc['aluno_id']);
            Push::enviarOcorrencia(
                $tokens,
                '⚠️ Ocorrência registrada',
                "Uma ocorrência foi registrada para {$oc['aluno_nome']}.",
                $id
            );
        }

        Response::json(['id' => $id, 'status' => $decisao, 'avaliado_em' => $agora]);
    }

    // ---------------------------------------------------------------
    private function getTokensDoAluno(int $alunoId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT pt.token
             FROM push_tokens pt
             JOIN vinculos_responsavel vr ON vr.responsavel_id = pt.usuario_id
             WHERE pt.ativo = 1 AND vr.status = "aprovado" AND vr.aluno_id = ?'
        );
        $stmt->execute([$alunoId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function showById(int $id, int $status = 200): void
    {
        $stmt = $this->db->prepare(
            'SELECT o.id, o.tipo, o.gravidade, o.descricao, o.status,
                    o.criado_em, o.avaliado_em,
                    a.id AS aid, a.nome AS anome,
                    t.id AS tid, t.nome AS tnome, t.ano_letivo,
                    up.id AS pid, up.nome AS pnome,
                    ua.id AS avid, ua.nome AS avnome
             FROM ocorrencias o
             JOIN alunos a       ON a.id = o.aluno_id
             JOIN turmas t       ON t.id = a.turma_id
             JOIN usuarios up    ON up.id = o.professor_id
             LEFT JOIN usuarios ua ON ua.id = o.avaliado_por
             WHERE o.id = ?'
        );
        $stmt->execute([$id]);
        Response::json($this->format($stmt->fetch()), $status);
    }

    private function format(array $r): array
    {
        return [
            'id'          => (int) $r['id'],
            'tipo'        => $r['tipo'],
            'gravidade'   => $r['gravidade'],
            'descricao'   => $r['descricao'],
            'status'      => $r['status'],
            'aluno'       => ['id' => (int) $r['aid'], 'nome' => $r['anome'],
                              'turma' => ['id' => (int) $r['tid'], 'nome' => $r['tnome'], 'ano_letivo' => (int) $r['ano_letivo']]],
            'professor'   => ['id' => (int) $r['pid'], 'nome' => $r['pnome']],
            'avaliado_por'=> $r['avid'] ? ['id' => (int) $r['avid'], 'nome' => $r['avnome']] : null,
            'criado_em'   => $r['criado_em'],
            'avaliado_em' => $r['avaliado_em'],
        ];
    }
}
