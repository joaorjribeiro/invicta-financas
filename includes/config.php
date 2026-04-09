<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Configuração de conexão PDO
 */
function getConnection(): PDO
{
    static $pdo = null;

    // Retorna conexão válida já existente
    if ($pdo !== null) {
        try {
            $pdo->query('SELECT 1'); // Verifica se ainda está ativa
            return $pdo;
        } catch (PDOException $e) {
            $pdo = null; // Conexão morta — reconecta
        }
    }

    // Carrega variáveis de ambiente
    $config = [
        'host'   => getenv('MYSQLHOST')     ?: '127.0.0.1',
        'port'   => getenv('MYSQLPORT')     ?: '3306',
        'user'   => getenv('MYSQLUSER'),
        'pass'   => getenv('MYSQLPASSWORD'),
        'dbname' => getenv('MYSQLDATABASE'),
    ];

    // Validação das variáveis obrigatórias
    $missing = array_filter(['MYSQLUSER' => $config['user'], 'MYSQLDATABASE' => $config['dbname']], fn($v) => empty($v));
    if (!empty($missing)) {
        throw new RuntimeException(
            "❌ Variáveis de ambiente não configuradas: " . implode(', ', array_keys($missing))
        );
    }

    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";

    // PHP 8.5+: constantes PDO::MYSQL_* foram movidas para Pdo\Mysql::*
    if (PHP_VERSION_ID >= 80500) {
        $initCommandKey = Pdo\Mysql::ATTR_INIT_COMMAND;
        $sslVerifyKey   = Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT;
    } else {
        $initCommandKey = PDO::MYSQL_ATTR_INIT_COMMAND;
        $sslVerifyKey   = PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 8,
        PDO::ATTR_PERSISTENT         => false,
        PDO::ATTR_EMULATE_PREPARES   => false,
        $initCommandKey              => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        $sslVerifyKey                => false, // SSL Railway sem verificação de cert
    ];

    $maxRetries    = 2;  // 2 tentativas × (8s timeout + 500ms delay) = ~17s máximo
    $retryCount  = 0;
    $lastException = null;

    while ($retryCount < $maxRetries) {
        try {
            $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
            return $pdo;

        } catch (PDOException $e) {
            $pdo           = null;
            $lastException = $e;
            $retryCount++;

            $isConnectionError =
                in_array($e->getCode(), [2002, 2006, 2013]) ||
                strpos($e->getMessage(), 'greeting packet')  !== false ||
                strpos($e->getMessage(), 'gone away')        !== false ||
                strpos($e->getMessage(), 'Connection refused') !== false ||
                strpos($e->getMessage(), 'timed out')        !== false;

            if ($isConnectionError && $retryCount < $maxRetries) {
                usleep(500_000); // 500ms entre tentativas (Railway proxy)
                continue;
            }

            break;
        }
    }

    logError($lastException, "Falha ao conectar ao banco de dados", [
        'host'  => $config['host'],
        'port'  => $config['port'],
        'db'    => $config['dbname'],
        'retry' => $retryCount,
    ]);

    throw $lastException;
}

/**
 * Executa uma query com preparação e retry automático em caso de conexão perdida
 */
function query(string $sql, array $params = [], bool $retry = true): PDOStatement
{
    try {
        $pdo  = getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;

    } catch (PDOException $e) {
        // Retry automático para conexão perdida (erro 2006)
        if ($retry && (
            $e->getCode() == 2006 ||
            strpos($e->getMessage(), 'gone away') !== false ||
            strpos($e->getMessage(), 'Lost connection') !== false
        )) {
            return query($sql, $params, false);
        }

        logError($e, $sql, $params);
        throw $e;
    }
}

/**
 * Retorna todos os resultados de uma query
 */
function fetchAll(string $sql, array $params = []): array
{
    return query($sql, $params)->fetchAll();
}

/**
 * Retorna apenas a primeira linha de uma query
 */
function fetchOne(string $sql, array $params = []): array|false
{
    return query($sql, $params)->fetch();
}

/**
 * Executa uma query e retorna o número de linhas afetadas
 */
function execute(string $sql, array $params = []): int
{
    return query($sql, $params)->rowCount();
}

/**
 * Retorna o último ID inserido
 */
function lastInsertId(): string
{
    return getConnection()->lastInsertId();
}

/**
 * Executa um bloco dentro de uma transação, com rollback automático em caso de erro
 */
function transaction(callable $callback): mixed
{
    $pdo = getConnection();
    $pdo->beginTransaction();

    try {
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (Throwable $e) {
        $pdo->rollBack();
        logError($e, "Erro durante transação");
        throw $e;
    }
}

/**
 * Logging estruturado de erros no error_log do PHP
 */
function logError(Throwable $e, string $context = '', array $extra = []): void
{
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'context'   => $context,
        'error'     => $e->getMessage(),
        'code'      => $e->getCode(),
        'file'      => $e->getFile(),
        'line'      => $e->getLine(),
        'extra'     => $extra,
        'trace'     => $e->getTraceAsString(),
    ];

    error_log('[DB] ' . json_encode($log, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}