<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

$id_usuario = $_SESSION['usuario_id'];



// Dados do usuário
$stmt = $pdo->prepare("SELECT nome_completo, avatar FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$user = $stmt->fetch();
$avatar = $user['avatar'] ?? '../assets/avatar-padrao.png';

// === FILTRO DE MÊS ===
$ano = date('Y');
$mes = date('m');

if (isset($_GET['mes'])) {
    $data = $_GET['mes'] . '-01';
    $ano = substr($data, 0, 4);
    $mes = substr($data, 5, 2);
}

$data_inicio = "$ano-$mes-01";
$data_fim = date('Y-m-t', strtotime($data_inicio));

// === TOTAIS DO MÊS SELECIONADO ===
$stmt = $pdo->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN tipo = 'Entrada' THEN valor ELSE 0 END), 0) AS receitas,
        COALESCE(SUM(CASE WHEN tipo = 'Saída' THEN valor ELSE 0 END), 0) AS despesas
    FROM transacoes 
    WHERE id_usuario = ? AND data_transacao BETWEEN ? AND ?
");
$stmt->execute([$id_usuario, $data_inicio, $data_fim]);
$totais = $stmt->fetch();

$receitas = (float) $totais['receitas'];
$despesas = (float) $totais['despesas'];
$saldo = $receitas - $despesas;

// === DESPESAS POR CATEGORIA (gráfico pizza) ===
$stmt = $pdo->prepare("
    SELECT c.nome_categoria, COALESCE(SUM(t.valor), 0) AS total
    FROM transacoes t
    JOIN categorias c ON t.id_categoria = c.id_categoria
    WHERE t.id_usuario = ? AND t.tipo = 'Saída'
      AND t.data_transacao BETWEEN ? AND ?
    GROUP BY c.id_categoria
    ORDER BY total DESC
");
$stmt->execute([$id_usuario, $data_inicio, $data_fim]);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === RECEITAS x DESPESAS (últimos 6 meses) ===
$labels_mes = [];
$receitas_6 = [];
$despesas_6 = [];

for ($i = 5; $i >= 0; $i--) {
    $data = date('Y-m', strtotime("-$i month"));
    $inicio = "$data-01";
    $fim = date('Y-m-t', strtotime($inicio));

    $labels_mes[] = date('M', strtotime($inicio));

    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN tipo='Entrada' THEN valor ELSE 0 END),0) AS rec,
            COALESCE(SUM(CASE WHEN tipo='Saída' THEN valor ELSE 0 END),0) AS desp
        FROM transacoes WHERE id_usuario = ? AND data_transacao BETWEEN ? AND ?
    ");
    $stmt->execute([$id_usuario, $inicio, $fim]);
    $row = $stmt->fetch();
    $receitas_6[] = (float) $row['rec'];
    $despesas_6[] = (float) $row['desp'];
}

// Buscar saldo do usuário
$sqlValores = $pdo->prepare("SELECT saldo_inicial FROM valores_usuarios WHERE id_usuario=? LIMIT 1");
$sqlValores->execute([$id_usuario]);
$valores = $sqlValores->fetch(PDO::FETCH_ASSOC);
$saldo = $valores['saldo_inicial'] ?? 0;

$erro = "";


// =======================
// Valores financeiros fixos (renda, limite)
// =======================
$sqlValores = $pdo->prepare("
    SELECT saldo_inicial, renda_prevista, limite_gastos
    FROM valores_usuarios
    WHERE id_usuario = ?
    LIMIT 1
");
$sqlValores->execute([$id_usuario]);
$valores = $sqlValores->fetch(PDO::FETCH_ASSOC);

$renda = $valores['renda_prevista'] ?? 0;
$limite = $valores['limite_gastos'] ?? 0;

// =======================
// Metas financeiras
// =======================
$stmtMetas = $pdo->prepare("
    SELECT id_meta, nome_meta, valor_meta, valor_atual
    FROM metas_financeiras
    WHERE id_usuario = ?
    ORDER BY data_criacao DESC
");
$stmtMetas->execute([$id_usuario]);
$metas = $stmtMetas->fetchAll(PDO::FETCH_ASSOC);

foreach ($metas as &$meta) {
    $meta['progresso'] = ($meta['valor_meta'] > 0)
        ? min(($meta['valor_atual'] / $meta['valor_meta']) * 100, 100)
        : 0;
}
unset($meta);

// =======================
// Últimas transações
// =======================
$stmtTransacoes = $pdo->prepare("
    SELECT t.*, c.nome_categoria
    FROM transacoes t
    LEFT JOIN categorias c ON t.id_categoria = c.id_categoria
    WHERE t.id_usuario = ?
    ORDER BY t.data_transacao DESC
    LIMIT 5
");
$stmtTransacoes->execute([$id_usuario]);
$ultimasTransacoes = $stmtTransacoes->fetchAll(PDO::FETCH_ASSOC);

// =======================
// Despesas mensais
// =======================
$sqlDespesas = $pdo->prepare("
    SELECT MONTH(data_transacao) AS mes, SUM(valor) AS total
    FROM transacoes
    WHERE tipo = 'Saída' AND id_usuario = ?
    GROUP BY MONTH(data_transacao)
");
$sqlDespesas->execute([$id_usuario]);

$despesasMensais = array_fill(1, 12, 0);
foreach ($sqlDespesas->fetchAll(PDO::FETCH_ASSOC) as $d) {
    $despesasMensais[$d['mes']] = (float) $d['total'];
}

// =======================
// Receitas mensais
// =======================
$sqlReceitas = $pdo->prepare("
    SELECT MONTH(data_transacao) AS mes, SUM(valor) AS total
    FROM transacoes
    WHERE tipo = 'Entrada' AND id_usuario = ?
    GROUP BY MONTH(data_transacao)
");
$sqlReceitas->execute([$id_usuario]);

$receitasMensais = array_fill(1, 12, 0);
foreach ($sqlReceitas->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $receitasMensais[$r['mes']] = (float) $r['total'];
}

// =======================
// Renda e limite
// =======================
$sqlValores = $pdo->prepare("SELECT renda_prevista, limite_gastos FROM valores_usuarios WHERE id_usuario = ? LIMIT 1");
$sqlValores->execute([$id_usuario]);
$val = $sqlValores->fetch(PDO::FETCH_ASSOC);
$renda = $val['renda_prevista'] ?? 0;
$limite = $val['limite_gastos'] ?? 0;

// NOTIFICAÇÃO DE LIMITE DE GASTOS (para o sininho)
// =======================
$mesAtual = date('Y-m');
$stmtGasto = $pdo->prepare("SELECT COALESCE(SUM(valor), 0) FROM transacoes WHERE id_usuario = ? AND tipo = 'Saída' AND DATE_FORMAT(data_transacao, '%Y-%m') = ?");
$stmtGasto->execute([$id_usuario, $mesAtual]);
$gastoMes = $stmtGasto->fetchColumn();

$porcentagem = $limite > 0 ? ($gastoMes / $limite) * 100 : 0;
$totalNotificacoes = 0;

if ($porcentagem >= 50) {
    $totalNotificacoes = 1;

    if ($porcentagem >= 95) {
        $notifTitulo = "VOCÊ ULTRAPASSOU O LIMITE!";
        $notifTexto = "Gastou R$ " . number_format($gastoMes, 2, ',', '.') . " de R$ " . number_format($limite, 2, ',', '.');
        $notifIcone = "alert-triangle";
        $notifCor = "text-red-600";
    } elseif ($porcentagem >= 90) {
        $notifTitulo = "QUASE NO LIMITE!";
        $notifTexto = "Você já usou " . number_format($porcentagem, 1) . "% do limite.";
        $notifIcone = "alert-octagon";
        $notifCor = "text-red-500";
    } elseif ($porcentagem >= 75) {
        $notifTitulo = "Cuidado com os gastos!";
        $notifTexto = "Você já gastou " . number_format($porcentagem, 1) . "% do limite.";
        $notifIcone = "bell-ring";
        $notifCor = "text-orange-600";
    } else {
        $notifTitulo = "Metade do limite alcançada";
        $notifTexto = "Você já usou " . number_format($porcentagem, 1) . "% do orçamento.";
        $notifIcone = "bell";
        $notifCor = "text-yellow-600";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<script>
    if (localStorage.theme === 'dark') {
        document.documentElement.classList.add('dark');
    }
</script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Invicta Finanças</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@latest/dist/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { crimson: { 500: '#EF4B2A', 600: '#D94426' } } } }
        }
    </script>

    <style>
        main {
            transition: font-size 0.25s ease;
        }

        #resetText {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 flex text-gray-900 dark:text-gray-100 transition-colors duration-300">

    <?php $activePage = 'relatorios';
    include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header
            class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center transition-colors duration-300">
            <h2 class="text-xl font-bold">Relatórios</h2>

            <div class="flex items-center gap-3">
                <!-- Acessibilidade -->
                <div class="flex items-center gap-2">
                    <button id="increaseText" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        title="Aumentar fonte">
                        <i data-feather="zoom-in" class="text-gray-600 dark:text-gray-300"></i>
                    </button>
                    <button id="decreaseText" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        title="Diminuir fonte">
                        <i data-feather="zoom-out" class="text-gray-600 dark:text-gray-300"></i>
                    </button>
                    <button id="resetText" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        title="Redefinir tamanho da fonte">
                        <i data-feather="refresh-ccw" class="text-gray-600 dark:text-gray-300"></i>
                    </button>
                </div>

                <!-- Dark mode toggle -->
                <button id="darkToggle" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                    title="Alternar modo escuro">
                    <i data-feather="moon" class="text-gray-600 dark:text-gray-300"></i>
                </button>

                <!-- Notificações -->
                <!-- NOTIFICAÇÕES FUNCIONAIS -->
                <div class="relative">
                    <button id="notificacoesBtn"
                        class="relative p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        title="Notificações">
                        <i data-feather="bell" class="w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                        <?php if ($totalNotificacoes > 0): ?>
                            <span
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center animate-pulse">
                                <?= $totalNotificacoes ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <!-- Dropdown de Notificações -->
                    <div id="notificacoesDropdown"
                        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden opacity-0 invisible transition-all duration-300 transform scale-95 origin-top-right z-50">
                        <div class="p-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                            <h3 class="font-bold text-lg">Notificações</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <?php if ($porcentagem >= 50): ?>
                                <div
                                    class="p-4 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 mt-1">
                                            <i data-feather="<?= $notifIcone ?>" class="w-8 h-8 <?= $notifCor ?>"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-sm <?= $notifCor ?>"><?= $notifTitulo ?></p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><?= $notifTexto ?></p>
                                            <div class="text-xs text-gray-500 mt-2">
                                                Gasto: R$ <?= number_format($gastoMes, 2, ',', '.') ?> •
                                                Limite: R$ <?= number_format($limite, 2, ',', '.') ?>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-2">Hoje • <?= date('H:i') ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                                    <i data-feather="bell-off" class="w-16 h-16 mx-auto mb-4 opacity-50"></i>
                                    <p class="font-medium">Tudo tranquilo!</p>
                                    <p class="text-sm">Nenhuma notificação no momento.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-900 text-center border-t dark:border-gray-700">
                            <a href="#" class="text-sm text-crimson-500 hover:text-crimson-600 font-medium">Ver
                                todas</a>
                        </div>
                    </div>
                </div>

                <!-- Perfil -->
                <div class="flex items-center gap-2">
                    <img src="<?= $avatar ?>" alt="Avatar" class="w-10 h-10 rounded-full">
                    <span class="font-medium"><?= htmlspecialchars($user['nome_completo']) ?></span>
                </div>
            </div>
        </header>

        <main class="p-6 flex-1 overflow-y-auto space-y-6">
            <form class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="month" name="mes" value="<?= $ano . '-' . $mes ?>"
                        class="border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-800">
                    <button type="submit"
                        class="bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition">
                        Filtrar
                    </button>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total Receitas</p>
                        <h3 class="text-2xl font-bold text-green-500">R$ <?= number_format($receitas, 2, ',', '.') ?>
                        </h3>
                    </div>
                    <i data-feather="arrow-up" class="text-gray-400"></i>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total Despesas</p>
                        <h3 class="text-2xl font-bold text-red-500">R$ <?= number_format($despesas, 2, ',', '.') ?></h3>
                    </div>
                    <i data-feather="arrow-down" class="text-gray-400"></i>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Saldo</p>
                        <h3 class="text-2xl font-bold text-crimson-500">R$ <?= number_format($saldo, 2, ',', '.') ?>
                        </h3>
                    </div>
                    <i data-feather="dollar-sign" class="text-gray-400"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                    <h4 class="text-gray-700 dark:text-gray-200 font-semibold mb-4">Despesas por Categoria</h4>
                    <canvas id="despesasCategoriaChart" class="w-full h-64"></canvas>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                    <h4 class="text-gray-700 dark:text-gray-200 font-semibold mb-4">Receitas x Despesas</h4>
                    <canvas id="receitasDespesasChart" class="w-full h-64"></canvas>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 mt-4">
                <button onclick="exportarPDF()"
                    class="flex items-center gap-2 bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition">
                    <i data-feather="file-text"></i> Exportar PDF
                </button>
                <button onclick="exportarExcel()"
                    class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                    <i data-feather="file"></i> Exportar Planilha
                </button>
                <button onclick="window.print()"
                    class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    <i data-feather="printer"></i> Imprimir
                </button>
            </div>
        </main>
    </div>

    <script>
        feather.replace();

        // === MODO ESCURO ===
        const html = document.documentElement;
        if (localStorage.theme === 'dark') html.classList.add('dark');
        document.getElementById('darkToggle').addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
        });

        // === FONTE AJUSTÁVEL ===
        let fontSize = parseInt(localStorage.getItem('fontSize')) || 100;
        html.style.fontSize = fontSize + '%';
        const updateFont = () => {
            html.style.fontSize = fontSize + '%';
            localStorage.setItem('fontSize', fontSize);
            document.getElementById('resetText').style.display = fontSize !== 100 ? 'inline-flex' : 'none';
        };
        document.getElementById('increaseText').onclick = () => { fontSize = Math.min(150, fontSize + 10); updateFont(); };
        document.getElementById('decreaseText').onclick = () => { fontSize = Math.max(80, fontSize - 10); updateFont(); };
        document.getElementById('resetText').onclick = () => { fontSize = 100; updateFont(); };
        updateFont();

        // === GRÁFICO PIZZA (dados reais) ===
        new Chart(document.getElementById('despesasCategoriaChart'), {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($categorias, 'nome_categoria') ?: ['Sem dados']) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($categorias, 'total') ?: [1]) ?>,
                    backgroundColor: ['#efd211ff', '#7c19cdff', '#5367d7ff', '#37ff00ff', '#ff6b6b', '#4ecdc4']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // === GRÁFICO LINHA (dados reais) ===
        new Chart(document.getElementById('receitasDespesasChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($labels_mes) ?>,
                datasets: [
                    { label: 'Receitas', data: <?= json_encode($receitas_6) ?>, borderColor: '#2aef4b', backgroundColor: 'rgba(42,239,75,0.2)', tension: 0.4, fill: true },
                    { label: 'Despesas', data: <?= json_encode($despesas_6) ?>, borderColor: '#EF4B2A', backgroundColor: 'rgba(239,75,42,0.2)', tension: 0.4, fill: true }
                ]
            },
            options: { responsive: true }
        });

        // === EXPORTAR PDF ===
        function exportarPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(20);
            doc.text('Relatório Financeiro - Invicta Finanças', 20, 20);
            doc.setFontSize(12);
            doc.text(`Período: <?= date('m/Y', strtotime($data_inicio)) ?>`, 20, 35);
            doc.text(`Receitas: R$ <?= number_format($receitas, 2, ',', '.') ?>`, 20, 45);
            doc.text(`Despesas: R$ <?= number_format($despesas, 2, ',', '.') ?>`, 20, 55);
            doc.text(`Saldo: R$ <?= number_format($saldo, 2, ',', '.') ?>`, 20, 65);
            doc.save('relatorio_invicta_<?= $ano . $mes ?>.pdf');
        }

        // === EXPORTAR EXCEL ===
        function exportarExcel() {
            const wb = XLSX.utils.book_new();
            const data = [
                ["Relatório Invicta Finanças"], ["Período: <?= date('m/Y', strtotime($data_inicio)) ?>"],
                [], ["Receitas", <?= $receitas ?>], ["Despesas", <?= $despesas ?>], ["Saldo", <?= $saldo ?>],
                [], ["Despesas por Categoria"], ["Categoria", "Valor"],
                ...<?= json_encode(array_map(fn($c) => [$c['nome_categoria'], (float) $c['total']], $categorias)) ?>
            ];
            const ws = XLSX.utils.aoa_to_sheet(data);
            XLSX.utils.book_append_sheet(wb, ws, "Relatório");
            XLSX.writeFile(wb, "relatorio_invicta_<?= $ano . $mes ?>.xlsx");
        }

        // Dropdown de notificações
        document.getElementById('notificacoesBtn').addEventListener('click', function (e) {
            e.stopPropagation();
            const dropdown = document.getElementById('notificacoesDropdown');
            const isOpen = dropdown.classList.contains('opacity-100');

            // Fecha todos os dropdowns
            document.querySelectorAll('[id*="Dropdown"]').forEach(d => {
                d.classList.remove('opacity-100', 'visible', 'scale-100');
                d.classList.add('opacity-0', 'invisible', 'scale-95');
            });

            if (!isOpen) {
                dropdown.classList.remove('opacity-0', 'invisible', 'scale-95');
                dropdown.classList.add('opacity-100', 'visible', 'scale-100');
                feather.replace();
            }
        });

        // Fecha ao clicar fora
        document.addEventListener('click', () => {
            document.getElementById('notificacoesDropdown').classList.remove('opacity-100', 'visible', 'scale-100');
            document.getElementById('notificacoesDropdown').classList.add('opacity-0', 'invisible', 'scale-95');
        });

    </script>
</body>

</html>