<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';


$id_usuario = $_SESSION['usuario_id'];


// =======================
// Dados do usuário
// =======================
$stmtUser = $pdo->prepare("SELECT nome_completo, avatar FROM usuarios WHERE id_usuario = ?");
$stmtUser->execute([$id_usuario]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);
$avatar = $user['avatar'] ?: '/assets/default-avatar.png';

// =======================
// Saldo real (Entradas - Saídas)
// =======================
$sqlSaldo = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN tipo = 'Entrada' THEN valor ELSE 0 END) -
        SUM(CASE WHEN tipo = 'Saída' THEN valor ELSE 0 END) AS saldo
    FROM transacoes
    WHERE id_usuario = ?
");
$sqlSaldo->execute([$id_usuario]);
$saldo = $sqlSaldo->fetchColumn() ?? 0;

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
        $notifTexto  = "Gastou R$ ".number_format($gastoMes,2,',','.')." de R$ ".number_format($limite,2,',','.');
        $notifIcone  = "alert-triangle";
        $notifCor    = "text-red-600";
    } elseif ($porcentagem >= 90) {
        $notifTitulo = "QUASE NO LIMITE!";
        $notifTexto  = "Você já usou ".number_format($porcentagem,1)."% do limite.";
        $notifIcone  = "alert-octagon";
        $notifCor    = "text-red-500";
    } elseif ($porcentagem >= 75) {
        $notifTitulo = "Cuidado com os gastos!";
        $notifTexto  = "Você já gastou ".number_format($porcentagem,1)."% do limite.";
        $notifIcone  = "bell-ring";
        $notifCor    = "text-orange-600";
    } else {
        $notifTitulo = "Metade do limite alcançada";
        $notifTexto  = "Você já usou ".number_format($porcentagem,1)."% do orçamento.";
        $notifIcone  = "bell";
        $notifCor    = "text-yellow-600";
    }
}

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
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex">

<?php
$activePage = 'dashboard';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Topbar -->
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


                <!-- NOTIFICAÇÕES FUNCIONAIS -->
<div class="relative">
    <button id="notificacoesBtn" class="relative p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition" title="Notificações">
        <i data-feather="bell" class="w-6 h-6 text-gray-600 dark:text-gray-300"></i>
        <?php if ($totalNotificacoes > 0): ?>
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center animate-pulse">
            <?= $totalNotificacoes ?>
        </span>
        <?php endif; ?>
    </button>

    <!-- Dropdown de Notificações -->
    <div id="notificacoesDropdown" class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden opacity-0 invisible transition-all duration-300 transform scale-95 origin-top-right z-50">
        <div class="p-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <h3 class="font-bold text-lg">Notificações</h3>
        </div>
        <div class="max-h-96 overflow-y-auto">
            <?php if ($porcentagem >= 50): ?>
            <div class="p-4 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
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
            <a href="#" class="text-sm text-crimson-500 hover:text-crimson-600 font-medium">Ver todas</a>
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

    <main class="p-6 space-y-6">

        <!-- Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Saldo Atual -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <p class="text-gray-500">Saldo Atual</p>
                <h3 class="text-2xl font-bold text-green-500" data-value="<?= $saldo ?>">R$ 0,00</h3>
            </div>

            <!-- Receita Prevista -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <p class="text-gray-500">Receita Prevista</p>
                <h3 class="text-2xl font-bold text-green-600" data-value="<?= $renda ?>">R$ 0,00</h3>
            </div>

            <!-- Limite de Gastos -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <p class="text-gray-500">Limite de Gastos</p>
                <h3 class="text-2xl font-bold text-crimson-500" data-value="<?= $limite ?>">R$ 0,00</h3>
            </div>
        </div>

        <!-- Metas -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h4 class="font-semibold mb-4">Metas Financeiras</h4>

            <?php foreach ($metas as $meta): ?>
                <div class="mb-3">
                    <p class="text-sm"><?= htmlspecialchars($meta['nome_meta']) ?></p>
                    <div class="w-full bg-gray-300 h-3 rounded-full mt-1">
                        <div class="bg-crimson-500 h-3 rounded-full"
                             data-width="<?= round($meta['progresso']) ?>%"
                             style="width:0"></div>
                    </div>
                    <p class="text-sm mt-1">Progresso: <?= round($meta['progresso']) ?>%</p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-4">Despesas Mensais</h4>
                <canvas id="despesasChart"></canvas>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-4">Receitas x Despesas</h4>
                <canvas id="receitasChart"></canvas>
            </div>
        </div>

        <!-- Transações -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h4 class="font-semibold mb-4">Últimas Transações</h4>

            <table class="w-full">
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Tipo</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($ultimasTransacoes as $t): ?>
                    <tr class="border-b border-gray-200">
                        <td><?= date('d/m/Y', strtotime($t['data_transacao'])) ?></td>
                        <td><?= htmlspecialchars($t['descricao']) ?></td>
                        <td class="<?= $t['tipo']=='Entrada' ? 'text-green-500' : 'text-red-500' ?>">
                            R$ <?= number_format($t['valor'], 2, ',', '.') ?>
                        </td>
                        <td><?= $t['tipo'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<!-- ========================= -->
<!-- VARIÁVEIS PHP → JS -->
<!-- ========================= -->
<script>
const despesas = <?= json_encode(array_values($despesasMensais)) ?>;
const receitas  = <?= json_encode(array_values($receitasMensais)) ?>;
</script>

<!-- JS -->
<script>
feather.replace();

// Animação dos números
function animateNumber(el) {
    const end = parseFloat(el.dataset.value);
    let start = 0;
    const step = end / 50;

    const interval = setInterval(() => {
        start += step;
        if (start >= end) {
            start = end;
            clearInterval(interval);
        }
        el.textContent = start.toLocaleString('pt-BR', {style:'currency', currency:'BRL'});
    }, 20);
}

document.querySelectorAll('[data-value]').forEach(animateNumber);

// Animação das barras
document.querySelectorAll('[data-width]').forEach(bar => {
    setTimeout(() => {
        bar.style.transition = "width 1s ease";
        bar.style.width = bar.dataset.width;
    }, 100);
});

// Gráfico despesas
new Chart(document.getElementById('despesasChart'), {
    type: 'bar',
    data: {
        labels: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        datasets: [{
            label: 'Despesas',
            data: despesas,
            backgroundColor: 'rgba(239, 75, 42, 0.7)',
            borderRadius: 5
        }]
    }
});

// Gráfico Receitas x Despesas
new Chart(document.getElementById('receitasChart'), {
    type: 'line',
    data: {
        labels: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        datasets: [
            {
                label: 'Receitas',
                data: receitas,
                borderColor: '#2aef4b',
                backgroundColor: 'rgba(42,239,75,0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Despesas',
                data: despesas,
                borderColor: '#ef4b2a',
                backgroundColor: 'rgba(239,75,42,0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    }
});
// Dropdown de notificações
document.getElementById('notificacoesBtn').addEventListener('click', function(e) {
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

        // Charts
        const ctx1 = document.getElementById('despesasCategoriaChart').getContext('2d');
        new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: ['Alimentação', 'Transporte', 'Contas', 'Lazer'],
                datasets: [{
                    data: [450, 200, 300, 100],
                    backgroundColor: ['#efd211ff', '#7c19cdff', '#5367d7ff', '#37ff00ff']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        const ctx2 = document.getElementById('receitasDespesasChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [
                    { label: 'Receitas', data: [2000, 1800, 2200, 2400, 2300, 2500], borderColor: '#2aef4b', backgroundColor: 'rgba(42,239,75,0.2)', tension: 0.4, fill: true },
                    { label: 'Despesas', data: [1200, 1500, 1000, 1700, 1400, 1600], borderColor: '#EF4B2A', backgroundColor: 'rgba(239,75,42,0.2)', tension: 0.4, fill: true }
                ]
            },
            options: { responsive: true }
        });


        

    </script>

</body>
</html>
<?