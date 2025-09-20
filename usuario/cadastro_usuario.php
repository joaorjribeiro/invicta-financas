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
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $perfil = $_POST['perfil'] ?? 'usuario';

    $errors = [];

    // Validação dos dados
    if (empty($nome)) {
        $errors[] = 'O nome é obrigatório.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'O email é inválido ou não foi preenchido.';
    }
    if (empty($senha) || strlen($senha) < 6) {
        $errors[] = 'A senha deve ter pelo menos 6 caracteres.';
    }

    // Se não houver erros de validação, continua com o cadastro
    if (empty($errors)) {
        try {
            // Verifica se o email já está cadastrado
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Email já cadastrado.';
            } else {
                // Criptografa a senha com password_hash
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                
                // Insere o novo usuário
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nome, $email, $senha_hash, $perfil]);
                
                // Define a mensagem de sucesso na sessão
                $_SESSION['success_message'] = 'Usuário cadastrado com sucesso! Faça login para continuar.';
                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            // Em caso de erro no banco de dados, registra e informa o usuário
            error_log("Erro ao cadastrar usuário: " . $e->getMessage());
            $errors[] = 'Erro ao cadastrar: Por favor, tente novamente mais tarde.';
        }
    }

    // Se houver erros, armazena-os na sessão e redireciona
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST; // Armazena os dados do formulário para preencher novamente
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Recupera os dados do formulário da sessão para preenchimento automático
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro - Invicta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/cadastro_user.css">
</head>
<body class="<?= htmlspecialchars($_SESSION['tema'] ?? 'claro') ?>">
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 400px;">
            <div class="card-body">
                <h3 class="card-title text-center"><i class="bi bi-person-plus"></i> Cadastro</h3>
                
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
                        <input type="text" name="nome" id="nome" class="form-control" required value="<?= htmlspecialchars($form_data['nome'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($form_data['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" name="senha" id="senha" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="perfil" class="form-label">Perfil</label>
                        <select name="perfil" id="perfil" class="form-select" required>
                            <option value="usuario" <?= (($form_data['perfil'] ?? 'usuario') == 'usuario') ? 'selected' : '' ?>>Usuário</option>
                            <option value="admin" <?= (($form_data['perfil'] ?? 'usuario') == 'admin') ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
                    <div class="text-center mt-3">
                        <a href="login.php">Já tem conta? Faça login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>