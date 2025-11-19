<?php
session_start();
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/auth.php';

$id = $_SESSION['usuario_id'];

/* ============================================================
   SALVAR PERFIL
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_perfil'])) {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    if (empty($nome) || empty($email)) {
        $msgErro = "Nome e e-mail são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msgErro = "E-mail inválido.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET nome_completo = ?, email = ?, telefone = ? 
            WHERE id_usuario = ?
        ");
        $stmt->execute([$nome, $email, $telefone, $id]);
        header("Location: configuracoes.php?perfil=ok");
        exit;
    }
}

/* ============================================================
   ALTERAR SENHA
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_senha'])) {

    $senhaAtual = $_POST['senha_atual'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    $stmt = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id]);
    $senhaHash = $stmt->fetchColumn();

    if (!$senhaHash || !password_verify($senhaAtual, $senhaHash)) {
        $msgSenhaErro = "Senha atual incorreta.";
    } elseif (empty($novaSenha)) {
        $msgSenhaErro = "A nova senha não pode ser vazia.";
    } elseif ($novaSenha !== $confirmarSenha) {
        $msgSenhaErro = "A confirmação da nova senha não confere.";
    } else {

        $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE usuarios SET senha_hash = ? WHERE id_usuario = ?");
        $stmt->execute([$novoHash, $id]);

        header("Location: configuracoes.php?senha=ok");
        exit;
    }
}

/* ============================================================
   BUSCAR DADOS DO USUÁRIO
============================================================ */
$sql = $pdo->prepare("
    SELECT nome_completo, email, telefone, avatar 
    FROM usuarios 
    WHERE id_usuario = ?
");
$sql->execute([$id]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuário não encontrado.");
}

/* ============================================================
   CONFIGURAÇÃO DOS AVATARES
============================================================ */
$avatarPathBase = __DIR__ . "/../assets/img/";
$avatarURLBase = "../assets/img/";

$avatarDefault = $avatarURLBase . "avatar_default.png";

$avatar = $avatarDefault;

if (!empty($user['avatar']) && file_exists($avatarPathBase . $user['avatar'])) {
    $avatar = $avatarURLBase . $user['avatar'] . "?v=" . time(); // SEM CACHE
}

/* ============================================================
   ALTERAR FOTO DE PERFIL
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_avatar'])) {

    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === 0) {

        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $msgErro = "Apenas arquivos JPG e PNG são permitidos.";
        } else {

            $novoNome = "avatar_" . $id . "." . $ext;
            $destino = $avatarPathBase . $novoNome;

            if (move_uploaded_file($file['tmp_name'], $destino)) {

                $stmt = $pdo->prepare("UPDATE usuarios SET avatar = ? WHERE id_usuario = ?");
                $stmt->execute([$novoNome, $id]);

                // Reload para pegar imagem nova SEM CACHE
                header("Location: configuracoes.php?avatar=ok&v=" . time());
                exit;

            } else {
                $msgErro = "Falha ao salvar a imagem.";
            }
        }
    } else {
        $msgErro = "Nenhum arquivo enviado ou erro no upload.";
    }
}

/* ============================================================
   DELETAR CONTA
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletar_conta'])) {

    // Exclui o avatar, se existir
    if (!empty($user['avatar'])) {
        $avatarPath = $avatarPathBase . $user['avatar'];
        if (file_exists($avatarPath)) {
            unlink($avatarPath);
        }
    }

    // Exclui transações e categorias (se houver foreign key sem ON DELETE CASCADE)
    $pdo->prepare("DELETE FROM transacoes WHERE id_usuario = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM categorias WHERE id_usuario = ?")->execute([$id]);

    // Exclui o usuário
    $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?")->execute([$id]);

    // Destroi sessão
    session_destroy();

    // Redireciona
    header("Location: ../pages/index.php?conta_deletada=1");
    exit;
}


/* ============================================================
   VARIÁVEIS FINAIS PARA EXIBIÇÃO
============================================================ */
$nome = htmlspecialchars($user['nome_completo'] ?? '');
$email = htmlspecialchars($user['email'] ?? '');
$telefone = htmlspecialchars($user['telefone'] ?? '');

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
    $activePage = 'configuracoes';
    include __DIR__ . '/../includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <header
            class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center transition-colors duration-300">
            <h2 class="text-xl font-bold">Configurações</h2>

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
                    <span class="font-medium"><?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </header>

        <!-- Conteúdo -->
        <main class="p-6 flex-1 overflow-y-auto space-y-6">

            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 dark:text-gray-200 font-semibold text-lg">Foto de Perfil</h4>

                <form method="POST" enctype="multipart/form-data">
                    <div class="flex items-center gap-4">
                        <img id="profilePreview" src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>"
                            class="w-20 h-20 rounded-full border border-gray-300 dark:border-gray-600">

                        <div>
                            <input id="profileInput" name="avatar" type="file" accept="image/*" class="hidden">

                            <button type="button" onclick="document.getElementById('profileInput').click()"
                                class="bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition">
                                Alterar Foto
                            </button>

                            <button type="submit" name="salvar_avatar"
                                class="ml-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                                Salvar Foto
                            </button>

                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                                Formatos aceitos: JPG, PNG. Máx: 2MB
                            </p>
                        </div>
                    </div>
                </form>
            </div>


            <!-- Perfil -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 dark:text-gray-200 font-semibold text-lg">Perfil do Usuário</h4>

                <!-- Mensagem de sucesso ou erro -->
                <?php if (!empty($msgSucesso)): ?>
                    <p class="text-green-600"><?= htmlspecialchars($msgSucesso) ?></p>
                <?php elseif (!empty($msgErro)): ?>
                    <p class="text-red-600"><?= htmlspecialchars($msgErro) ?></p>
                <?php endif; ?>

                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Nome</label>
                            <input type="text" name="nome"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700"
                                value="<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div>
                            <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Email</label>
                            <input type="email" name="email"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700"
                                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div>
                            <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Telefone</label>
                            <input type="tel" name="telefone"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700"
                                value="<?= htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <button type="submit" name="salvar_perfil"
                        class="bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition mt-2">
                        Salvar Perfil
                    </button>
                </form>
            </div>

            <!-- Alterar senha -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 dark:text-gray-200 font-semibold text-lg">Alterar Senha</h4>

                <!-- Mensagens de erro ou sucesso -->
                <?php if (!empty($msgSenhaSucesso)): ?>
                    <p class="text-green-600"><?= htmlspecialchars($msgSenhaSucesso) ?></p>
                <?php elseif (!empty($msgSenhaErro)): ?>
                    <p class="text-red-600"><?= htmlspecialchars($msgSenhaErro) ?></p>
                <?php endif; ?>

                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Senha Atual</label>
                            <input type="password" name="senha_atual"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700"
                                required>
                        </div>
                        <div>
                            <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Nova Senha</label>
                            <input type="password" name="nova_senha"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700"
                                required>
                        </div>
                        <div>
                            <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Confirmar Nova
                                Senha</label>
                            <input type="password" name="confirmar_senha"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700"
                                required>
                        </div>
                    </div>

                    <button type="submit" name="alterar_senha"
                        class="bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition mt-2">
                        Atualizar Senha
                    </button>
                </form>
            </div>

            <!-- Notificações -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 dark:text-gray-200 font-semibold text-lg">Preferências de Notificação</h4>
                <div class="flex flex-col space-y-3">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="rounded text-crimson-500 focus:ring-crimson-500" checked>
                        Notificações de Transações
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="rounded text-crimson-500 focus:ring-crimson-500"> Resumo Semanal
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="rounded text-crimson-500 focus:ring-crimson-500" checked> Alertas
                        de Metas
                    </label>
                </div>
            </div>

            <!-- Deletar conta -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 dark:text-gray-200 font-semibold text-lg">Deletar Conta</h4>
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    Essa ação é irreversível. Todos os seus dados serão apagados.
                </p>

                <form method="POST"
                    onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.')">
                    <button type="submit" name="deletar_conta"
                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
                        Deletar Conta
                    </button>
                </form>
            </div>


        </main>
    </div>

    <script>
        feather.replace();

        // Tema escuro
        const html = document.documentElement;
        const toggle = document.getElementById('darkToggle');
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

        // Preview da foto de perfil
        const profileInput = document.getElementById('profileInput');
        const profilePreview = document.getElementById('profilePreview');
        profileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) { profilePreview.src = e.target.result; }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>