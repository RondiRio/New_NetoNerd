<?php
class UsuariosController
{
    private PDO $db;

    public function __construct()
    {
        AuthMiddleware::handle();
        AuthMiddleware::requireRole('admin', 'secretaria');
        $this->db = Database::connect();
    }

    // GET /usuarios
    public function index(): void
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($_GET['role']))  { $where[] = 'role = ?';  $params[] = $_GET['role']; }
        if (!empty($_GET['ativo'])) { $where[] = 'ativo = ?'; $params[] = $_GET['ativo'] === 'false' ? 0 : 1; }
        if (!empty($_GET['q']))     {
            $where[]  = '(nome LIKE ? OR email LIKE ?)';
            $params[] = '%' . $_GET['q'] . '%';
            $params[] = '%' . $_GET['q'] . '%';
        }

        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $limite = min(100, max(1, (int) ($_GET['limite'] ?? 30)));
        $offset = ($pagina - 1) * $limite;

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $total = $this->db->prepare("SELECT COUNT(*) FROM usuarios $whereClause");
        $total->execute($params);
        $totalRows = (int) $total->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT id, nome, email, role, foto_url, ativo, criado_em
             FROM usuarios
             $whereClause
             ORDER BY nome
             LIMIT $limite OFFSET $offset"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        Response::json([
            'usuarios' => array_map(fn($r) => $this->format($r), $rows),
            'meta'     => ['total' => $totalRows, 'pagina' => $pagina, 'limite' => $limite],
        ]);
    }

    // GET /usuarios/{id}
    public function show(int $id): void
    {
        $stmt = $this->db->prepare(
            'SELECT id, nome, email, role, foto_url, ativo, criado_em FROM usuarios WHERE id = ?'
        );
        $stmt->execute([$id]);
        $u = $stmt->fetch();
        if (!$u) Response::notFound('Usuário');

        Response::json($this->format($u));
    }

    // PATCH /usuarios/{id}
    public function update(int $id): void
    {
        // Secretaria não pode alterar admin
        $target = $this->db->prepare('SELECT role, ativo FROM usuarios WHERE id = ?');
        $target->execute([$id]);
        $u = $target->fetch();
        if (!$u) Response::notFound('Usuário');

        $callerRole = AuthMiddleware::userRole();
        if ($callerRole === 'secretaria' && $u['role'] === 'admin') {
            Response::forbidden();
        }

        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $sets   = [];
        $params = [];

        if (!empty($body['nome']))   { $sets[] = 'nome = ?';  $params[] = trim($body['nome']); }
        if (!empty($body['role'])) {
            $allowed = ['admin', 'secretaria', 'professor', 'responsavel'];
            if (!in_array($body['role'], $allowed, true)) {
                Response::error('Role inválido.', 'ROLE_INVALIDO', 400);
            }
            // Apenas admin pode promover para admin
            if ($body['role'] === 'admin' && $callerRole !== 'admin') Response::forbidden();
            $sets[]   = 'role = ?';
            $params[] = $body['role'];
        }
        if (isset($body['ativo'])) { $sets[] = 'ativo = ?'; $params[] = (int)(bool)$body['ativo']; }

        if (empty($sets)) Response::error('Nenhum campo para atualizar.', 'DADOS_INVALIDOS', 400);

        $params[] = $id;
        $this->db->prepare('UPDATE usuarios SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);

        $this->show($id);
    }

    // GET /usuarios/{id}/turmas  — professores vinculados a turmas
    public function turmas(int $id): void
    {
        $stmt = $this->db->prepare('SELECT role FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $u = $stmt->fetch();
        if (!$u) Response::notFound('Usuário');
        if ($u['role'] !== 'professor') Response::error('Usuário não é professor.', 'NAO_PROFESSOR', 400);

        $stmt = $this->db->prepare(
            'SELECT t.id, t.nome, t.ano_letivo
             FROM professor_turmas pt
             JOIN turmas t ON t.id = pt.turma_id
             WHERE pt.professor_id = ?
             ORDER BY t.nome'
        );
        $stmt->execute([$id]);
        Response::json(['turmas' => $stmt->fetchAll()]);
    }

    // POST /usuarios/{id}/turmas
    public function adicionarTurma(int $id): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        Validator::make($body)->required('turma_id', 'Turma')->integer('turma_id')->failOrContinue();

        $stmt = $this->db->prepare('SELECT role FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $u = $stmt->fetch();
        if (!$u) Response::notFound('Usuário');
        if ($u['role'] !== 'professor') Response::error('Usuário não é professor.', 'NAO_PROFESSOR', 400);

        $turmaId = (int) $body['turma_id'];
        $turma   = $this->db->prepare('SELECT id FROM turmas WHERE id = ? AND ativo = 1');
        $turma->execute([$turmaId]);
        if (!$turma->fetch()) Response::notFound('Turma');

        $this->db->prepare(
            'INSERT IGNORE INTO professor_turmas (professor_id, turma_id) VALUES (?, ?)'
        )->execute([$id, $turmaId]);

        $this->turmas($id);
    }

    // DELETE /usuarios/{id}/turmas/{turmaId}
    public function removerTurma(int $id, int $turmaId): void
    {
        $this->db->prepare(
            'DELETE FROM professor_turmas WHERE professor_id = ? AND turma_id = ?'
        )->execute([$id, $turmaId]);

        $this->turmas($id);
    }

    // ---------------------------------------------------------------
    private function format(array $r): array
    {
        return [
            'id'        => (int) $r['id'],
            'nome'      => $r['nome'],
            'email'     => $r['email'],
            'role'      => $r['role'],
            'foto_url'  => $r['foto_url'],
            'ativo'     => (bool) $r['ativo'],
            'criado_em' => $r['criado_em'],
        ];
    }
}
