<?php
/**
 * Autoloader simples — carrega classes por nome de arquivo
 */
spl_autoload_register(function (string $class): void {
    $dirs = [
        __DIR__ . '/../helpers/',
        __DIR__ . '/../middleware/',
        __DIR__ . '/../controllers/',
        __DIR__ . '/../models/',
        __DIR__ . '/../',
    ];

    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Carrega configs sempre necessárias
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/cors.php';
