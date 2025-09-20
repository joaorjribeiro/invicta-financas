<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: usuario/login.php');
    exit;
}
require 'includes/db_connect.php';
require 'includes/functions.php';

$usuario_id = $_SESSION['user_id'];
$saldo = calcularSaldo($pdo, $usuario_id);
$mes_ano = date('Y-m');
$alertas = verificarLimiteGastos($pdo, $usuario_id, $mes_ano);

$stmt = $pdo->prepare("
    SELECT t.*, c.nome as categoria_nome 
    FROM transacoes t 
    JOIN categorias c ON c.id = t.id_categoria 
    WHERE t.id_usuario = ? 
    ORDER BY t.data_transacao DESC 
    LIMIT 10
");
$stmt->execute([$usuario_id]);
$transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt_despesas = $pdo->prepare("
    SELECT c.nome, SUM(t.valor) as total 
    FROM transacoes t 
    JOIN categorias c ON c.id = t.id_categoria 
    WHERE t.id_usuario = ? AND t.tipo = 'despesa' 
    GROUP BY c.nome
");
$stmt_despesas->execute([$usuario_id]);
$despesas_categoria = $stmt_despesas->fetchAll(PDO::FETCH_ASSOC);

$stmt_fluxo = $pdo->prepare("
    SELECT 
        DATE_FORMAT(data_transacao, '%Y-%m') as mes,
        SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END) as saldo_mensal
    FROM transacoes
    WHERE id_usuario = ?
    GROUP BY mes
    ORDER BY mes
");
$stmt_fluxo->execute([$usuario_id]);
$fluxo_caixa = $stmt_fluxo->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Invicta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css></head>
<body class="<?= htmlspecialchars($_SESSION['tema'] ?? 'claro') ?>">
    <header class="bg-primary text-white text-center p-4">
        <h1>Invicta - Dashboard de Finanças</h1>
    </header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-wallet2"></i> Invicta</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="transacoes/cadastrar_transacao.php"><i class="bi bi-plus-circle"></i> Nova Transação</a></li>
                    <li class="nav-item"><a class="nav-link" href="relatorios/gerar_relatorio.php"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a></li>
                    <li class="nav-item"><a class="nav-link" href="usuario/editar_usuario.php"><i class="bi bi-person"></i> Perfil</a></li>
                    <li class="nav-item"><a class="nav-link" href="usuario/logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container my-5">
        <?php if (!empty($alertas)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Atenção!</strong> Gastos acima do limite em:
                <ul>
                    <?php foreach ($alertas as $alerta): ?>
                        <li>Categoria **<?= htmlspecialchars($alerta['id_categoria']) ?>**: Gasto R$ **<?= number_format($alerta['total_gasto'], 2, ',', '.') ?>** (Limite: R$ **<?= number_format($alerta['valor_limite'], 2, ',', '.') ?>**)</li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-currency-dollar"></i> Saldo Atual</h5>
                        <p class="card-text fs-3">R$ <?= number_format($saldo, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Fluxo de Caixa Mensal</h5>
                <?php if (empty($fluxo_caixa)): ?>
                    <p class="text-center">Não há dados de fluxo de caixa para exibir.</p>
                <?php else: ?>
                    <canvas id="graficoFluxoCaixa"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Distribuição de Despesas por Categoria</h5>
                <?php if (empty($despesas_categoria)): ?>
                    <p class="text-center">Não há despesas para exibir no gráfico.</p>
                <?php else: ?>
                    <canvas id="graficoDespesas"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Transações Recentes</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Categoria</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Tipo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transacoes)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Nenhuma transação encontrada.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transacoes as $transacao): ?>
                                <tr>
                                    <td><?= htmlspecialchars($transacao['data_transacao']) ?></td>
                                    <td><?= htmlspecialchars($transacao['categoria_nome']) ?></td>
                                    <td><?= htmlspecialchars($transacao['descricao'] ?? '-') ?></td>
                                    <td class="<?= $transacao['tipo'] == 'receita' ? 'text-success' : 'text-danger' ?>">
                                        R$ <?= number_format($transacao['valor'], 2, ',', '.') ?>
                                    </td>
                                    <td><?= $transacao['tipo'] == 'receita' ? 'Receita' : 'Despesa' ?></td>
                                    <td>
                                        <a href="transacoes/editar_transacao.php?id=<?= $transacao['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                        <a href="transacoes/excluir_transacao.php?id=<?= $transacao['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <footer class="bg-light text-center text-muted py-4">
        <p>&copy; <?= date("Y") ?> Invicta. Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        const formatCurrency = (value) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);

        document.addEventListener('DOMContentLoaded', () => {
            const despesasData = <?= json_encode(array_column($despesas_categoria, 'total')) ?>;
            const despesasLabels = <?= json_encode(array_column($despesas_categoria, 'nome')) ?>;

            if (despesasData.length > 0) {
                const ctxDespesas = document.getElementById('graficoDespesas').getContext('2d');
                new Chart(ctxDespesas, {
                    type: 'pie',
                    data: {
                        labels: despesasLabels,
                        datasets: [{
                            data: despesasData,
                            backgroundColor: ['#dc3545', '#007bff', '#ffc107', '#17a2b8', '#fd7e14', '#6610f2', '#e83e8c', '#6f42c1', '#20c997', '#adb5bd']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: { callbacks: { label: (context) => {
                                let label = context.label || '';
                                if (label) label += ': ';
                                if (context.parsed !== null) label += formatCurrency(context.parsed);
                                return label;
                            }}}
                        }
                    }
                });
            }

            const fluxoData = <?= json_encode(array_column($fluxo_caixa, 'saldo_mensal')) ?>;
            const fluxoLabels = <?= json_encode(array_column($fluxo_caixa, 'mes')) ?>;

            if (fluxoData.length > 0) {
                const ctxFluxo = document.getElementById('graficoFluxoCaixa').getContext('2d');
                new Chart(ctxFluxo, {
                    type: 'line',
                    data: {
                        labels: fluxoLabels,
                        datasets: [{
                            label: 'Saldo Mensal',
                            data: fluxoData,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.2)',
                            fill: true,
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Valor (R$)' },
                                ticks: { callbacks: (value) => formatCurrency(value) }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (context) => {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) label += formatCurrency(context.parsed.y);
                                return label;
                            }}}
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>