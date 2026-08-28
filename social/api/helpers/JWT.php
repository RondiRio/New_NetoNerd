<?php
class JWT
{
    private static function secret(): string
    {
        $secret = env('JWT_SECRET');
        if (empty($secret)) {
            throw new \RuntimeException('JWT_SECRET não configurado no .env — obrigatório para emitir/validar tokens.');
        }
        return $secret;
    }

    public static function encode(array $payload): string
    {
        $header  = self::base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = self::base64url(json_encode($payload));
        $sig     = self::base64url(hash_hmac('sha256', "$header.$payload", self::secret(), true));
        return "$header.$payload.$sig";
    }

    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $sig] = $parts;
        $expected = self::base64url(hash_hmac('sha256', "$header.$payload", self::secret(), true));

        // Comparação segura contra timing attacks
        if (!hash_equals($expected, $sig)) return null;

        $data = json_decode(self::base64urlDecode($payload), true);
        if (!$data) return null;

        // Verifica expiração
        if (isset($data['exp']) && $data['exp'] < time()) return null;

        return $data;
    }

    public static function createAccessToken(int $userId, string $role): string
    {
        $minutes = (int) env('JWT_EXPIRY_MINUTES', 15);
        return self::encode([
            'sub'  => $userId,
            'role' => $role,
            'iat'  => time(),
            'exp'  => time() + ($minutes * 60),
        ]);
    }

    public static function createRefreshToken(): string
    {
        return bin2hex(random_bytes(32)); // 64 chars hexadecimais
    }

    public static function hashRefreshToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}
