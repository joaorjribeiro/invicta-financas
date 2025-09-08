<?php
// Inicia a sessão
session_start();

// Redireciona para o login se o usuário não estiver autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Inclui a conexão com o banco de dados e as funções
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Obtém o ID do usuário logado
$usuario_id = $_SESSION['user_id'];

// Limpa mensagens de erro ou sucesso
$errors = $_SESSION['errors'] ?? [];
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['errors'], $_SESSION['success_message']);

// Busca os dados atuais do usuário
$stmt_usuario = $pdo->prepare("SELECT nome, email, perfil FROM usuarios WHERE id = ?");
$stmt_usuario->execute([$usuario_id]);
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    // Se o usuário não for encontrado (situação incomum), redireciona para o login
    session_destroy();
    header('Location: login.php');
    exit;
}

// Processa o formulário de atualização do usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $erros_form = [];

    // Validação do nome e email
    if (empty($nome)) {
        $erros_form[] = 'O nome é obrigatório.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros_form[] = 'Email inválido.';
    } else {
        // Verifica se o novo email já existe para outro usuário
        $stmt_email = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ? AND id != ?");
        $stmt_email->execute([$email, $usuario_id]);
        if ($stmt_email->fetchColumn() > 0) {
            $erros_form[] = 'Este email já está em uso por outro usuário.';
        }
    }

    // Se a senha estiver sendo alterada, valida a senha atual e a nova
    if (!empty($nova_senha)) {
        if (strlen($nova_senha) < 6) {
            $erros_form[] = 'A nova senha deve ter pelo menos 6 caracteres.';
        }
        $stmt_senha_atual = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmt_senha_atual->execute([$usuario_id]);
        $senha_hash_atual = $stmt_senha_atual->fetchColumn();

        if (!password_verify($senha_atual, $senha_hash_atual)) {
            $erros_form[] = 'A senha atual está incorreta.';
        }
    }

    if (empty($erros_form)) {
        try {
            // Prepara a query de atualização
            $sql = "UPDATE usuarios SET nome = ?, email = ?";
            $params = [$nome, $email];

            if (!empty($nova_senha)) {
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sql .= ", senha = ?";
                $params[] = $nova_senha_hash;
            }

            $sql .= " WHERE id = ?";
            $params[] = $usuario_id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $_SESSION['success_message'] = 'Dados do perfil atualizados com sucesso!';
            header('Location: editar_usuario.php');
            exit;

        } catch (PDOException $e) {
            error_log("Erro ao atualizar perfil do usuário: " . $e->getMessage());
            $erros_form[] = 'Erro ao atualizar. Por favor, tente novamente mais tarde.';
        }
    }

    if (!empty($erros_form)) {
        $_SESSION['errors'] = $erros_form;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Perfil - Invicta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="<?= htmlspecialchars($_SESSION['tema'] ?? 'claro') ?>">
    <header class="bg-primary text-white text-center p-4">
        <h1>Invicta - Editar Perfil</h1>
    </header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php"><i class="bi bi-wallet2"></i> Dashboard</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../dashboard.php"><i class="bi bi-house"></i> Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-body">
                <h3 class="card-title text-center mb-4"><i class="bi bi-person-fill-gear"></i> Configurações do Perfil</h3>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success" role="alert">
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" name="nome" id="nome" class="form-control" required value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                    </div>
                    <hr>
                    <p class="text-muted">Preencha os campos abaixo apenas se quiser alterar a senha.</p>
                    <div class="mb-3">
                        <label for="senha_atual" class="form-label">Senha Atual</label>
                        <input type="password" name="senha_atual" id="senha_atual" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="nova_senha" class="form-label">Nova Senha</label>
                        <input type="password" name="nova_senha" id="nova_senha" class="form-control">
                        <div class="form-text">Mínimo de 6 caracteres.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Atualizar Perfil</button>
                    <a href="../dashboard.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
                </form>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>