<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

$id_usuario = $_SESSION['usuario_id'];

// =======================
// Valores financeiros
// =======================
$sqlValores = $pdo->prepare("
    SELECT saldo_inicial, renda_prevista, limite_gastos
    FROM valores_usuarios
    WHERE id_usuario = ?
    LIMIT 1
");
$sqlValores->execute([$id_usuario]);
$valores = $sqlValores->fetch(PDO::FETCH_ASSOC);

// Evita erro caso o usuário ainda não tenha valores cadastrados
$saldo = $valores['saldo_inicial'] ?? 0;
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

// Calcula progresso de cada meta
foreach ($metas as &$meta) {
    $meta['progresso'] = ($meta['valor_meta'] > 0) ? min(($meta['valor_atual'] / $meta['valor_meta']) * 100, 100) : 0;
}
unset($meta); // limpar referência

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


?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Invicta Finanças</title>
    <!-- Tailwind + Feather + Chart.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        crimson: { 500: '#EF4B2A', 600: '#D94426' }
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            font-size: 100%;
            transition: font-size 0.25s ease;
        }

        main {
            transition: font-size 0.25s ease;
        }

        #resetText {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 flex text-gray-900 dark:text-gray-100 transition-colors duration-300">

    <?php
    $activePage = 'dashboard';
    include __DIR__ . '/../includes/sidebar.php';
    ?>

    <!-- Main -->
    <div class="flex-1 flex flex-col">
        <!-- Topbar -->
        <header
            class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center transition-colors duration-300">
            <h2 class="text-xl font-bold">Dashboard</h2>

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
                <button class="relative" title="Notificações">
                    <i data-feather="bell" class="text-gray-600 dark:text-gray-300"></i>
                    <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1">5</span>
                </button>

                <!-- Perfil -->
                <div class="flex items-center gap-2">
                    <img src="<?= $avatar ?>" alt="Avatar" class="w-10 h-10 rounded-full">
                    <span class="font-medium"><?= htmlspecialchars($user['nome_completo']) ?></span>
                </div>
            </div>
        </header>

        <!-- Conteúdo -->
        <main class="p-6 flex-1 overflow-y-auto space-y-6">
            <!-- Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Saldo Atual</p>
                        <h3 class="text-2xl font-bold text-green-500" data-value="<?= $saldo ?>">R$ 0,00</h3>
                    </div>
                    <i data-feather="dollar-sign" class="text-gray-400"></i>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Receita Prevista</p>
                        <h3 class="text-2xl font-bold text-green-600" data-value="<?= $renda ?>">R$ 0,00</h3>
                    </div>
                    <i data-feather="arrow-up" class="text-gray-400"></i>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Limite de Gastos</p>
                        <h3 class="text-2xl font-bold text-crimson-500" data-value="<?= $limite ?>">R$ 0,00</h3>
                    </div>
                    <i data-feather="arrow-down" class="text-gray-400"></i>
                </div>
            </div>

            <!-- Metas -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-4">Metas Financeiras</h4>
                <div class="space-y-3">
                    <?php foreach ($metas as $meta): ?>
                        <div class="p-2 bg-white dark:bg-gray-800 rounded">
                            <p class="text-gray-500 dark:text-gray-400 text-sm">
                                <?= htmlspecialchars($meta['nome_meta']) ?> - R$
                                <?= number_format($meta['valor_meta'], 2, ',', '.') ?>
                            </p>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mt-1">
                                <div class="bg-crimson-500 h-3 rounded-full" data-width="<?= round($meta['progresso']) ?>%"
                                    style="width: 0;"></div>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                                Progresso: <?= round($meta['progresso']) ?>%
                            </p>
                        </div>

                    <?php endforeach; ?>
                </div>
            </div>


            <!-- Gráficos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                    <h4 class="font-semibold mb-4">Despesas Mensais</h4>
                    <canvas id="despesasChart" class="w-full h-64"></canvas>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                    <h4 class="font-semibold mb-4">Receitas x Despesas</h4>
                    <canvas id="receitasChart" class="w-full h-64"></canvas>
                </div>
            </div>

            <!-- Transações -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-4">Últimas Transações</h4>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Data</th>
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Descrição</th>
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Valor</th>
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ultimasTransacoes)): ?>
                            <?php foreach ($ultimasTransacoes as $t): ?>
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 px-4"><?= date('d/m/Y', strtotime($t['data_transacao'])) ?></td>
                                    <td class="py-2 px-4"><?= htmlspecialchars($t['descricao']) ?></td>
                                    <td
                                        class="py-2 px-4 <?= $t['tipo'] === 'Entrada' ? 'text-green-500' : 'text-crimson-500' ?> font-semibold">
                                        R$ <?= number_format($t['valor'], 2, ',', '.') ?>
                                    </td>
                                    <td class="py-2 px-4"><?= $t['tipo'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-2 px-4 text-center text-gray-500 dark:text-gray-400">
                                    Nenhuma transação encontrada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

    </div>
    </main>

    <!-- Scripts -->
    <script>
        feather.replace();

        const html = document.documentElement;
        const toggle = document.getElementById('darkToggle');

        // Tema escuro
        if (localStorage.theme === 'dark') html.classList.add('dark');

        toggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
        });

        // Controle de fonte
        const increaseText = document.getElementById('increaseText');
        const decreaseText = document.getElementById('decreaseText');
        const resetText = document.getElementById('resetText');

        let fontSize = parseInt(localStorage.getItem('fontSize')) || 100;
        document.documentElement.style.fontSize = `${fontSize}%`;

        function updateFontSize() {
            document.documentElement.style.fontSize = `${fontSize}%`;
            localStorage.setItem('fontSize', fontSize);
            resetText.style.display = (fontSize !== 100) ? 'inline-flex' : 'none';
        }

        increaseText.addEventListener('click', () => { fontSize = Math.min(150, fontSize + 10); updateFontSize(); });
        decreaseText.addEventListener('click', () => { fontSize = Math.max(80, fontSize - 10); updateFontSize(); });
        resetText.addEventListener('click', () => { fontSize = 100; updateFontSize(); });
        resetText.style.display = (fontSize !== 100) ? 'inline-flex' : 'none';

        // === Gráficos ===
        const ctx1 = document.getElementById('despesasChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [{
                    label: 'Despesas',
                    data: [1200, 1500, 1000, 1700, 1400, 1600],
                    backgroundColor: 'rgba(239, 75, 42, 0.7)',
                    borderRadius: 5
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        const ctx2 = document.getElementById('receitasChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [
                    { label: 'Receitas', data: [2000, 1800, 2200, 2400, 2300, 2500], borderColor: '#2aef4b', backgroundColor: 'rgba(42,239,75,0.1)', tension: 0.4, fill: true },
                    { label: 'Despesas', data: [1200, 1500, 1000, 1700, 1400, 1600], borderColor: '#ef4b2a', backgroundColor: 'rgba(239,75,42,0.1)', tension: 0.4, fill: true }
                ]
            },
            options: { responsive: true }
        });

        // === Animação das barras de progresso das metas ===
        document.querySelectorAll('.w-full.bg-gray-200 .bg-crimson-500').forEach(bar => {
            const finalWidth = bar.getAttribute('data-width') || bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.transition = "width 1s ease-in-out";
                bar.style.width = finalWidth;
            }, 100);
        });

        // === Animação dos números dos cards ===
        function animateNumber(element, duration = 1000) {
            const finalValue = parseFloat(element.getAttribute('data-value')) || 0;
            let start = 0;
            const increment = finalValue / (duration / 20); // atualiza a cada 20ms
            const formatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

            const counter = setInterval(() => {
                start += increment;
                if (start >= finalValue) {
                    start = finalValue;
                    clearInterval(counter);
                }
                element.textContent = formatter.format(start);
            }, 20);
        }

        // Aplica animação em todos os elementos com data-value
        document.querySelectorAll('[data-value]').forEach(el => animateNumber(el));
    </script>


</body>

</html>