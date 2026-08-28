<?php
class ConfiguracoesController
{
    private PDO $db;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->db = Database::connect();
    }

    // GET /configuracoes
    public function show(): void
    {
        AuthMiddleware::requireRole('admin', 'secretaria');

        $stmt = $this->db->query('SELECT * FROM configuracoes_creche LIMIT 1');
        $cfg  = $stmt->fetch();

        if (!$cfg) {
            // Retorna defaults caso a tabela esteja vazia
            Response::json([
                'antecedencia_padrao_dias'    => 3,
                'validade_codigo_vinculo_dias' => 7,
                'nome_creche'                 => null,
                'atualizado_em'               => null,
            ]);
            return;
        }

        Response::json($this->format($cfg));
    }

    // PATCH /configuracoes
    public function update(): void
    {
        AuthMiddleware::requireRole('admin');

        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $sets   = [];
        $params = [];

        if (isset($body['antecedencia_padrao_dias'])) {
            if (!in_array((int) $body['antecedencia_padrao_dias'], [0, 1, 3, 7], true)) {
                Response::error('Valores válidos para antecedencia_padrao_dias: 0, 1, 3, 7.', 'VALOR_INVALIDO', 400);
            }
            $sets[]   = 'antecedencia_padrao_dias = ?';
            $params[] = (int) $body['antecedencia_padrao_dias'];
        }
        if (isset($body['validade_codigo_vinculo_dias'])) {
            $v = (int) $body['validade_codigo_vinculo_dias'];
            if ($v < 1 || $v > 90) {
                Response::error('validade_codigo_vinculo_dias deve ser entre 1 e 90.', 'VALOR_INVALIDO', 400);
            }
            $sets[]   = 'validade_codigo_vinculo_dias = ?';
            $params[] = $v;
        }
        if (isset($body['nome_creche'])) {
            $sets[]   = 'nome_creche = ?';
            $params[] = trim($body['nome_creche']) ?: null;
        }

        if (empty($sets)) Response::error('Nenhum campo para atualizar.', 'DADOS_INVALIDOS', 400);

        // Upsert: tenta atualizar; se não existir, insere
        $existing = $this->db->query('SELECT id FROM configuracoes_creche LIMIT 1')->fetch();

        if ($existing) {
            $params[] = $existing['id'];
            $this->db->prepare(
                'UPDATE configuracoes_creche SET ' . implode(', ', $sets) . ' WHERE id = ?'
            )->execute($params);
        } else {
            // Monta INSERT dinâmico
            $cols   = array_map(fn($s) => explode(' =', $s)[0], $sets);
            $this->db->prepare(
                'INSERT INTO configuracoes_creche (' . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($params), '?')) . ')'
            )->execute($params);
        }

        $this->show();
    }

    // ---------------------------------------------------------------
    private function format(array $r): array
    {
        return [
            'antecedencia_padrao_dias'    => (int) $r['antecedencia_padrao_dias'],
            'validade_codigo_vinculo_dias' => (int) $r['validade_codigo_vinculo_dias'],
            'nome_creche'                 => $r['nome_creche'] ?? null,
            'atualizado_em'               => $r['atualizado_em'] ?? null,
        ];
    }
}
