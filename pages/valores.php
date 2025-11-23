<?php
$activePage = 'valores';
include __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/config.php';

// =======================================================
// CRUD - Valores do Usuário
// =======================================================

$idUsuario = $_SESSION['usuario_id'];

// 1. Buscar valores existentes
$sqlValores = $pdo->prepare("
    SELECT * FROM valores_usuarios 
    WHERE id_usuario = ?
");
$sqlValores->execute([$idUsuario]);
$valores = $sqlValores->fetch(PDO::FETCH_ASSOC);

// 2. Se enviar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $saldo_inicial = $_POST['saldo_inicial'] ?? 0;
    $renda_prevista = $_POST['renda_prevista'] ?? 0;
    $limite_gastos = $_POST['limite_gastos'] ?? 0;

    if ($valores) {
        // UPDATE
        $update = $pdo->prepare("
            UPDATE valores_usuarios
            SET saldo_inicial = ?, renda_prevista = ?, limite_gastos = ?
            WHERE id_usuario = ?
        ");
        $update->execute([$saldo_inicial, $renda_prevista, $limite_gastos, $idUsuario]);

    } else {
        // INSERT
        $insert = $pdo->prepare("
            INSERT INTO valores_usuarios 
            (id_usuario, saldo_inicial, renda_prevista, limite_gastos)
            VALUES (?, ?, ?, ?)
        ");
        $insert->execute([$idUsuario, $saldo_inicial, $renda_prevista, $limite_gastos]);
    }

    // Atualizar página com dados novos
    header("Location: valores.php?success=1");
    exit();
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

<body class="bg-gray-100 dark:bg-gray-900 flex text-gray-900 dark:text-gray-100">

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- MAIN -->
    <div class="flex-1 flex flex-col">

        <!-- HEADER IDENTICO AO DA DASHBOARD, SÓ TROQUEI O TÍTULO -->
        <header
            class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center transition-colors duration-300">
            <h2 class="text-xl font-bold">Valores</h2>

            <div class="flex items-center gap-3">

                <!-- Acessibilidade -->
                <div class="flex items-center gap-2">
                    <button id="increaseText" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        <i data-feather="zoom-in" class="text-gray-600 dark:text-gray-300"></i>
                    </button>

                    <button id="decreaseText" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        <i data-feather="zoom-out" class="text-gray-600 dark:text-gray-300"></i>
                    </button>

                    <button id="resetText" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        <i data-feather="refresh-ccw" class="text-gray-600 dark:text-gray-300"></i>
                    </button>
                </div>

                <!-- Dark mode -->
                <button id="darkToggle" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                    <i data-feather="moon" class="text-gray-600 dark:text-gray-300"></i>
                </button>

                <!-- Notificações -->
                <button class="relative">
                    <i data-feather="bell" class="text-gray-600 dark:text-gray-300"></i>
                    <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1">5</span>
                </button>

                <!-- Perfil -->
                <div class="flex items-center gap-2">
                    <img src="<?= $avatar ?>" class="w-10 h-10 rounded-full" />
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

</body>

</html>