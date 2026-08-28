<?php
class DashboardController
{
    private PDO $db;

    public function __construct()
    {
        AuthMiddleware::handle();
        AuthMiddleware::requireRole('admin', 'secretaria');
        $this->db = Database::connect();
    }

    // GET /dashboard
    public function index(): void
    {
        // Totais gerais
        $totalAlunos        = (int) $this->db->query('SELECT COUNT(*) FROM alunos WHERE ativo = 1')->fetchColumn();
        $totalTurmas        = (int) $this->db->query('SELECT COUNT(*) FROM turmas WHERE ativo = 1')->fetchColumn();
        $totalResponsaveis  = (int) $this->db->query('SELECT COUNT(*) FROM usuarios WHERE role = "responsavel" AND ativo = 1')->fetchColumn();
        $totalProfessores   = (int) $this->db->query('SELECT COUNT(*) FROM usuarios WHERE role = "professor" AND ativo = 1')->fetchColumn();

        // Vínculos pendentes
        $vinculosPendentes  = (int) $this->db->query('SELECT COUNT(*) FROM vinculos_responsavel WHERE status = "pendente"')->fetchColumn();

        // Ocorrências pendentes
        $ocorrenciasPendentes = (int) $this->db->query('SELECT COUNT(*) FROM ocorrencias WHERE status = "pendente"')->fetchColumn();

        // Atividades: próximas 7 dias
        $atividadesProximas = (int) $this->db->query(
            'SELECT COUNT(*) FROM atividades
             WHERE status = "ativa" AND data_atividade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)'
        )->fetchColumn();

        // Atividades urgentes ativas
        $atividadesUrgentes = (int) $this->db->query(
            'SELECT COUNT(*) FROM atividades WHERE status = "ativa" AND urgente = 1 AND data_atividade >= CURDATE()'
        )->fetchColumn();

        // Próximas 5 atividades
        $stmt = $this->db->query(
            'SELECT a.id, a.titulo, a.data_atividade, a.urgente, a.status,
                    t.nome AS turma_nome
             FROM atividades a
             LEFT JOIN turmas t ON t.id = a.turma_id
             WHERE a.status = "ativa" AND a.data_atividade >= CURDATE()
             ORDER BY a.data_atividade ASC
             LIMIT 5'
        );
        $proximasAtividades = array_map(fn($r) => [
            'id'             => (int) $r['id'],
            'titulo'         => $r['titulo'],
            'data_atividade' => $r['data_atividade'],
            'urgente'        => (bool) $r['urgente'],
            'status'         => $r['status'],
            'turma'          => $r['turma_nome'],
        ], $stmt->fetchAll());

        // Últimas 5 ocorrências pendentes
        $stmt = $this->db->query(
            'SELECT o.id, o.tipo, o.gravidade, o.status, o.criado_em,
                    a.nome AS aluno_nome, t.nome AS turma_nome,
                    up.nome AS professor_nome
             FROM ocorrencias o
             JOIN alunos a    ON a.id = o.aluno_id
             JOIN turmas t    ON t.id = a.turma_id
             JOIN usuarios up ON up.id = o.professor_id
             WHERE o.status = "pendente"
             ORDER BY o.criado_em DESC
             LIMIT 5'
        );
        $ocorrenciaRecentes = array_map(fn($r) => [
            'id'             => (int) $r['id'],
            'tipo'           => $r['tipo'],
            'gravidade'      => $r['gravidade'],
            'status'         => $r['status'],
            'aluno'          => $r['aluno_nome'],
            'turma'          => $r['turma_nome'],
            'professor'      => $r['professor_nome'],
            'criado_em'      => $r['criado_em'],
        ], $stmt->fetchAll());

        Response::json([
            'resumo' => [
                'total_alunos'             => $totalAlunos,
                'total_turmas'             => $totalTurmas,
                'total_responsaveis'       => $totalResponsaveis,
                'total_professores'        => $totalProfessores,
                'vinculos_pendentes'       => $vinculosPendentes,
                'ocorrencias_pendentes'    => $ocorrenciasPendentes,
                'atividades_proximas_7d'   => $atividadesProximas,
                'atividades_urgentes'      => $atividadesUrgentes,
            ],
            'proximas_atividades'   => $proximasAtividades,
            'ocorrencias_pendentes' => $ocorrenciaRecentes,
        ]);
    }
}
