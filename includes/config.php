<?php
// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inv_db');
define('DB_CHARSET', 'utf8mb4');

// Configurações da Aplicação
define('DEFAULT_THEME', 'claro');
define('DEBUG_MODE', true);

// Tratamento de Erros (usado em conjunto com o DEBUG_MODE)
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}