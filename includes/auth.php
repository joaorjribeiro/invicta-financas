<?php
// Sempre iniciar a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Se não estiver logado, redireciona para o login
    header("Location: ../pages/login.php");
    exit();
}

?>