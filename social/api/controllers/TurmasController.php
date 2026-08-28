<?php
class TurmasController
{
    private PDO $db;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->db = Database::connect();
    }

    // GET /turmas
    public function index(): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria', 'professor');

        $role   = AuthMiddleware::userRole();
        $userId = AuthMiddleware::userId();

        if ($role === 'professor') {
            // Professor vê apenas suas turmas
            $stmt = $this->db->prepare(
                'SELECT t.id, t.nome, t.ano_letivo, t.ativo
                 FROM turmas t
                 JOIN professor_turmas pt ON pt.turma_id = t.id
                 WHERE pt.professor_id = ? AND t.ativo = 1
                 ORDER BY t.nome'
            );
            $stmt->execute([$userId]);
        } else {
            $stmt = $this->db->query('SELECT id, nome, ano_letivo, ativo FROM turmas WHERE ativo = 1 ORDER BY nome');
        }

        Response::json(['turmas' => $stmt->fetchAll()]);
    }

    // POST /turmas
    public function store(): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)
            ->required('nome', 'Nome')
            ->required('ano_letivo', 'Ano letivo')
            ->integer('ano_letivo')
            ->failOrContinue();

        $nome      = trim($body['nome']);
        $anoLetivo = (int) $body['ano_letivo'];

        $check = $this->db->prepare('SELECT id FROM turmas WHERE nome = ? AND ano_letivo = ?');
        $check->execute([$nome, $anoLetivo]);
        if ($check->fetch()) Response::error('Turma já existe neste ano.', 'TURMA_JA_EXISTE', 409);

        $this->db->prepare('INSERT INTO turmas (nome, ano_letivo) VALUES (?, ?)')->execute([$nome, $anoLetivo]);
        $id = (int) $this->db->lastInsertId();

        Response::json(['id' => $id, 'nome' => $nome, 'ano_letivo' => $anoLetivo, 'ativo' => true], 201);
    }

    // PATCH /turmas/{id}
    public function update(int $id): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $stmt = $this->db->prepare('SELECT * FROM turmas WHERE id = ?');
        $stmt->execute([$id]);
        $turma = $stmt->fetch();
        if (!$turma) Response::notFound('Turma');

        $sets = [];
        $params = [];

        if (!empty($body['nome']))              { $sets[] = 'nome = ?';      $params[] = trim($body['nome']); }
        if (isset($body['ativo']))              { $sets[] = 'ativo = ?';     $params[] = (int)(bool)$body['ativo']; }

        if (empty($sets)) Response::error('Nenhum campo para atualizar.', 'DADOS_INVALIDOS', 400);

        $params[] = $id;
        $this->db->prepare('UPDATE turmas SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);

        $stmt->execute([$id]);
        Response::json($stmt->fetch());
    }
}
