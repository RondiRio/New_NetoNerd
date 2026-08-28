<?php
class MeController
{
    private PDO $db;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->db = Database::connect();
    }

    // GET /me
    public function show(): void
    {
        $stmt = $this->db->prepare(
            'SELECT id, nome, email, role, foto_url, criado_em FROM usuarios WHERE id = ?'
        );
        $stmt->execute([AuthMiddleware::userId()]);
        $u = $stmt->fetch();
        if (!$u) Response::notFound('Usuário');
        Response::json($u);
    }

    // PATCH /me
    public function update(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = AuthMiddleware::userId();
        $sets = [];
        $params = [];

        if (!empty($body['nome'])) {
            $sets[]   = 'nome = ?';
            $params[] = trim($body['nome']);
        }
        if (array_key_exists('foto_url', $body)) {
            $sets[]   = 'foto_url = ?';
            $params[] = $body['foto_url'] ?: null;
        }

        if (empty($sets)) Response::error('Nenhum campo para atualizar.', 'DADOS_INVALIDOS', 400);

        $params[] = $id;
        $this->db->prepare('UPDATE usuarios SET ' . implode(', ', $sets) . ' WHERE id = ?')
                 ->execute($params);

        $this->show();
    }

    // PATCH /me/senha
    public function senha(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)
            ->required('senha_atual', 'Senha atual')
            ->required('nova_senha', 'Nova senha')
            ->min('nova_senha', 8, 'Nova senha')
            ->failOrContinue();

        $id   = AuthMiddleware::userId();
        $stmt = $this->db->prepare('SELECT senha_hash FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $u = $stmt->fetch();

        if (!$u['senha_hash']) {
            Response::error('Esta conta usa login pelo Google e não possui senha.', 'CONTA_GOOGLE_SEM_SENHA', 400);
        }
        if (!password_verify($body['senha_atual'], $u['senha_hash'])) {
            Response::error('Senha atual incorreta.', 'SENHA_ATUAL_INCORRETA', 400);
        }

        $hash = password_hash($body['nova_senha'], PASSWORD_BCRYPT, ['cost' => 12]);
        $this->db->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')->execute([$hash, $id]);

        Response::json(['mensagem' => 'Senha alterada com sucesso.']);
    }
}
