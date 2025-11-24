<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

$activePage = 'transacoes';
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

// === Inserir nova transação ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descricao'], $_POST['valor'], $_POST['tipo'], $_POST['id_categoria'])) {
    $descricao = trim($_POST['descricao']);
    $valor = floatval($_POST['valor']);
    $tipo = $_POST['tipo'];
    $id_categoria = intval($_POST['id_categoria']);

    if ($descricao && $valor > 0 && in_array($tipo, ['Entrada', 'Saída'])) {
        $stmt = $pdo->prepare("INSERT INTO transacoes (id_usuario, id_categoria, data_transacao, descricao, valor, tipo) VALUES (?, ?, CURDATE(), ?, ?, ?)");
        $stmt->execute([$id_usuario, $id_categoria, $descricao, $valor, $tipo]);
        header("Location: transacoes.php");
        exit;
    }
}

// === Editar transação ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_valor'], $_POST['id_transacao_editar'])) {
    $id_transacao = intval($_POST['id_transacao_editar']);
    $novo_valor = floatval($_POST['editar_valor']);

    $stmtUpdate = $pdo->prepare("UPDATE transacoes SET valor=? WHERE id_transacao=? AND id_usuario=?");
    $stmtUpdate->execute([$novo_valor, $id_transacao, $id_usuario]);
    header("Location: transacoes.php");
    exit;
}

// === Excluir transação ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_transacao_id'])) {
    $id_transacao = intval($_POST['excluir_transacao_id']);
    $stmt = $pdo->prepare("DELETE FROM transacoes WHERE id_transacao=? AND id_usuario=?");
    $stmt->execute([$id_transacao, $id_usuario]);
    header("Location: transacoes.php");
    exit;
}

// Buscar transações
$stmt = $pdo->prepare("
    SELECT t.*, c.nome_categoria, c.tipo_categoria
    FROM transacoes t
    LEFT JOIN categorias c ON t.id_categoria=c.id_categoria
    WHERE t.id_usuario=?
    ORDER BY t.data_transacao DESC
");
$stmt->execute([$id_usuario]);
$transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar categorias
$stmtCat = $pdo->prepare("SELECT * FROM categorias WHERE id_usuario=?");
$stmtCat->execute([$id_usuario]);
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Se não tiver categoria, inserir algumas padrões
if (count($categorias) === 0) {
    $defaultCategories = [
        ['nome' => 'Salário', 'tipo' => 'Receita'],
        ['nome' => 'Alimentação', 'tipo' => 'Despesa'],
        ['nome' => 'Transporte', 'tipo' => 'Despesa'],
        ['nome' => 'Lazer', 'tipo' => 'Despesa'],
        ['nome' => 'Contas', 'tipo' => 'Despesa']
    ];
    $stmtInsertCat = $pdo->prepare("INSERT INTO categorias (nome_categoria, tipo_categoria, id_usuario) VALUES (?, ?, ?)");
    foreach ($defaultCategories as $cat) {
        $stmtInsertCat->execute([$cat['nome'], $cat['tipo'], $id_usuario]);
    }
    // Buscar novamente
    $stmtCat->execute([$id_usuario]);
    $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Transações - Invicta Finanças</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { crimson: { 500: '#EF4B2A', 600: '#D94426' } } } }
        };
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 flex text-gray-900 dark:text-gray-100">

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header
            class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center transition-colors duration-300">
            <h2 class="text-xl font-bold">Transações</h2>

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

            <!-- Botão Nova Transação -->
            <button id="openModalNova"
                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition"><i data-feather="plus"
                    class="inline mr-1"></i> Nova Transação</button>

            <!-- Modal Nova Transação -->
            <div id="modalNova"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-lg w-full max-w-md">
                    <h3 class="text-xl font-semibold mb-4">Nova Transação</h3>
                    <form method="POST">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-200 mb-1">Descrição</label>
                            <input type="text" name="descricao"
                                class="w-full border rounded p-2 dark:bg-gray-700 dark:text-gray-100" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-200 mb-1">Valor</label>
                            <input type="number" step="0.01" name="valor"
                                class="w-full border rounded p-2 dark:bg-gray-700 dark:text-gray-100" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-200 mb-1">Tipo</label>
                            <select name="tipo" class="w-full border rounded p-2 dark:bg-gray-700 dark:text-gray-100">
                                <option value="Entrada">Entrada</option>
                                <option value="Saída">Saída</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-200 mb-1">Categoria</label>
                            <select name="id_categoria"
                                class="w-full border rounded p-2 dark:bg-gray-700 dark:text-gray-100">
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>">
                                        <?= htmlspecialchars($cat['nome_categoria']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" id="closeModalNova"
                                class="px-4 py-2 rounded border hover:bg-gray-200 dark:hover:bg-gray-700">Cancelar</button>
                            <button type="submit"
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Editar Transação -->
            <div id="modalEditar"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-lg w-full max-w-md">
                    <h3 class="text-xl font-semibold mb-4">Editar Transação</h3>
                    <form method="POST">
                        <input type="hidden" name="id_transacao_editar" id="id_transacao_editar">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-200 mb-1">Valor</label>
                            <input type="number" step="0.01" name="editar_valor" id="editar_valor"
                                class="w-full border rounded p-2 dark:bg-gray-700 dark:text-gray-100" required>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" id="closeModalEditar"
                                class="px-4 py-2 rounded border hover:bg-gray-200 dark:hover:bg-gray-700">Cancelar</button>
                            <button type="submit"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Transações -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 px-4 text-sm">Data</th>
                            <th class="py-2 px-4 text-sm">Descrição</th>
                            <th class="py-2 px-4 text-sm">Categoria</th>
                            <th class="py-2 px-4 text-sm">Valor</th>
                            <th class="py-2 px-4 text-sm">Tipo</th>
                            <th class="py-2 px-4 text-sm">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transacoes as $t): ?>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="py-2 px-4"><?= date('d/m/Y', strtotime($t['data_transacao'])) ?></td>
                                <td class="py-2 px-4"><?= htmlspecialchars($t['descricao']) ?></td>
                                <td class="py-2 px-4"><?= htmlspecialchars($t['nome_categoria']) ?></td>
                                <td class="py-2 px-4 <?= $t['tipo'] == 'Entrada' ? 'text-green-500' : 'text-red-500' ?>">R$
                                    <?= number_format($t['valor'], 2, ',', '.') ?>
                                </td>
                                <td class="py-2 px-4"><?= $t['tipo'] ?></td>
                                <td class="py-2 px-4 flex gap-2">
                                    <button class="editarTransacaoBtn text-blue-500 text-sm hover:underline"
                                        data-id="<?= $t['id_transacao'] ?>" data-valor="<?= $t['valor'] ?>">Editar</button>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="excluir_transacao_id" value="<?= $t['id_transacao'] ?>">
                                        <button type="submit" class="text-red-500 hover:underline text-sm">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <script>
        feather.replace();

        // Dark Mode
        const html = document.documentElement;
        const toggle = document.getElementById('darkToggle');
        if (localStorage.theme === 'dark') html.classList.add('dark');
        toggle.addEventListener('click', () => { html.classList.toggle('dark'); localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light'; });

        // Font controls
        const increaseText = document.getElementById('increaseText');
        const decreaseText = document.getElementById('decreaseText');
        const resetText = document.getElementById('resetText');
        let fontSize = parseInt(localStorage.getItem('fontSize')) || 100;
        function updateFontSize() { document.documentElement.style.fontSize = `${fontSize}%`; localStorage.setItem('fontSize', fontSize); resetText.style.display = (fontSize !== 100) ? 'inline-flex' : 'none'; }
        increaseText.addEventListener('click', () => { fontSize = Math.min(150, fontSize + 10); updateFontSize(); });
        decreaseText.addEventListener('click', () => { fontSize = Math.max(80, fontSize - 10); updateFontSize(); });
        resetText.addEventListener('click', () => { fontSize = 100; updateFontSize(); });
        updateFontSize();

        // Modal Nova
        const openModalNova = document.getElementById('openModalNova');
        const closeModalNova = document.getElementById('closeModalNova');
        const modalNova = document.getElementById('modalNova');
        openModalNova.addEventListener('click', () => modalNova.classList.remove('hidden'));
        closeModalNova.addEventListener('click', () => modalNova.classList.add('hidden'));

        // Modal Editar
        const editarBtns = document.querySelectorAll('.editarTransacaoBtn');
        const modalEditar = document.getElementById('modalEditar');
        const closeModalEditar = document.getElementById('closeModalEditar');
        const idTransacaoEditar = document.getElementById('id_transacao_editar');
        const editarValor = document.getElementById('editar_valor');
        editarBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                idTransacaoEditar.value = btn.dataset.id;
                editarValor.value = btn.dataset.valor;
                modalEditar.classList.remove('hidden');
            });
        });
        closeModalEditar.addEventListener('click', () => modalEditar.classList.add('hidden'));

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