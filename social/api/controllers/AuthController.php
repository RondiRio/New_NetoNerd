<?php
class AuthController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // POST /auth/registro
    public function registro(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)
            ->required('nome', 'Nome')
            ->required('email', 'E-mail')
            ->email('email')
            ->required('senha', 'Senha')
            ->min('senha', 8, 'Senha')
            ->failOrContinue();

        $nome  = trim($body['nome']);
        $email = strtolower(trim($body['email']));
        $senha = $body['senha'];

        // Verifica duplicidade
        $stmt = $this->db->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            Response::error('E-mail já cadastrado.', 'EMAIL_JA_CADASTRADO', 409);
        }

        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nome, $email, $hash, 'responsavel']);
        $id = (int) $this->db->lastInsertId();

        Response::json([
            'mensagem' => 'Conta criada com sucesso.',
            'usuario'  => ['id' => $id, 'nome' => $nome, 'email' => $email, 'role' => 'responsavel'],
        ], 201);
    }

    // POST /auth/login
    public function login(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)
            ->required('email', 'E-mail')
            ->required('senha', 'Senha')
            ->required('dispositivo_id', 'Dispositivo')
            ->failOrContinue();

        $email       = strtolower(trim($body['email']));
        $senha       = $body['senha'];
        $dispositivoId = trim($body['dispositivo_id']);

        $stmt = $this->db->prepare(
            'SELECT id, nome, email, senha_hash, role, ativo FROM usuarios WHERE email = ?'
        );
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if (!$usuario || !$usuario['senha_hash'] || !password_verify($senha, $usuario['senha_hash'])) {
            Response::error('E-mail ou senha incorretos.', 'CREDENCIAIS_INVALIDAS', 401);
        }

        if (!$usuario['ativo']) {
            Response::error('Conta desativada. Entre em contato com a secretaria.', 'CONTA_INATIVA', 403);
        }

        Response::json($this->gerarTokens($usuario, $dispositivoId));
    }

    // POST /auth/google
    public function google(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)
            ->required('authorization_code', 'Código de autorização')
            ->required('dispositivo_id', 'Dispositivo')
            ->failOrContinue();

        $code          = $body['authorization_code'];
        $dispositivoId = $body['dispositivo_id'];

        // Troca code por token com o Google
        $googleUser = $this->trocaCodeGoogle($code);
        if (!$googleUser) {
            Response::error('Código Google inválido ou expirado.', 'CODIGO_INVALIDO', 400);
        }

        $googleId = $googleUser['sub'];
        $email    = strtolower($googleUser['email']);
        $nome     = $googleUser['name'];

        // Busca por google_id ou email
        $stmt = $this->db->prepare(
            'SELECT id, nome, email, role, ativo FROM usuarios WHERE google_id = ? OR email = ? LIMIT 1'
        );
        $stmt->execute([$googleId, $email]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            // Cria conta nova
            $stmt = $this->db->prepare(
                'INSERT INTO usuarios (nome, email, google_id, role) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$nome, $email, $googleId, 'responsavel']);
            $id = (int) $this->db->lastInsertId();
            $usuario = ['id' => $id, 'nome' => $nome, 'email' => $email, 'role' => 'responsavel', 'ativo' => 1];
        } else {
            // Garante que google_id está salvo
            $this->db->prepare('UPDATE usuarios SET google_id = ? WHERE id = ?')
                     ->execute([$googleId, $usuario['id']]);
        }

        if (!$usuario['ativo']) {
            Response::error('Conta desativada.', 'CONTA_INATIVA', 403);
        }

        Response::json($this->gerarTokens($usuario, $dispositivoId));
    }

    // POST /auth/refresh
    public function refresh(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)
            ->required('refresh_token', 'Refresh token')
            ->required('dispositivo_id', 'Dispositivo')
            ->failOrContinue();

        $token         = $body['refresh_token'];
        $dispositivoId = $body['dispositivo_id'];
        $hash          = JWT::hashRefreshToken($token);

        $stmt = $this->db->prepare(
            'SELECT rt.id, rt.expira_em, rt.revogado, u.id AS usuario_id, u.role, u.ativo
             FROM refresh_tokens rt
             JOIN usuarios u ON u.id = rt.usuario_id
             WHERE rt.token_hash = ? AND rt.dispositivo_id = ?'
        );
        $stmt->execute([$hash, $dispositivoId]);
        $row = $stmt->fetch();

        if (!$row)                          Response::error('Refresh token inválido.', 'REFRESH_TOKEN_INVALIDO', 401);
        if ($row['revogado'])               Response::error('Sessão encerrada.',        'REFRESH_TOKEN_REVOGADO',  401);
        if (strtotime($row['expira_em']) < time()) Response::error('Refresh token expirado.', 'REFRESH_TOKEN_EXPIRADO', 401);
        if (!$row['ativo'])                 Response::error('Conta desativada.',         'CONTA_INATIVA',           403);

        $accessToken = JWT::createAccessToken($row['usuario_id'], $row['role']);
        $expiresAt   = date('Y-m-d\TH:i:s', time() + (int)env('JWT_EXPIRY_MINUTES', 15) * 60);

        Response::json(['access_token' => $accessToken, 'expira_em' => $expiresAt]);
    }

    // POST /auth/logout
    public function logout(): void
    {
        AuthMiddleware::handle();
        $body          = json_decode(file_get_contents('php://input'), true) ?? [];
        $dispositivoId = trim($body['dispositivo_id'] ?? '');

        if ($dispositivoId) {
            $this->db->prepare(
                'UPDATE refresh_tokens SET revogado = 1 WHERE usuario_id = ? AND dispositivo_id = ?'
            )->execute([AuthMiddleware::userId(), $dispositivoId]);
        }

        Response::json(['mensagem' => 'Sessão encerrada.']);
    }

    // POST /auth/push-token
    public function pushToken(): void
    {
        AuthMiddleware::handle();
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        Validator::make($body)
            ->required('token', 'Token push')
            ->required('plataforma', 'Plataforma')
            ->in('plataforma', ['ios', 'android'])
            ->required('dispositivo_id', 'Dispositivo')
            ->failOrContinue();

        $userId        = AuthMiddleware::userId();
        $token         = trim($body['token']);
        $plataforma    = $body['plataforma'];
        $dispositivoId = $body['dispositivo_id'];

        // Upsert — atualiza se já existe para este dispositivo, senão insere
        $stmt = $this->db->prepare(
            'INSERT INTO push_tokens (usuario_id, token, plataforma, dispositivo_id)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE token = VALUES(token), ativo = 1'
        );
        $stmt->execute([$userId, $token, $plataforma, $dispositivoId]);

        Response::json(['mensagem' => 'Token registrado.']);
    }

    // ---------------------------------------------------------------
    // Privados
    // ---------------------------------------------------------------

    private function gerarTokens(array $usuario, string $dispositivoId): array
    {
        $userId      = (int) $usuario['id'];
        $role        = $usuario['role'];
        $accessToken = JWT::createAccessToken($userId, $role);
        $refreshRaw  = JWT::createRefreshToken();
        $refreshHash = JWT::hashRefreshToken($refreshRaw);
        $expDays     = (int) env('JWT_REFRESH_EXPIRY_DAYS', 365);
        $expiresAt   = date('Y-m-d H:i:s', strtotime("+$expDays days"));

        // Revoga token anterior do mesmo dispositivo e insere novo
        $this->db->prepare(
            'UPDATE refresh_tokens SET revogado = 1 WHERE usuario_id = ? AND dispositivo_id = ?'
        )->execute([$userId, $dispositivoId]);

        $this->db->prepare(
            'INSERT INTO refresh_tokens (usuario_id, token_hash, dispositivo_id, expira_em)
             VALUES (?, ?, ?, ?)'
        )->execute([$userId, $refreshHash, $dispositivoId, $expiresAt]);

        $accessExpiresAt = date('Y-m-d\TH:i:s', time() + (int)env('JWT_EXPIRY_MINUTES', 15) * 60);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshRaw,
            'expira_em'     => $accessExpiresAt,
            'usuario'       => [
                'id'       => $userId,
                'nome'     => $usuario['nome'],
                'email'    => $usuario['email'],
                'role'     => $role,
                'foto_url' => null,
            ],
        ];
    }

    private function trocaCodeGoogle(string $code): ?array
    {
        $clientId     = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        $redirectUri  = env('GOOGLE_REDIRECT_URI');

        // Troca code por access_token
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'code'          => $code,
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri'  => $redirectUri,
                'grant_type'    => 'authorization_code',
            ]),
        ]);
        $tokenRes = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (empty($tokenRes['id_token'])) return null;

        // Decodifica o id_token para pegar dados do usuário (sem verificar assinatura — apenas para uso interno)
        $parts = explode('.', $tokenRes['id_token']);
        if (count($parts) < 2) return null;

        return json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true) ?: null;
    }
}
