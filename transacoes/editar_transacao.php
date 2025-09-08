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

// Obtém o ID da transação a ser editada da URL
$transacao_id = $_GET['id'] ?? null;
if (!$transacao_id) {
    header('Location: ../dashboard.php');
    exit;
}

// Limpa mensagens de erro ou sucesso
$errors = $_SESSION['errors'] ?? [];
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['errors'], $_SESSION['success_message']);

// Lógica para buscar a transação
try {
    $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$transacao_id, $usuario_id]);
    $transacao = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se a transação não for encontrada ou não pertencer ao usuário, redireciona
    if (!$transacao) {
        header('Location: ../dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar transação para edição: " . $e->getMessage());
    $_SESSION['errors'][] = 'Erro ao carregar transação. Tente novamente.';
    header('Location: ../dashboard.php');
    exit;
}

// Busca as categorias do usuário para o dropdown
$stmt_categorias = $pdo->prepare("SELECT id, nome, tipo FROM categorias WHERE id_usuario = ? ORDER BY nome");
$stmt_categorias->execute([$usuario_id]);
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

// Processa o formulário de atualização da transação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao'] ?? '');
    $valor = sanitizeMoney($_POST['valor'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $data_transacao = $_POST['data_transacao'] ?? date('Y-m-d');
    $id_categoria = $_POST['id_categoria'] ?? null;

    $errors = [];

    // Validação dos dados
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

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE transacoes 
                SET descricao = ?, valor = ?, tipo = ?, data_transacao = ?, id_categoria = ?
                WHERE id = ? AND id_usuario = ?
            ");
            $stmt->execute([$descricao, $valor, $tipo, $data_transacao, $id_categoria, $transacao_id, $usuario_id]);

            $_SESSION['success_message'] = 'Transação atualizada com sucesso!';
            header('Location: ../dashboard.php');
            exit;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar transação: " . $e->getMessage());
            $errors[] = 'Erro ao atualizar a transação. Por favor, tente novamente mais tarde.';
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $transacao_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Transação - Invicta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="<?= htmlspecialchars($_SESSION['tema'] ?? 'claro') ?>">
    <header class="bg-primary text-white text-center p-4">
        <h1>Invicta - Editar Transação</h1>
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
                <h3 class="card-title text-center mb-4"><i class="bi bi-pencil-square"></i> Editar Transação</h3>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="editar_transacao.php?id=<?= htmlspecialchars($transacao_id) ?>">
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <input type="text" name="descricao" id="descricao" class="form-control" required value="<?= htmlspecialchars($transacao['descricao'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor (R$)</label>
                        <input type="text" name="valor" id="valor" class="form-control" required value="<?= htmlspecialchars($transacao['valor'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="despesa" <?= ($transacao['tipo'] == 'despesa') ? 'selected' : '' ?>>Despesa</option>
                            <option value="receita" <?= ($transacao['tipo'] == 'receita') ? 'selected' : '' ?>>Receita</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="id_categoria" class="form-label">Categoria</label>
                        <select name="id_categoria" id="id_categoria" class="form-select" required>
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= htmlspecialchars($categoria['id']) ?>" 
                                    <?= ($transacao['id_categoria'] == $categoria['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nome']) ?> (<?= htmlspecialchars($categoria['tipo']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="data_transacao" class="form-label">Data</label>
                        <input type="date" name="data_transacao" id="data_transacao" class="form-control" required value="<?= htmlspecialchars($transacao['data_transacao'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Atualizar Transação</button>
                    <a href="../dashboard.php" class="btn btn-secondary w-100 mt-2">Cancelar</a>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>