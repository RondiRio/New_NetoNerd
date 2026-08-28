<?php
/**
 * Configura headers CORS com allowlist explícita (APP_CORS_ORIGINS no .env,
 * separado por vírgula). Refletir qualquer Origin com Credentials: true era
 * o antipadrão que anula a proteção do CORS — origem fora da lista não
 * recebe o header Allow-Origin (navegador bloqueia a resposta).
 */
function setCorsHeaders(): void
{
    $origensPermitidas = array_filter(array_map('trim', explode(',', (string) env('APP_CORS_ORIGINS', ''))));
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if ($origin !== '' && in_array($origin, $origensPermitidas, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
    }

    header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');

    // Preflight OPTIONS — responde imediatamente
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
