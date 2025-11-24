<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

$activePage = 'valores';
$id_usuario = $_SESSION['usuario_id'];

// Buscar usuário (avatar/nome)
$sql = $pdo->prepare("SELECT nome_completo, avatar FROM usuarios WHERE id_usuario=?");
$sql->execute([$id_usuario]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

$avatarURLBase = "../assets/img/";
$avatarPathBase = __DIR__ . "/../assets/img/";
$avatar = (!empty($user['avatar']) && file_exists($avatarPathBase . $user['avatar']))
    ? $avatarURLBase . $user['avatar']
    : $avatarURLBase . "avatar_default.png";

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

// Buscar usuário (igual dashboard)
$id = $_SESSION['usuario_id'];

$sqlUser = $pdo->prepare("SELECT nome_completo, avatar FROM usuarios WHERE id_usuario = ?");
$sqlUser->execute([$id]);
$user = $sqlUser->fetch(PDO::FETCH_ASSOC);

$avatarURLBase = "../assets/img/";
$avatarPathBase = __DIR__ . "/../assets/img/";

if (!empty($user['avatar']) && file_exists($avatarPathBase . $user['avatar'])) {
    $avatar = $avatarURLBase . $user['avatar'] . "?v=" . time();
} else {
    $avatar = $avatarURLBase . "avatar_default.png";
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Valores - Invicta Finanças</title>

    <!-- MESMOS SCRIPTS DA DASHBOARD -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        crimson: { 500: "#EF4B2A", 600: "#D94426" },
                    },
                },
            },
        };
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
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col">
        <header
            class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center transition-colors duration-300">
            <h2 class="text-xl font-bold">Metas Financeiras</h2>
            <div class="flex items-center gap-3">
                <!-- Acessibilidade -->
                <div class="flex items-center gap-2">
                    <button id="increaseText" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        title="Aumentar fonte"><i data-feather="zoom-in"
                            class="text-gray-600 dark:text-gray-300"></i></button>
                    <button id="decreaseText" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        title="Diminuir fonte"><i data-feather="zoom-out"
                            class="text-gray-600 dark:text-gray-300"></i></button>
                    <button id="resetText" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        title="Redefinir tamanho da fonte"><i data-feather="refresh-ccw"
                            class="text-gray-600 dark:text-gray-300"></i></button>
                </div>
                <!-- Dark mode toggle -->
                <button id="darkToggle" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                    title="Alternar modo escuro"><i data-feather="moon"
                        class="text-gray-600 dark:text-gray-300"></i></button>
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
        <!-- CONTEÚDO DA PÁGINA VALORES -->
        <main class="p-6 overflow-y-auto">

            <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow">

                <h3 class="text-lg font-semibold mb-6">Configurar Valores Gerais</h3>

                <form method="POST" action="" class="space-y-6">

                    <div>
                        <label class="block mb-2 font-medium">Saldo Inicial</label>
                        <input type="number" step="0.01" name="saldo_inicial"
                            value="<?= $valores['saldo_inicial'] ?? '' ?>" placeholder="Ex: 1500.00"
                            class="w-full p-3 rounded border dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    </div>

                    <div>
                        <label class="block mb-2 font-medium">Renda Mensal Prevista</label>
                        <input type="number" step="0.01" name="renda_prevista"
                            value="<?= $valores['renda_prevista'] ?? '' ?>" placeholder="Ex: 3000.00"
                            class="w-full p-3 rounded border dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    </div>

                    <div>
                        <label class="block mb-2 font-medium">Limite de Gastos Mensais</label>
                        <input type="number" step="0.01" name="limite_gastos"
                            value="<?= $valores['limite_gastos'] ?? '' ?>" placeholder="Ex: 2000.00"
                            class="w-full p-3 rounded border dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-crimson-500 hover:bg-crimson-600 text-white px-6 py-3 rounded-lg transition">
                            Salvar Valores
                        </button>
                    </div>

                </form>


            </div>

        </main>
    </div>

    <!-- SCRIPTS IDENTICOS DA DASHBOARD -->
    <script>
        feather.replace();

        const html = document.documentElement;
        const toggle = document.getElementById("darkToggle");

        if (localStorage.theme === "dark") html.classList.add("dark");

        toggle.addEventListener("click", () => {
            html.classList.toggle("dark");
            localStorage.theme = html.classList.contains("dark") ? "dark" : "light";
        });

        const increaseText = document.getElementById("increaseText");
        const decreaseText = document.getElementById("decreaseText");
        const resetText = document.getElementById("resetText");

        let fontSize = parseInt(localStorage.getItem("fontSize")) || 100;
        document.documentElement.style.fontSize = `${fontSize}%`;

        function updateFontSize() {
            document.documentElement.style.fontSize = `${fontSize}%`;
            localStorage.setItem("fontSize", fontSize);
            resetText.style.display = fontSize !== 100 ? "inline-flex" : "none";
        }

        increaseText.addEventListener("click", () => {
            fontSize = Math.min(150, fontSize + 10);
            updateFontSize();
        });

        decreaseText.addEventListener("click", () => {
            fontSize = Math.max(80, fontSize - 10);
            updateFontSize();
        });

        resetText.addEventListener("click", () => {
            fontSize = 100;
            updateFontSize();
        });

        resetText.style.display = fontSize !== 100 ? "inline-flex" : "none";
    </script>
    <script> // Dropdown de notificações
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

</body>

</html>