<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

function getConnection()
{
    static $config;

    if (!$config) {
        $config = [
            'host' => getenv('MYSQLHOST'),
            'user' => getenv('MYSQLUSER'),
            'pass' => getenv('MYSQLPASSWORD'),
            'dbname' => getenv('MYSQLDATABASE'),
            'port' => getenv('MYSQLPORT')
        ];

        if (!$config['host'] || !$config['user'] || !$config['dbname'] || !$config['port']) {
            throw new Exception("Variáveis de ambiente não configuradas");
        }
    }

    return new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4;",
        $config['user'],
        $config['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
}

/**
 * Executa query com retry automático + logging
 */
function query($sql, $params = [], $retry = true)
{
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;

    } catch (PDOException $e) {

        // Retry automático para erro 2006
        if ($retry && $e->getCode() == 2006) {
            return query($sql, $params, false);
        }

        logError($e, $sql, $params);
        throw $e;
    }
}

/**
 * Helpers 
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

/** Logging de erro (produção) **/

function logError($e, $sql, $params)
{
    $log = [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'sql' => $sql,
        'params' => $params,
        'time' => date('Y-m-d H:i:s')
    ];

    error_log(json_encode($log));
}