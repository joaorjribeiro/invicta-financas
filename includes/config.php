<?php

$host = 'junction.proxy.rlwy.net';
$port = '14900';
$user = 'root';
$pass = 'GxMSPpuyVgMTVTlImAtanQaVaWCRWrac';
$dbname = 'railway';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8;",
        $user,
        $pass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}