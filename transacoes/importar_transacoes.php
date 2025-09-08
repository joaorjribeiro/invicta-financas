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

// Busca as categorias do usuário para a lógica de correspondência
$stmt_categorias = $pdo->prepare("SELECT id, nome, tipo FROM categorias WHERE id_usuario = ?");
$stmt_categorias->execute([$usuario_id]);
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

$categoria_map = [];
foreach ($categorias as $cat) {
    $categoria_map[mb_strtolower($cat['nome'])] = $cat;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erro no upload do arquivo.';
    } else {
        $file_tmp_path = $_FILES['csv_file']['tmp_name'];
        $file_mime_type = mime_content_type($file_tmp_path);
        
        // Verifica se o arquivo é CSV
        if ($file_mime_type !== 'text/csv' && $file_mime_type !== 'application/vnd.ms-excel' && !str_contains($_FILES['csv_file']['name'], '.csv')) {
            $errors[] = 'Tipo de arquivo inválido. Por favor, envie um arquivo CSV.';
        } else {
            $handle = fopen($file_tmp_path, 'r');
            if ($handle === false) {
                $errors[] = 'Não foi possível ler o arquivo.';
            } else {
                $count = 0;
                $inserted_count = 0;
                $row_errors = [];
                $header = fgetcsv($handle, 1000, ','); // Pula a primeira linha (cabeçalho)

                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $count++;

                    // Assume a ordem das colunas no CSV: Data, Descricao, Categoria, Tipo, Valor
                    // Adapte esta ordem conforme a necessidade
                    $data_transacao_str = trim($data[0] ?? '');
                    $descricao = trim($data[1] ?? '');
                    $categoria_nome = mb_strtolower(trim($data[2] ?? ''));
                    $tipo = trim($data[3] ?? '');
                    $valor = sanitizeMoney(trim($data[4] ?? ''));

                    // Validações
                    $current_row_errors = [];

                    if (empty($data_transacao_str) || !strtotime($data_transacao_str)) {
                        $current_row_errors[] = 'Data inválida.';
                    }
                    if (empty($descricao)) {
                        $current_row_errors[] = 'Descrição ausente.';
                    }
                    if (!isset($categoria_map[$categoria_nome])) {
                        $current_row_errors[] = 'Categoria "' . $categoria_nome . '" não encontrada.';
                    }
                    if (!is_numeric($valor) || $valor <= 0) {
                        $current_row_errors[] = 'Valor inválido.';
                    }
                    if (!in_array(mb_strtolower($tipo), ['receita', 'despesa'])) {
                        $current_row_errors[] = 'Tipo inválido (deve ser "receita" ou "despesa").';
                    }

                    if (empty($current_row_errors)) {
                        // Dados válidos, insere no banco
                        $id_categoria = $categoria_map[$categoria_nome]['id'];
                        $stmt_insert = $pdo->prepare("INSERT INTO transacoes (descricao, valor, tipo, data_transacao, id_categoria, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt_insert->execute([$descricao, $valor, $tipo, date('Y-m-d', strtotime($data_transacao_str)), $id_categoria, $usuario_id]);
                        $inserted_count++;
                    } else {
                        $row_errors[$count] = $current_row_errors;
                    }
                }
                fclose($handle);

                $_SESSION['success_message'] = "Importação concluída. Total de linhas processadas: $count. Total de transações inseridas: $inserted_count.";
                if (!empty($row_errors)) {
                    $_SESSION['errors'][] = 'Algumas linhas não puderam ser importadas. Detalhes: ' . json_encode($row_errors, JSON_UNESCAPED_UNICODE);
                }
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
        }
    }
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
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
    <title>Importar Transações - Invicta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="<?= htmlspecialchars($_SESSION['tema'] ?? 'claro') ?>">
    <header class="bg-primary text-white text-center p-4">
        <h1>Invicta - Importar Transações</h1>
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
                <h3 class="card-title text-center mb-4"><i class="bi bi-upload"></i> Importar de CSV</h3>
                
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

                <p class="text-muted text-center">Formato de arquivo esperado: **data,descricao,categoria,tipo,valor**</p>
                <p class="text-muted text-center">Exemplo: `2025-09-07,Supermercado,Supermercado,despesa,150.75`</p>

                <form method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Selecione o arquivo CSV</label>
                        <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Importar</button>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>