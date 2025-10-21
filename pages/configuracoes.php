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
            theme: {
                extend: {
                    colors: {
                        crimson: {
                            500: '#EF4B2A',
                            600: '#D94426',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100 flex">

    <!-- Sidebar -->
    <aside class="bg-white w-64 min-h-screen shadow-lg flex flex-col">
        <div class="p-6 text-center border-b">
            <h1 class="text-2xl font-bold text-crimson-500">Invicta</h1>
            <p class="text-gray-500 text-sm mt-1">Finanças</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-200">
                <i data-feather="home"></i>
                <span>Dashboard</span>
            </a>
            <a href="metas.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-200">
                <i data-feather="target"></i>
                <span>Metas</span>
            </a>
            <a href="transacoes.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-200">
                <i data-feather="credit-card"></i>
                <span>Transações</span>
            </a>
            <a href="relatorios.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-200">
                <i data-feather="bar-chart-2"></i>
                <span>Relatórios</span>
            </a>
            <a href="configuracoes.php" class="flex items-center gap-3 p-2 rounded bg-gray-200 hover:bg-gray-300">
                <i data-feather="settings"></i>
                <span>Configurações</span>
            </a>
        </nav>
        <div class="p-4 border-t">
            <a href="index.php"
                class="w-full block text-center bg-crimson-500 text-white py-2 rounded hover:bg-crimson-600 transition">
                Sair
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Topbar -->
        <header class="bg-white shadow p-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-700">Configurações</h2>
            <div class="flex items-center gap-4">
                <button class="relative">
                    <i data-feather="bell" class="text-gray-600"></i>
                    <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1">5</span>
                </button>
                <div class="flex items-center gap-2">
                    <img src="https://media.licdn.com/dms/image/v2/D4D03AQEVMRj09hWePQ/profile-displayphoto-scale_400_400/B4DZgek3u7GQAs-/0/1752859647185?e=1762387200&v=beta&t=mY4wYrU8Mvwye5MqIVOxHt1GpOn9FPytDtvUqczD-2w"
                        alt="Avatar" class="w-10 h-10 rounded-full">
                    <span class="text-gray-700 font-medium">João</span>
                </div>
            </div>
        </header>

        <!-- Configurações content -->
        <main class="p-6 flex-1 overflow-y-auto space-y-6">

            <!-- Foto de Perfil -->
            <div class="bg-white p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 font-semibold text-lg">Foto de Perfil</h4>
                <div class="flex items-center gap-4">
                    <img id="profilePreview"
                        src="https://media.licdn.com/dms/image/v2/D4D03AQEVMRj09hWePQ/profile-displayphoto-scale_400_400/B4DZgek3u7GQAs-/0/1752859647185?e=1762387200&v=beta&t=mY4wYrU8Mvwye5MqIVOxHt1GpOn9FPytDtvUqczD-2w"
                        alt="Avatar" class="w-20 h-20 rounded-full border border-gray-300">
                    <div>
                        <input id="profileInput" type="file" accept="image/*" class="hidden">
                        <button onclick="document.getElementById('profileInput').click()"
                            class="bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition">Alterar
                            Foto</button>
                        <p class="text-gray-500 text-sm mt-1">Formatos aceitos: JPG, PNG. Máx: 2MB</p>
                    </div>
                </div>
            </div>

            <!-- Perfil -->
            <div class="bg-white p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 font-semibold text-lg">Perfil do Usuário</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Nome</label>
                        <input type="text" class="w-full border border-gray-300 rounded px-3 py-2" value="João">
                    </div>
                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Email</label>
                        <input type="email" class="w-full border border-gray-300 rounded px-3 py-2"
                            value="joao@email.com">
                    </div>
                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Telefone</label>
                        <input type="tel" class="w-full border border-gray-300 rounded px-3 py-2"
                            value="(11) 99999-9999">
                    </div>
                </div>
                <button class="bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition mt-2">
                    Salvar Perfil
                </button>
            </div>

            <!-- Notificações -->
            <div class="bg-white p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 font-semibold text-lg">Preferências de Notificação</h4>
                <div class="flex flex-col space-y-3">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="rounded text-crimson-500 focus:ring-crimson-500" checked>
                        Notificações de Transações
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="rounded text-crimson-500 focus:ring-crimson-500">
                        Resumo Semanal
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="rounded text-crimson-500 focus:ring-crimson-500" checked>
                        Alertas de Metas
                    </label>
                </div>
            </div>

            <!-- Alterar senha -->
            <div class="bg-white p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 font-semibold text-lg">Alterar Senha</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Senha Atual</label>
                        <input type="password" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Nova Senha</label>
                        <input type="password" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Confirmar Nova Senha</label>
                        <input type="password" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                </div>
                <button class="bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition mt-2">
                    Atualizar Senha
                </button>
            </div>

            <!-- Deletar conta -->
            <div class="bg-white p-6 rounded-lg shadow space-y-4">
                <h4 class="text-gray-700 font-semibold text-lg">Deletar Conta</h4>
                <p class="text-gray-500 text-sm">Essa ação é irreversível. Todos os seus dados serão apagados.</p>
                <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
                    Deletar Conta
                </button>
            </div>

        </main>
    </div>

    <script>
        feather.replace();

        // Preview da foto de perfil
        const profileInput = document.getElementById('profileInput');
        const profilePreview = document.getElementById('profilePreview');

        profileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    profilePreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

</body>

</html>