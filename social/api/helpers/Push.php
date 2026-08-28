<?php
/**
 * Expo Push Notifications
 * Docs: https://docs.expo.dev/push-notifications/sending-notifications/
 */
class Push
{
    private const EXPO_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Envia notificações em lote para uma lista de tokens Expo
     *
     * @param string[] $tokens     Lista de ExponentPushToken[xxx]
     * @param string   $titulo
     * @param string   $corpo
     * @param array    $dados      Dados extras enviados no payload (ex: tipo, id)
     * @param string   $som        'default' ou null para silencioso
     */
    public static function enviar(
        array  $tokens,
        string $titulo,
        string $corpo,
        array  $dados = [],
        string $som   = 'default'
    ): void {
        if (empty($tokens)) return;

        // Expo aceita até 100 notificações por request
        $chunks = array_chunk($tokens, 100);

        foreach ($chunks as $chunk) {
            $messages = array_map(fn($token) => [
                'to'    => $token,
                'title' => $titulo,
                'body'  => $corpo,
                'data'  => $dados,
                'sound' => $som,
            ], $chunk);

            self::post($messages);
        }
    }

    /**
     * Dispara push imediato de atividade urgente ou cancelada/reagendada
     */
    public static function enviarAtividade(
        array  $tokens,
        string $titulo,
        string $corpo,
        int    $atividadeId,
        bool   $urgente = false
    ): void {
        self::enviar($tokens, $titulo, $corpo, [
            'tipo'        => 'atividade',
            'atividade_id'=> $atividadeId,
            'urgente'     => $urgente,
        ]);
    }

    /**
     * Dispara push de ocorrência aprovada para os responsáveis
     */
    public static function enviarOcorrencia(
        array  $tokens,
        string $nomeAluno,
        string $tipoOcorrencia,
        int    $ocorrenciaId
    ): void {
        $tipo_labels = [
            'queda'        => 'Queda / Acidente',
            'conflito'     => 'Conflito',
            'mal_estar'    => 'Mal-estar',
            'comportamento'=> 'Comportamento',
            'outro'        => 'Ocorrência',
        ];
        $label = $tipo_labels[$tipoOcorrencia] ?? 'Ocorrência';

        self::enviar(
            $tokens,
            "Ocorrência: $nomeAluno",
            "$label registrado(a). Toque para ver os detalhes.",
            ['tipo' => 'ocorrencia', 'ocorrencia_id' => $ocorrenciaId]
        );
    }

    private static function post(array $messages): void
    {
        $ch = curl_init(self::EXPO_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($messages),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Accept-Encoding: gzip, deflate',
            ],
            CURLOPT_TIMEOUT        => 10,
        ]);

        // Falha silenciosa — não bloqueia a resposta da API se o push falhar
        curl_exec($ch);
        curl_close($ch);
    }
}
