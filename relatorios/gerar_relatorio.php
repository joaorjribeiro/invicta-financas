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

// Define o mês e ano atuais como padrão
$mes_ano = $_GET['mes_ano'] ?? date('Y-m');
$tipo_transacao = $_GET['tipo'] ?? 'todos';
$data_parts = explode('-', $mes_ano);
$ano = $data_parts[0];
$mes = $data_parts[1];

// Lógica de backend para buscar os dados
$sql = "
    SELECT t.*, c.nome as categoria_nome 
    FROM transacoes t 
    JOIN categorias c ON c.id = t.id_categoria 
    WHERE t.id_usuario = ? AND YEAR(t.data_transacao) = ? AND MONTH(t.data_transacao) = ?
";

$params = [$usuario_id, $ano, $mes];

if ($tipo_transacao != 'todos') {
    $sql .= " AND t.tipo = ?";
    $params[] = $tipo_transacao;
}

$sql .= " ORDER BY t.data_transacao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca dados para o gráfico de barras
$sql_grafico = "
    SELECT c.nome, SUM(t.valor) as total 
    FROM transacoes t 
    JOIN categorias c ON c.id = t.id_categoria 
    WHERE t.id_usuario = ? AND YEAR(t.data_transacao) = ? AND MONTH(t.data_transacao) = ?
";

$params_grafico = [$usuario_id, $ano, $mes];

if ($tipo_transacao != 'todos') {
    $sql_grafico .= " AND t.tipo = ?";
    $params_grafico[] = $tipo_transacao;
}

$sql_grafico .= " GROUP BY c.nome ORDER BY total DESC";

$stmt_grafico = $pdo->prepare($sql_grafico);
$stmt_grafico->execute($params_grafico);
$dados_grafico = $stmt_grafico->fetchAll(PDO::FETCH_ASSOC);

$labels_grafico = array_column($dados_grafico, 'nome');
$valores_grafico = array_column($dados_grafico, 'total');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatórios - Invicta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body class="<?= htmlspecialchars($_SESSION['tema'] ?? 'claro') ?>">
    <header class="bg-primary text-white text-center p-4">
        <h1>Invicta - Relatórios</h1>
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
        <h3 class="mb-4">Relatório de Transações por Mês</h3>
        
        <form method="GET" class="row g-3 align-items-end mb-4">
            <div class="col-md-5">
                <label for="mes_ano" class="form-label">Mês e Ano</label>
                <input type="month" name="mes_ano" id="mes_ano" class="form-control" value="<?= htmlspecialchars($mes_ano) ?>" required>
            </div>
            <div class="col-md-5">
                <label for="tipo" class="form-label">Tipo de Transação</label>
                <select name="tipo" id="tipo" class="form-select">
                    <option value="todos" <?= ($tipo_transacao == 'todos') ? 'selected' : '' ?>>Todas</option>
                    <option value="receita" <?= ($tipo_transacao == 'receita') ? 'selected' : '' ?>>Receitas</option>
                    <option value="despesa" <?= ($tipo_transacao == 'despesa') ? 'selected' : '' ?>>Despesas</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
        </form>

        <?php if (empty($transacoes)): ?>
            <div class="alert alert-info text-center" role="alert">
                Nenhuma transação encontrada para o período selecionado.
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Resumo por Categoria</h5>
                    <canvas id="graficoRelatorio"></canvas>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Tabela de Transações</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transacoes as $transacao): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($transacao['data_transacao']))) ?></td>
                                    <td><?= htmlspecialchars($transacao['descricao']) ?></td>
                                    <td><?= htmlspecialchars($transacao['categoria_nome']) ?></td>
                                    <td class="<?= $transacao['tipo'] == 'receita' ? 'text-success' : 'text-danger' ?>">
                                        <?= $transacao['tipo'] == 'receita' ? 'Receita' : 'Despesa' ?>
                                    </td>
                                    <td class="<?= $transacao['tipo'] == 'receita' ? 'text-success' : 'text-danger' ?>">
                                        R$ <?= number_format($transacao['valor'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <footer class="bg-light text-center text-muted py-4 mt-5">
        <p>&copy; <?= date("Y") ?> Invicta. Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const labels = <?= json_encode($labels_grafico) ?>;
            const data = <?= json_encode($valores_grafico) ?>;
            const tipo = "<?= htmlspecialchars($tipo_transacao) ?>";

            if (data.length > 0) {
                const ctx = document.getElementById('graficoRelatorio').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: tipo === 'receita' ? 'Receitas por Categoria' : (tipo === 'despesa' ? 'Despesas por Categoria' : 'Transações por Categoria'),
                            data: data,
                            backgroundColor: tipo === 'receita' ? 'rgba(40, 167, 69, 0.6)' : 'rgba(220, 53, 69, 0.6)',
                            borderColor: tipo === 'receita' ? '#28a745' : '#dc3545',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>