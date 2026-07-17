<?php
/**
 * Gerenciador de Configurações - NetoNerd ITSM
 * Carrega variáveis de ambiente do arquivo .env
 */

class Config {
    private static $config = [];
    private static $loaded = false;

    /**
     * Carrega o arquivo .env
     */
    public static function load() {
        if (self::$loaded) {
            return;
        }

        $envFile = __DIR__ . '/../.env';

        // Se o arquivo .env não existir, usa .env.example como fallback (apenas para desenvolvimento)
        if (!file_exists($envFile)) {
            $envFile = __DIR__ . '/../.env.example';
            error_log("AVISO: Arquivo .env não encontrado. Usando .env.example como fallback.");
        }

        if (!file_exists($envFile)) {
            die("ERRO CRÍTICO: Arquivo .env não encontrado. Por favor, copie .env.example para .env e configure suas credenciais.");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentários e linhas vazias
            if (strpos(trim($line), '#') === 0 || trim($line) === '') {
                continue;
            }

            // Parsear a linha no formato CHAVE=VALOR
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remover aspas delimitadoras (envolvendo o valor inteiro), preservando
            // aspas que façam parte do valor real (ex: senha terminando em ").
            // trim($value, '"\'') apagaria essas aspas reais silenciosamente.
            $len = strlen($value);
            if ($len >= 2) {
                $first = $value[0];
                $last = $value[$len - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            // Converter valores booleanos
            if (strtolower($value) === 'true') {
                $value = true;
            } elseif (strtolower($value) === 'false') {
                $value = false;
            }

            self::$config[$key] = $value;
        }

        self::$loaded = true;
    }

    /**
     * Obtém um valor de configuração
     *
     * @param string $key Chave da configuração
     * @param mixed $default Valor padrão se a chave não existir
     * @return mixed
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }

        return self::$config[$key] ?? $default;
    }

    /**
     * Define um valor de configuração em runtime
     *
     * @param string $key Chave da configuração
     * @param mixed $value Valor da configuração
     */
    public static function set($key, $value) {
        self::$config[$key] = $value;
    }

    /**
     * Verifica se uma chave existe
     *
     * @param string $key Chave da configuração
     * @return bool
     */
    public static function has($key) {
        if (!self::$loaded) {
            self::load();
        }

        return isset(self::$config[$key]);
    }

    /**
     * Obtém todas as configurações
     *
     * @return array
     */
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }

        return self::$config;
    }

    /**
     * Obtém configurações de banco de dados
     *
     * @return array
     */
    public static function database() {
        return [
            'host' => self::get('DB_HOST', 'localhost'),
            'port' => self::get('DB_PORT', '3306'),
            'name' => self::get('DB_NAME', ''),
            'username' => self::get('DB_USERNAME', ''),
            'password' => self::get('DB_PASSWORD', '')
        ];
    }

    /**
     * Obtém configurações de email
     *
     * @return array
     */
    public static function email() {
        return [
            'host' => self::get('MAIL_HOST', 'smtp.hostinger.com'),
            'port' => self::get('MAIL_PORT', 465),
            'username' => self::get('MAIL_USERNAME', ''),
            'password' => self::get('MAIL_PASSWORD', ''),
            'from_email' => self::get('MAIL_FROM_EMAIL', 'noreply@netonerd.com.br'),
            'from_name' => self::get('MAIL_FROM_NAME', 'NetoNerd Suporte'),
            'encryption' => self::get('MAIL_ENCRYPTION', 'tls')
        ];
    }

    /**
     * Verifica se está em modo de depuração
     *
     * @return bool
     */
    public static function isDebug() {
        return self::get('APP_DEBUG', false) === true || self::get('APP_DEBUG', false) === 'true';
    }

    /**
     * Verifica se está em ambiente de produção
     *
     * @return bool
     */
    public static function isProduction() {
        return self::get('APP_ENV', 'development') === 'production';
    }
}

// Carregar automaticamente ao incluir este arquivo
Config::load();
