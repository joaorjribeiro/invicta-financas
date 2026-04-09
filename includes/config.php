<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Configuração de conexão PDO - Versão robusta
 */
function getConnection()
{
    static $pdo = null;

    // Retorna a conexão já existente (evita reconectar desnecessariamente)
    if ($pdo !== null) {
        return $pdo;
    }

    // Carrega variáveis de ambiente
    $config = [
        'host'   => getenv('MYSQLHOST')     ?: '127.0.0.1',
        'port'   => getenv('MYSQLPORT')     ?: '3306',
        'user'   => getenv('MYSQLUSER'),
        'pass'   => getenv('MYSQLPASSWORD'),
        'dbname' => getenv('MYSQLDATABASE'),
    ];

    // Validação clara das variáveis obrigatórias
    if (empty($config['host']) || empty($config['user']) || empty($config['dbname']) || empty($config['port'])) {
        throw new Exception("❌ Variáveis de ambiente do banco não configuradas corretamente.\nVerifique: MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE e MYSQLPORT");
    }

    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 15,       
        PDO::ATTR_PERSISTENT         => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $maxRetries = 3;
    $retryCount = 0;

    while ($retryCount < $maxRetries) {
        try {
            $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
            return $pdo;

        } catch (PDOException $e) {
            $retryCount++;

            // Erros comuns de conexão que valem retry
            $isConnectionError = 
                $e->getCode() == 2006 || 
                $e->getCode() == 2002 ||
                strpos($e->getMessage(), 'greeting packet') !== false ||
                strpos($e->getMessage(), 'gone away') !== false ||
                strpos($e->getMessage(), 'Connection refused') !== false;

            if ($isConnectionError && $retryCount < $maxRetries) {
                sleep(1); // Aguarda 1 segundo antes de tentar novamente
                continue;
            }

            // Log detalhado antes de lançar o erro
            logError($e, "Falha ao conectar ao banco de dados", ['dsn' => $dsn, 'retry' => $retryCount]);
            throw $e;
        }
    }
}

/**
 * Executa uma query com preparação e retry automático em caso de "server has gone away"
 */
function query($sql, $params = [], $retry = true)
{
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;

    } catch (PDOException $e) {
        // Retry automático para erro 2006 (MySQL server has gone away)
        if ($retry && ($e->getCode() == 2006 || strpos($e->getMessage(), 'gone away') !== false)) {
            return query($sql, $params, false); // Tenta uma vez sem retry
        }

        logError($e, $sql, $params);
        throw $e;
    }
}

/**
 * Funções auxiliares
 */
function fetchAll($sql, $params = [])
{
    return query($sql, $params)->fetchAll();
}

function fetchOne($sql, $params = [])
{
    return query($sql, $params)->fetch();
}

function execute($sql, $params = [])
{
    return query($sql, $params)->rowCount();
}

/**
 * Logging de erros 
 */
function logError($e, $sql, $params = [])
{
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error'     => $e->getMessage(),
        'code'      => $e->getCode(),
        'file'      => $e->getFile(),
        'line'      => $e->getLine(),
        'sql'       => $sql,
        'params'    => $params,
        'trace'     => $e->getTraceAsString()
    ];

    error_log(json_encode($log, JSON_UNESCAPED_UNICODE));
}