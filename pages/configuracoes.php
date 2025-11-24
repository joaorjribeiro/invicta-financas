<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_SESSION['usuario_id'];

// === MENSAGENS DE SUCESSO ===
$msgSucesso = $msgErro = $msgSenhaErro = '';
if (isset($_GET['perfil'])) $msgSucesso = "Perfil atualizado com sucesso!";
if (isset($_GET['senha']))   $msgSucesso = "Senha alterada com sucesso!";
if (isset($_GET['avatar']))  $msgSucesso = "Foto de perfil atualizada!";

// === BUSCAR DADOS DO USUÁRIO ===
$stmt = $pdo->prepare("SELECT nome_completo, email, telefone, avatar FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Valores para os campos (com fallback do POST)
$nome     = $_POST['nome']     ?? $user['nome_completo'] ?? '';
$email    = $_POST['email']    ?? $user['email'] ?? '';
$telefone = $_POST['telefone'] ?? $user['telefone'] ?? '';

// === AVATAR ===
$avatarURL = "../assets/img/avatar_default.png";
if (!empty($user['avatar']) && file_exists(__DIR__ . "/../assets/img/" . $user['avatar'])) {
    $avatarURL = "../assets/img/" . $user['avatar'] . "?v=" . time();
}

// === SALVAR PERFIL ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_perfil'])) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    if (empty($nome) || empty($email)) {
        $msgErro = "Nome e e-mail são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msgErro = "E-mail inválido.";
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET nome_completo = ?, email = ?, telefone = ? WHERE id_usuario = ?");
        $stmt->execute([$nome, $email, $telefone, $id]);
        $_SESSION['nome_completo'] = $nome;
        header("Location: configuracoes.php?perfil=ok");
        exit;
    }
}

// === ALTERAR SENHA ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_senha'])) {
    $atual = $_POST['senha_atual'] ?? '';
    $nova  = $_POST['nova_senha'] ?? '';
    $conf  = $_POST['confirmar_senha'] ?? '';

    $hash = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE id_usuario = ?")->execute([$id]) 
        ? $pdo->query("SELECT senha_hash FROM usuarios WHERE id_usuario = $id")->fetchColumn() : '';

    if (!password_verify($atual, $hash)) {
        $msgSenhaErro = "Senha atual incorreta.";
    } elseif ($nova !== $conf) {
        $msgSenhaErro = "As senhas não coincidem.";
    } elseif (strlen($nova) < 6) {
        $msgSenhaErro = "A nova senha deve ter pelo menos 6 caracteres.";
    } else {
        $novoHash = password_hash($nova, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE usuarios SET senha_hash = ? WHERE id_usuario = ?")->execute([$novoHash, $id]);
        header("Location: configuracoes.php?senha=ok");
        exit;
    }
}

// === UPLOAD DE AVATAR ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_avatar']) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    $file = $_FILES['avatar'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $msgErro = "Apenas JPG ou PNG.";
    } elseif ($file['size'] > 2*1024*1024) {
        $msgErro = "Imagem muito grande (máx 2MB).";
    } else {
        $novoNome = "avatar_$id.$ext";
        $destino = __DIR__ . "/../assets/img/$novoNome";
        if (move_uploaded_file($file['tmp_name'], $destino)) {
            $pdo->prepare("UPDATE usuarios SET avatar = ? WHERE id_usuario = ?")->execute([$novoNome, $id]);
            header("Location: configuracoes.php?avatar=ok");
            exit;
        } else {
            $msgErro = "Erro ao salvar imagem.";
        }
    }
}

// === DELETAR CONTA ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletar_conta'])) {
    if (!empty($user['avatar'])) {
        $caminho = __DIR__ . "/../assets/img/" . $user['avatar'];
        if (file_exists($caminho)) unlink($caminho);
    }
    $pdo->prepare("DELETE FROM transacoes WHERE id_usuario = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?")->execute([$id]);
    session_destroy();
    header("Location: ../index.php?deletada=1");
    exit;
}

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
    <title>Configurações - Invicta Finanças</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { crimson: { 500: '#EF4B2A', 600: '#D94426' } } } } }
    </script>
    <style>
        main { transition: font-size 0.25s ease; }
        #resetText { display: none; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 flex text-gray-900 dark:text-gray-100 transition-colors duration-300">

    <?php $activePage = 'configuracoes'; include __DIR__ . '/../includes/sidebar.php'; ?>

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


        <!-- Continuação do <main> que você já tem -->

<main class="p-6 space-y-6">

    <!-- Mensagem de sucesso (avatar, perfil, senha) -->
    <?php if (!empty($msgSucesso)): ?>
        <div class="bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-300 px-6 py-4 rounded-lg font-medium flex items-center gap-3">
            <i data-feather="check-circle"></i>
            <?= $msgSucesso ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($msgErro)): ?>
        <div class="bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-300 px-6 py-4 rounded-lg font-medium flex items-center gap-3">
            <i data-feather="alert-circle"></i>
            <?= $msgErro ?>
        </div>
    <?php endif; ?>

    <!-- Foto de Perfil (já está funcionando) -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg">
        <h4 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-6">Foto de Perfil</h4>
        <form method="POST" enctype="multipart/form-data" class="flex items-center gap-8">
            <img id="preview" src="<?= $avatarURL ?>" class="w-32 h-32 rounded-full object-cover ring-4 ring-crimson-500 shadow-xl">
            <div>
                <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png" class="hidden">
                <button type="button" onclick="document.getElementById('avatarInput').click()"
                    class="bg-crimson-500 hover:bg-crimson-600 text-white font-medium px-6 py-3 rounded-lg transition shadow-md">
                    Escolher Foto
                </button>
                <button type="submit" name="salvar_avatar"
                    class="ml-3 bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg transition shadow-md">
                    Salvar
                </button>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">JPG/PNG • Máximo 2MB</p>
            </div>
        </form>
    </div>

    <!-- Perfil do Usuário -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg">
        <h4 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-6">Perfil do Usuário</h4>
        <form method="POST" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome completo</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($nome) ?>" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-crimson-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-crimson-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Telefone</label>
                    <input type="tel" name="telefone" value="<?= htmlspecialchars($telefone) ?>" placeholder="(00) 00000-0000"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-crimson-500 focus:border-transparent transition">
                </div>
            </div>
            <button type="submit" name="salvar_perfil"
                class="bg-crimson-500 hover:bg-crimson-600 text-white font-bold px-8 py-3 rounded-lg transition shadow-md">
                Salvar Alterações
            </button>
        </form>
    </div>

    <!-- Alterar Senha -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg">
        <h4 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-6">Alterar Senha</h4>
        <?php if (!empty($msgSenhaErro)): ?>
            <div class="bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-300 px-6 py-4 rounded-lg mb-5 flex items-center gap-3">
                <i data-feather="alert-circle"></i>
                <?= $msgSenhaErro ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Senha Atual</label>
                    <input type="password" name="senha_atual" required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-crimson-500">
                </div>
                <div></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nova Senha</label>
                    <input type="password" name="nova_senha" required minlength="6"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-crimson-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirmar Nova Senha</label>
                    <input type="password" name="confirmar_senha" required minlength="6"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-crimson-500">
                </div>
            </div>
            <button type="submit" name="alterar_senha"
                class="bg-crimson-500 hover:bg-crimson-600 text-white font-bold px-8 py-3 rounded-lg transition shadow-md">
                Atualizar Senha
            </button>
        </form>
    </div>

    <!-- Preferências de Notificação -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg">
        <h4 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-6">Notificações</h4>
        <div class="space-y-5">
            <label class="flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-4">
                    <i data-feather="bell" class="w-5 h-5 text-gray-600"></i>
                    <div>
                        <p class="font-medium">Notificações de novas transações</p>
                        <p class="text-sm text-gray-500">Receba alerta toda vez que houver entrada ou saída</p>
                    </div>
                </div>
                <input type="checkbox" class="w-6 h-6 text-crimson-500 rounded focus:ring-crimson-500" checked>
            </label>
            <label class="flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-4">
                    <i data-feather="mail" class="w-5 h-5 text-gray-600"></i>
                    <div>
                        <p class="font-medium">Resumo semanal por e-mail</p>
                        <p class="text-sm text-gray-500">Receba todo domingo um relatório da semana</p>
                    </div>
                </div>
                <input type="checkbox" class="w-6 h-6 text-crimson-500 rounded focus:ring-crimson-500">
            </label>
            <label class="flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-4">
                    <i data-feather="target" class="w-5 h-5 text-gray-600"></i>
                    <div>
                        <p class="font-medium">Alertas de metas</p>
                        <p class="text-sm text-gray-500">Aviso quando estiver perto de atingir uma meta</p>
                    </div>
                </div>
                <input type="checkbox" class="w-6 h-6 text-crimson-500 rounded focus:ring-crimson-500" checked>
            </label>
        </div>
    </div>

    <!-- Deletar Conta -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg border border-red-200 dark:border-red-900/50">
        <h4 class="text-lg font-bold text-red-600 dark:text-red-400 mb-4">Deletar Conta</h4>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Essa ação é <strong>irreversível</strong>. Todos os seus dados, transações e metas serão permanentemente excluídos.
        </p>
        <form method="POST" onsubmit="return confirm('Tem certeza absoluta? Essa ação não pode ser desfeita!')">
            <button type="submit" name="deletar_conta"
                class="bg-red-600 hover:bg-red-700 text-white font-bold px-8 py-3 rounded-lg transition shadow-md">
                Sim, quero deletar minha conta
            </button>
        </form>
    </div>

</main>
    </div>

    <script>
        feather.replace();

        // Modo escuro
        if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');
        document.getElementById('darkToggle').addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        });

        // Fonte
        let fontSize = parseInt(localStorage.getItem('fontSize')) || 100;
        document.documentElement.style.fontSize = fontSize + '%';
        const update = () => {
            document.documentElement.style.fontSize = fontSize + '%';
            localStorage.setItem('fontSize', fontSize);
            document.getElementById('resetText').style.display = fontSize !== 100 ? 'inline-flex' : 'none';
        };
        document.getElementById('increaseText').onclick = () => { fontSize = Math.min(150, fontSize + 10); update(); };
        document.getElementById('decreaseText').onclick = () => { fontSize = Math.max(80, fontSize - 10); update(); };
        document.getElementById('resetText').onclick = () => { fontSize = 100; update(); };
        update();

        // Preview da foto
        document.getElementById('avatarInput').addEventListener('change', e => {
            if (e.target.files[0]) {
                document.getElementById('preview').src = URL.createObjectURL(e.target.files[0]);
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
</body>
</html>