<?php
class AlunosController
{
    private PDO $db;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->db = Database::connect();
    }

    // GET /alunos
    public function index(): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria', 'professor');
        $role   = AuthMiddleware::userRole();
        $userId = AuthMiddleware::userId();

        $where  = ['1=1'];
        $params = [];

        if ($role === 'professor') {
            $where[]  = 'a.turma_id IN (SELECT turma_id FROM professor_turmas WHERE professor_id = ?)';
            $params[] = $userId;
        }
        if (!empty($_GET['turma_id'])) {
            $where[]  = 'a.turma_id = ?';
            $params[] = (int) $_GET['turma_id'];
        }
        if (isset($_GET['ativo'])) {
            $where[]  = 'a.ativo = ?';
            $params[] = $_GET['ativo'] === 'false' ? 0 : 1;
        } else {
            $where[] = 'a.ativo = 1';
        }

        $sql = 'SELECT a.id, a.nome, a.dt_nasc, a.ativo,
                       t.id AS turma_id, t.nome AS turma_nome, t.ano_letivo
                FROM alunos a
                JOIN turmas t ON t.id = a.turma_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY a.nome';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $alunos = array_map(fn($r) => $this->formatAluno($r), $rows);
        Response::json(['alunos' => $alunos]);
    }

    // POST /alunos
    public function store(): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)
            ->required('nome', 'Nome')
            ->required('turma_id', 'Turma')
            ->integer('turma_id')
            ->failOrContinue();

        $turma = $this->db->prepare('SELECT id, nome, ano_letivo FROM turmas WHERE id = ? AND ativo = 1');
        $turma->execute([(int) $body['turma_id']]);
        $turmaRow = $turma->fetch();
        if (!$turmaRow) Response::notFound('Turma');

        $stmt = $this->db->prepare('INSERT INTO alunos (nome, dt_nasc, turma_id) VALUES (?, ?, ?)');
        $stmt->execute([
            trim($body['nome']),
            !empty($body['dt_nasc']) ? $body['dt_nasc'] : null,
            (int) $body['turma_id'],
        ]);
        $id = (int) $this->db->lastInsertId();

        Response::json([
            'id'      => $id,
            'nome'    => trim($body['nome']),
            'dt_nasc' => $body['dt_nasc'] ?? null,
            'turma'   => ['id' => $turmaRow['id'], 'nome' => $turmaRow['nome'], 'ano_letivo' => $turmaRow['ano_letivo']],
            'ativo'   => true,
        ], 201);
    }

    // GET /alunos/{id}
    public function show(int $id): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria', 'professor');
        $role   = AuthMiddleware::userRole();
        $userId = AuthMiddleware::userId();

        $stmt = $this->db->prepare(
            'SELECT a.id, a.nome, a.dt_nasc, a.ativo,
                    t.id AS turma_id, t.nome AS turma_nome, t.ano_letivo
             FROM alunos a JOIN turmas t ON t.id = a.turma_id
             WHERE a.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) Response::notFound('Aluno');

        // Professor só vê alunos da sua turma
        if ($role === 'professor') {
            $check = $this->db->prepare(
                'SELECT 1 FROM professor_turmas WHERE professor_id = ? AND turma_id = ?'
            );
            $check->execute([$userId, $row['turma_id']]);
            if (!$check->fetch()) Response::forbidden();
        }

        Response::json($this->formatAluno($row));
    }

    // PATCH /alunos/{id}
    public function update(int $id): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $stmt = $this->db->prepare('SELECT id FROM alunos WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) Response::notFound('Aluno');

        $sets = []; $params = [];
        if (!empty($body['nome']))     { $sets[] = 'nome = ?';     $params[] = trim($body['nome']); }
        if (!empty($body['turma_id'])) { $sets[] = 'turma_id = ?'; $params[] = (int) $body['turma_id']; }
        if (!empty($body['dt_nasc']))  { $sets[] = 'dt_nasc = ?';  $params[] = $body['dt_nasc']; }
        if (isset($body['ativo']))     { $sets[] = 'ativo = ?';     $params[] = (int)(bool)$body['ativo']; }

        if (empty($sets)) Response::error('Nenhum campo para atualizar.', 'DADOS_INVALIDOS', 400);
        $params[] = $id;
        $this->db->prepare('UPDATE alunos SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);

        $this->show($id);
    }

    // GET /alunos/{id}/codigo-vinculo
    public function getCodigo(int $alunoId): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');

        $stmt = $this->db->prepare('SELECT id FROM alunos WHERE id = ?');
        $stmt->execute([$alunoId]);
        if (!$stmt->fetch()) Response::notFound('Aluno');

        $stmt = $this->db->prepare(
            'SELECT codigo, expira_em, usado FROM codigos_vinculo
             WHERE aluno_id = ? AND expira_em > NOW() AND usado = 0
             ORDER BY criado_em DESC LIMIT 1'
        );
        $stmt->execute([$alunoId]);
        $codigo = $stmt->fetch();

        Response::json($codigo ?: ['codigo' => null]);
    }

    // POST /alunos/{id}/codigo-vinculo
    public function gerarCodigo(int $alunoId): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');

        $stmt = $this->db->prepare('SELECT id FROM alunos WHERE id = ?');
        $stmt->execute([$alunoId]);
        if (!$stmt->fetch()) Response::notFound('Aluno');

        $body       = json_decode(file_get_contents('php://input'), true) ?? [];
        $configStmt = $this->db->query('SELECT validade_codigo_vinculo_dias FROM configuracoes_creche LIMIT 1');
        $config     = $configStmt->fetch();
        $dias       = (int) ($body['validade_dias'] ?? $config['validade_codigo_vinculo_dias'] ?? 7);
        $expira     = date('Y-m-d H:i:s', strtotime("+$dias days"));

        // Gera código único de 8 caracteres
        do {
            $codigo = strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
            $check  = $this->db->prepare('SELECT id FROM codigos_vinculo WHERE codigo = ?');
            $check->execute([$codigo]);
        } while ($check->fetch());

        $this->db->prepare(
            'INSERT INTO codigos_vinculo (aluno_id, codigo, expira_em, criado_por) VALUES (?, ?, ?, ?)'
        )->execute([$alunoId, $codigo, $expira, AuthMiddleware::userId()]);

        Response::json(['codigo' => $codigo, 'expira_em' => $expira], 201);
    }

    private function formatAluno(array $r): array
    {
        return [
            'id'      => (int) $r['id'],
            'nome'    => $r['nome'],
            'dt_nasc' => $r['dt_nasc'],
            'ativo'   => (bool) $r['ativo'],
            'turma'   => ['id' => (int) $r['turma_id'], 'nome' => $r['turma_nome'], 'ano_letivo' => (int) $r['ano_letivo']],
        ];
    }
}
