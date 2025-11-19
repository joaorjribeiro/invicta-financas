<?php
session_start();
session_unset();         // Limpa variáveis de sessão
session_destroy();       // Destrói a sessão

// Impede voltar com botão do navegador
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

// Redireciona para a página inicial
header("Location: ../pages/index.php");
exit;
?>
