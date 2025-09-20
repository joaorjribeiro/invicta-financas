<?php
// Inicia a sessão no topo do arquivo
session_start();

// Redireciona se o usuário já estiver logado
    if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit;
} 

// Inclui o arquivo de conexão com o banco de dados
require_once '../includes/db_connect.php';

// Limpa mensagens de erro ou sucesso anteriores
$errors = $_SESSION['errors'] ?? [];
$success_message = $_SESSION['success_message'] ?? '';

unset($_SESSION['errors']);
unset($_SESSION['success_message']);

// Processa o formulário apenas se for um POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Busca o usuário pelo email
    $stmt = $pdo->prepare("SELECT id, senha FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verifica se o usuário existe e se a senha está correta
    if ($user && password_verify($senha, $user['senha'])) {
        // Autenticação bem-sucedida, define a sessão e redireciona
        $_SESSION['user_id'] = $user['id'];
        header('Location: ../dashboard.php');
        exit;
    } else {
        // Autenticação falhou, armazena a mensagem de erro na sessão
        $_SESSION['errors'][] = 'Email ou senha incorretos.';
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Invicta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
    <style>
        .theme-dark { background: #333; color: #fff; }
        .theme-dark .card { background: #444; color: #fff; }
        .theme-dark .form-label { color: #fff; }
        .theme-dark .form-control { background: #555; color: #fff; border: 1px solid #666; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 400px;">
            <div class="card-body">
                <h3 class="card-title text-center"><i class="bi bi-person-circle"></i> Login</h3>
                
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
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" name="senha" id="senha" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    <div class="text-center mt-3">
                        <a href="cadastro_usuario.php">Não tem conta? Cadastre-se</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>