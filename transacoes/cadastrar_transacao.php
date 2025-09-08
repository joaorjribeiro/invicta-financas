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

// Limpa mensagens de erro ou sucesso
$errors = $_SESSION['errors'] ?? [];
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['errors'], $_SESSION['success_message']);

// Busca as categorias do usuário para o dropdown
$stmt_categorias = $pdo->prepare("SELECT id, nome, tipo FROM categorias WHERE id_usuario = ? ORDER BY nome");
$stmt_categorias->execute([$usuario_id]);
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

// Processa o formulário de cadastro de transação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao'] ?? '');
    $valor = sanitizeMoney($_POST['valor'] ?? ''); // Usa a função para limpar o valor
    $tipo = $_POST['tipo'] ?? '';
    $data_transacao = $_POST['data_transacao'] ?? date('Y-m-d');
    $id_categoria = $_POST['id_categoria'] ?? null;

    $errors = [];

    // Validação dos dados do formulário
    if (empty($descricao)) {
        $errors[] = 'A descrição é obrigatória.';
    }
    if (!is_numeric($valor) || $valor <= 0) {
        $errors[] = 'O valor deve ser um número positivo.';
    }
    if (!in_array($tipo, ['receita', 'despesa'])) {
        $errors[] = 'O tipo da transação é inválido.';
    }
    if (empty($id_categoria)) {
        $errors[] = 'A categoria é obrigatória.';
    }

    // Se não houver erros, insere a transação no banco de dados
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO transacoes (descricao, valor, tipo, data_transacao, id_categoria, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$descricao, $valor, $tipo, $data_transacao, $id_categoria, $usuario_id]);

            $_SESSION['success_message'] = 'Transação cadastrada com sucesso!';
            header('Location: ../dashboard.php');
            exit;

        } catch (PDOException $e) {
            error_log("Erro ao cadastrar transação: " . $e->getMessage());
            $errors[] = 'Erro ao cadastrar a transação. Por favor, tente novamente mais tarde.';
        }
    }

    // Se houver erros, armazena-os na sessão e redireciona
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
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
    <title>Nova Transação - Invicta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="<?= htmlspecialchars($_SESSION['tema'] ?? 'claro') ?>">
    <header class="bg-primary text-white text-center p-4">
        <h1>Invicta - Nova Transação</h1>
    </header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php"><i class="bi bi-wallet2"></i> Dashboard</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../dashboard.php"><i class="bi bi-house"></i> Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="../usuario/logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-body">
                <h3 class="card-title text-center mb-4"><i class="bi bi-cash-stack"></i> Cadastrar Nova Transação</h3>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <input type="text" name="descricao" id="descricao" class="form-control" required value="<?= htmlspecialchars($form_data['descricao'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor (R$)</label>
                        <input type="text" name="valor" id="valor" class="form-control" required value="<?= htmlspecialchars($form_data['valor'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="despesa" <?= (($form_data['tipo'] ?? 'despesa') == 'despesa') ? 'selected' : '' ?>>Despesa</option>
                            <option value="receita" <?= (($form_data['tipo'] ?? 'despesa') == 'receita') ? 'selected' : '' ?>>Receita</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="id_categoria" class="form-label">Categoria</label>
                        <select name="id_categoria" id="id_categoria" class="form-select" required>
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= htmlspecialchars($categoria['id']) ?>" 
                                    data-tipo="<?= htmlspecialchars($categoria['tipo']) ?>" 
                                    <?= (($form_data['id_categoria'] ?? '') == $categoria['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nome']) ?> (<?= htmlspecialchars($categoria['tipo']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="data_transacao" class="form-label">Data</label>
                        <input type="date" name="data_transacao" id="data_transacao" class="form-control" required value="<?= htmlspecialchars($form_data['data_transacao'] ?? date('Y-m-d')) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Cadastrar Transação</button>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>