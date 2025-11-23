<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

$activePage = 'metas';
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

// === Inserir nova meta ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_meta'], $_POST['valor_meta'])) {
    $nome_meta = trim($_POST['nome_meta']);
    $valor_meta = floatval($_POST['valor_meta']);

    if ($nome_meta !== '' && $valor_meta > 0) {
        $stmtInsert = $pdo->prepare("
            INSERT INTO metas_financeiras (id_usuario, nome_meta, valor_meta, valor_atual, data_criacao, status)
            VALUES (?, ?, ?, 0.00, CURDATE(), 'Em Progresso')
        ");
        $stmtInsert->execute([$id_usuario, $nome_meta, $valor_meta]);
        header("Location: metas.php");
        exit;
    }
}

// === Editar Meta ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_valor'], $_POST['id_meta_editar'])) {
    $id_meta_editar = intval($_POST['id_meta_editar']);
    $novo_valor = floatval($_POST['editar_valor']);

    $stmtMeta = $pdo->prepare("SELECT valor_atual, valor_meta FROM metas_financeiras WHERE id_meta=? AND id_usuario=?");
    $stmtMeta->execute([$id_meta_editar, $id_usuario]);
    $metaAtual = $stmtMeta->fetch(PDO::FETCH_ASSOC);

    if (!$metaAtual) {
        $erro = "Meta não encontrada.";
    } elseif ($novo_valor < 0) {
        $erro = "Valor inválido.";
    } elseif ($novo_valor + $metaAtual['valor_atual'] > $metaAtual['valor_meta']) {
        $erro = "O valor não pode ultrapassar o valor total da meta.";
    } else {
        $stmtUpdate = $pdo->prepare("
            UPDATE metas_financeiras 
            SET valor_atual = ?
            WHERE id_meta = ? AND id_usuario = ?
        ");
        $stmtUpdate->execute([$novo_valor + $metaAtual['valor_atual'], $id_meta_editar, $id_usuario]);
        header("Location: metas.php");
        exit;
    }
}

// === Excluir Meta ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_meta_id'])) {
    $id_meta = intval($_POST['excluir_meta_id']);
    $stmtDelete = $pdo->prepare("DELETE FROM metas_financeiras WHERE id_meta = ? AND id_usuario = ?");
    $stmtDelete->execute([$id_meta, $id_usuario]);
    header("Location: metas.php");
    exit;
}

// Buscar metas do usuário
$stmtMetas = $pdo->prepare("SELECT * FROM metas_financeiras WHERE id_usuario=? ORDER BY data_criacao DESC");
$stmtMetas->execute([$id_usuario]);
$metas = $stmtMetas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metas - Invicta Finanças</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { crimson: { 500: '#EF4B2A', 600: '#D94426' } } } }
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
                <!-- Perfil -->
                <div class="flex items-center gap-2">
                    <img src="<?= $avatar ?>" alt="Avatar" class="w-10 h-10 rounded-full">
                    <span class="font-medium"><?= htmlspecialchars($user['nome_completo']) ?></span>
                </div>
            </div>
        </header>

        <main class="p-6 flex-1 overflow-y-auto space-y-6">
            <!-- Botão Nova Meta -->
            <button id="openModalNova"
                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition"><i data-feather="plus"
                    class="inline mr-1"></i> Nova Meta</button>

            <!-- Modal Nova Meta -->
            <div id="modalNova"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-lg w-full max-w-md">
                    <h3 class="text-xl font-semibold mb-4">Criar Nova Meta</h3>
                    <form id="novaMetaForm" method="POST">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-200 mb-1">Nome da Meta</label>
                            <input type="text" name="nome_meta" id="nome_meta"
                                class="w-full border rounded p-2 dark:bg-gray-700 dark:text-gray-100" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-200 mb-1">Valor da Meta</label>
                            <input type="number" step="0.01" name="valor_meta" id="valor_meta"
                                class="w-full border rounded p-2 dark:bg-gray-700 dark:text-gray-100" required>
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

            <!-- Modal Editar Meta -->
            <div id="modalEditar"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-lg w-full max-w-md">
                    <h3 class="text-xl font-semibold mb-4">Editar Meta</h3>
                    <form id="editarMetaForm" method="POST">
                        <input type="hidden" name="id_meta_editar" id="id_meta_editar">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-200 mb-1">Adicionar Valor</label>
                            <input type="number" step="0.01" name="editar_valor" id="editar_valor"
                                class="w-full border rounded p-2 dark:bg-gray-700 dark:text-gray-100" required>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" id="closeModalEditar"
                                class="px-4 py-2 rounded border hover:bg-gray-200 dark:hover:bg-gray-700">Cancelar</button>
                            <button type="submit"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Salvar</button>
                        </div>
                        <?php if (!empty($erro))
                            echo "<p class='text-red-500 mt-2'>$erro</p>"; ?>
                    </form>
                </div>
            </div>

            <!-- Lista de Metas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <?php foreach ($metas as $meta):
                    $progresso = ($meta['valor_meta'] > 0) ? min(($meta['valor_atual'] / $meta['valor_meta']) * 100, 100) : 0;
                    ?>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-semibold"><?= htmlspecialchars($meta['nome_meta']) ?></h4>
                            <div class="flex gap-2">
                                <button class="editarMetaBtn text-blue-500 text-sm hover:underline"
                                    data-id="<?= $meta['id_meta'] ?>"
                                    data-valor="<?= $meta['valor_atual'] ?>">Editar</button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="excluir_meta_id" value="<?= $meta['id_meta'] ?>">
                                    <button type="submit" class="text-red-500 hover:underline text-sm">Excluir</button>
                                </form>
                            </div>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-2">
                            Meta: R$ <?= number_format($meta['valor_meta'], 2, ',', '.') ?> | Valor Atual: R$
                            <?= number_format($meta['valor_atual'], 2, ',', '.') ?>
                        </p>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-crimson-500 h-3 rounded-full" style="width: <?= round($progresso) ?>%;"></div>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Progresso: <?= round($progresso) ?>%</p>
                    </div>
                <?php endforeach; ?>
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

        // Font control
        const increaseText = document.getElementById('increaseText');
        const decreaseText = document.getElementById('decreaseText');
        const resetText = document.getElementById('resetText');
        let fontSize = parseInt(localStorage.getItem('fontSize')) || 100;
        document.documentElement.style.fontSize = `${fontSize}%`;
        function updateFontSize() { document.documentElement.style.fontSize = `${fontSize}%`; localStorage.setItem('fontSize', fontSize); resetText.style.display = (fontSize !== 100) ? 'inline-flex' : 'none'; }
        increaseText.addEventListener('click', () => { fontSize = Math.min(150, fontSize + 10); updateFontSize(); });
        decreaseText.addEventListener('click', () => { fontSize = Math.max(80, fontSize - 10); updateFontSize(); });
        resetText.addEventListener('click', () => { fontSize = 100; updateFontSize(); });
        resetText.style.display = (fontSize !== 100) ? 'inline-flex' : 'none';

        // Modal Nova Meta
        const openModalNova = document.getElementById('openModalNova');
        const closeModalNova = document.getElementById('closeModalNova');
        const modalNova = document.getElementById('modalNova');
        openModalNova.addEventListener('click', () => modalNova.classList.remove('hidden'));
        closeModalNova.addEventListener('click', () => modalNova.classList.add('hidden'));

        // Modal Editar Meta
        const editarMetaBtns = document.querySelectorAll('.editarMetaBtn');
        const modalEditar = document.getElementById('modalEditar');
        const closeModalEditar = document.getElementById('closeModalEditar');
        const idMetaEditar = document.getElementById('id_meta_editar');
        const editarValor = document.getElementById('editar_valor');
        editarMetaBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                idMetaEditar.value = btn.dataset.id;
                editarValor.value = btn.dataset.valor;
                modalEditar.classList.remove('hidden');
            });
        });
        closeModalEditar.addEventListener('click', () => modalEditar.classList.add('hidden'));

        // Animar barras de progresso
        document.querySelectorAll('.w-full.bg-gray-200 .bg-crimson-500').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => { bar.style.transition = "width 1s ease-in-out"; bar.style.width = width; }, 100);
        });
    </script>
</body>

</html>