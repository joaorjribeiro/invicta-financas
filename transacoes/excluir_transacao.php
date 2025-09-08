<?php
// Inicia a sessão
session_start();

// Redireciona para o login se o usuário não estiver autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../usuario/login.php');
    exit;
}

// Inclui a conexão com o banco de dados e as funções
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Obtém o ID do usuário logado
$usuario_id = $_SESSION['user_id'];

// Obtém o ID da transação a ser excluída da URL
$transacao_id = $_GET['id'] ?? null;
if (!$transacao_id) {
    $_SESSION['errors'][] = 'Transação não especificada.';
    header('Location: ../dashboard.php');
    exit;
}

try {
    // Primeiro, verifica se a transação pertence ao usuário logado
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transacoes WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$transacao_id, $usuario_id]);

    if ($stmt->fetchColumn() > 0) {
        // Se a transação existir e pertencer ao usuário, a exclui
        $stmt_delete = $pdo->prepare("DELETE FROM transacoes WHERE id = ? AND id_usuario = ?");
        $stmt_delete->execute([$transacao_id, $usuario_id]);

        $_SESSION['success_message'] = 'Transação excluída com sucesso!';
    } else {
        // Se a transação não for encontrada ou não pertencer ao usuário
        $_SESSION['errors'][] = 'Transação não encontrada ou você não tem permissão para excluí-la.';
    }
} catch (PDOException $e) {
    error_log("Erro ao excluir transação: " . $e->getMessage());
    $_SESSION['errors'][] = 'Erro ao excluir a transação. Tente novamente.';
}

// Redireciona de volta para o dashboard
header('Location: ../dashboard.php');
exit;
?>