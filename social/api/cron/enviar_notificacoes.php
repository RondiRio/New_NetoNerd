<?php
/**
 * Cron: envio de notificações agendadas
 *
 * Execute a cada 15 minutos:
 *   Windows Task Scheduler:
 *     php C:\xampp\htdocs\agenda_elsacorradine\api\cron\enviar_notificacoes.php
 *
 *   Linux crontab:
 *     *\/15 * * * * php /var/www/html/agenda_elsacorradine/api/cron/enviar_notificacoes.php
 */

declare(strict_types=1);

// Evita execução via HTTP
if (PHP_SAPI !== 'cli' && !defined('CRON_ALLOWED')) {
    http_response_code(403);
    exit('Forbidden');
}

require_once dirname(__DIR__) . '/config/autoload.php';

$db = Database::connect();

// Busca notificações para enviar (não enviadas, enviar_em <= agora)
$stmt = $db->prepare(
    'SELECT na.id, na.atividade_id, na.responsavel_id,
            a.titulo, a.urgente,
            pt.token
     FROM notificacoes_agendadas na
     JOIN atividades a      ON a.id = na.atividade_id
     JOIN push_tokens pt    ON pt.usuario_id = na.responsavel_id AND pt.ativo = 1
     WHERE na.enviado = 0
       AND na.enviar_em <= NOW()
       AND a.status = "ativa"
     ORDER BY na.enviar_em ASC
     LIMIT 500'
);
$stmt->execute();
$rows = $stmt->fetchAll();

if (empty($rows)) {
    echo '[' . date('Y-m-d H:i:s') . '] Nenhuma notificação para enviar.' . PHP_EOL;
    exit(0);
}

// Agrupa por atividade para envio em batch
$byAtividade = [];
foreach ($rows as $row) {
    $byAtividade[$row['atividade_id']][] = $row;
}

$enviados   = 0;
$naIds      = [];

foreach ($byAtividade as $atividadeId => $notifs) {
    $tokens = array_column($notifs, 'token');
    $titulo = $notifs[0]['titulo'];
    $urgente = (bool) $notifs[0]['urgente'];

    $body = $urgente
        ? '🚨 Aviso urgente: ' . $titulo
        : 'Lembrete: ' . $titulo . ' — verifique a data no app.';

    Push::enviarAtividade(
        $tokens,
        $urgente ? '🚨 Aviso urgente' : '📅 Lembrete de atividade',
        $body,
        $atividadeId,
        $urgente
    );

    foreach ($notifs as $n) {
        $naIds[] = $n['id'];
    }
    $enviados += count($notifs);
}

// Marca como enviado
if (!empty($naIds)) {
    $placeholders = implode(',', array_fill(0, count($naIds), '?'));
    $db->prepare(
        "UPDATE notificacoes_agendadas SET enviado = 1, enviado_em = NOW() WHERE id IN ($placeholders)"
    )->execute($naIds);
}

echo '[' . date('Y-m-d H:i:s') . "] Enviadas: $enviados notificações." . PHP_EOL;
exit(0);
