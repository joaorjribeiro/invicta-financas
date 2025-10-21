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
                    <img src="https://media.licdn.com/dms/image/v2/D4D03AQEVMRj09hWePQ/profile-displayphoto-scale_400_400/B4DZgek3u7GQAs-/0/1752859647185?e=1762387200&v=beta&t=mY4wYrU8Mvwye5MqIVOxHt1GpOn9FPytDtvUqczD-2w"
                        alt="Avatar" class="w-10 h-10 rounded-full">
                    <span class="font-medium">João</span>
                </div>
            </div>
        </header>

        <!-- Conteúdo -->
        <main class="p-6 flex-1 overflow-y-auto space-y-6">
            <!-- Foto de Perfil -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 dark:text-gray-200 font-semibold text-lg">Foto de Perfil</h4>
                <div class="flex items-center gap-4">
                    <img id="profilePreview"
                        src="https://media.licdn.com/dms/image/v2/D4D03AQEVMRj09hWePQ/profile-displayphoto-scale_400_400/B4DZgek3u7GQAs-/0/1752859647185?e=1762387200&v=beta&t=mY4wYrU8Mvwye5MqIVOxHt1GpOn9FPytDtvUqczD-2w"
                        alt="Avatar" class="w-20 h-20 rounded-full border border-gray-300 dark:border-gray-600">
                    <div>
                        <input id="profileInput" type="file" accept="image/*" class="hidden">
                        <button onclick="document.getElementById('profileInput').click()"
                            class="bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition">Alterar
                            Foto</button>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Formatos aceitos: JPG, PNG. Máx: 2MB
                        </p>
                    </div>
                </div>
            </div>

            <!-- Perfil -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 dark:text-gray-200 font-semibold text-lg">Perfil do Usuário</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Nome</label>
                        <input type="text"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700"
                            value="João">
                    </div>
                    <div>
                        <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Email</label>
                        <input type="email"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700"
                            value="joao@email.com">
                    </div>
                    <div>
                        <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Telefone</label>
                        <input type="tel"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700"
                            value="(11) 99999-9999">
                    </div>
                </div>
                <button class="bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition mt-2">Salvar
                    Perfil</button>
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

            <!-- Alterar senha -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 dark:text-gray-200 font-semibold text-lg">Alterar Senha</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Senha Atual</label>
                        <input type="password"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Nova Senha</label>
                        <input type="password"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-gray-500 dark:text-gray-400 text-sm mb-1">Confirmar Nova Senha</label>
                        <input type="password"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700">
                    </div>
                </div>
                <button
                    class="bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition mt-2">Atualizar
                    Senha</button>
            </div>

            <!-- Deletar conta -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 dark:text-gray-200 font-semibold text-lg">Deletar Conta</h4>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Essa ação é irreversível. Todos os seus dados serão
                    apagados.</p>
                <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Deletar
                    Conta</button>
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