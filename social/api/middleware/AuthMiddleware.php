<?php
class AuthMiddleware
{
    public static array $usuario = [];

    /**
     * Extrai e valida o Bearer token do header Authorization.
     * Popula self::$usuario com { sub, role }.
     * Aborta com 401 se inválido.
     */
    public static function handle(): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
               ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
               ?? '';

        if (!str_starts_with($header, 'Bearer ')) {
            Response::error('Token de autenticação não fornecido.', 'TOKEN_INVALIDO', 401);
        }

        $token   = substr($header, 7);
        $payload = JWT::decode($token);

        if (!$payload) {
            // Distingue token expirado de inválido checando sem validar expiração
            $partes = explode('.', $token);
            if (count($partes) === 3) {
                $data = json_decode(base64_decode(strtr($partes[1], '-_', '+/')), true);
                if ($data && isset($data['exp']) && $data['exp'] < time()) {
                    Response::error('Token expirado.', 'TOKEN_EXPIRADO', 401);
                }
            }
            Response::error('Token inválido.', 'TOKEN_INVALIDO', 401);
        }

        self::$usuario = $payload;
    }

    /**
     * Verifica se o usuário autenticado tem uma das roles permitidas.
     * Deve ser chamado após handle().
     */
    public static function requireRole(string ...$roles): void
    {
        if (!in_array(self::$usuario['role'] ?? '', $roles, true)) {
            Response::forbidden();
        }
    }

    public static function userId(): int
    {
        return (int) (self::$usuario['sub'] ?? 0);
    }

    public static function userRole(): string
    {
        return self::$usuario['role'] ?? '';
    }
}
