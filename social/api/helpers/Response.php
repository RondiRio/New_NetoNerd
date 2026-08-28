<?php
class Response
{
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, int $status = 200): never
    {
        self::json($data ?? ['ok' => true], $status);
    }

    public static function error(string $mensagem, string $codigo, int $status): never
    {
        self::json(['erro' => $mensagem, 'codigo' => $codigo], $status);
    }

    public static function notFound(string $recurso = 'Recurso'): never
    {
        self::error("$recurso não encontrado.", 'NAO_ENCONTRADO', 404);
    }

    public static function forbidden(): never
    {
        self::error('Sem permissão para esta ação.', 'SEM_PERMISSAO', 403);
    }

    public static function unprocessable(string $campo, string $mensagem): never
    {
        self::json(['erro' => $mensagem, 'codigo' => 'DADOS_INVALIDOS', 'campo' => $campo], 422);
    }
}
