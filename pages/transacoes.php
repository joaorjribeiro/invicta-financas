<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transações - Invicta Finanças</title>

    <!-- Tailwind + Feather -->
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

    <!-- Sidebar -->
    <aside class="bg-white dark:bg-gray-800 w-64 min-h-screen shadow-lg flex flex-col transition-colors duration-300">
        <div class="p-6 text-center border-b dark:border-gray-700">
            <h1 class="text-2xl font-bold text-crimson-500">Invicta</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Finanças</p>
        </div>

        <nav class="flex-1 p-4 space-y-2">
            <a href="dashboard.php"
                class="flex items-center gap-3 p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                <i data-feather="home"></i><span>Dashboard</span>
            </a>
            <a href="metas.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                <i data-feather="target"></i><span>Metas</span>
            </a>
            <a href="transacoes.php" class="flex items-center gap-3 p-2 rounded bg-gray-200 dark:bg-gray-700">
                <i data-feather="credit-card"></i><span>Transações</span>
            </a>
            <a href="relatorios.php"
                class="flex items-center gap-3 p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                <i data-feather="bar-chart-2"></i><span>Relatórios</span>
            </a>
            <a href="configuracoes.php"
                class="flex items-center gap-3 p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                <i data-feather="settings"></i><span>Configurações</span>
            </a>
        </nav>

        <div class="p-4 border-t dark:border-gray-700">
            <a href="index.php"
                class="w-full block text-center bg-crimson-500 text-white py-2 rounded hover:bg-crimson-600 transition">
                Sair
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Topbar -->
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

        <!-- Transações content -->
        <main class="p-6 flex-1 overflow-y-auto space-y-6">

            <!-- Filtro e botão adicionar -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="date"
                        class="border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-800">
                    <select
                        class="border border-gray-300 dark:border-gray-700 rounded px-3 py-2 bg-white dark:bg-gray-800">
                        <option value="">Tipo</option>
                        <option value="entrada">Entrada</option>
                        <option value="saida">Saída</option>
                    </select>
                    <button
                        class="bg-crimson-500 text-white px-4 py-2 rounded hover:bg-crimson-600 transition">Filtrar</button>
                </div>
                <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                    <i data-feather="plus" class="inline mr-1"></i> Nova Transação
                </button>
            </div>

            <!-- Tabela de Transações -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Data</th>
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Descrição</th>
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Categoria</th>
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Valor</th>
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Tipo</th>
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 px-4">21/10/2025</td>
                            <td class="py-2 px-4">Salário</td>
                            <td class="py-2 px-4">Renda</td>
                            <td class="py-2 px-4 text-crimson-500 font-semibold">R$ 4.000,00</td>
                            <td class="py-2 px-4 text-green-500">Entrada</td>
                            <td class="py-2 px-4 flex gap-2">
                                <button class="text-blue-500 hover:underline">Editar</button>
                                <button class="text-red-500 hover:underline">Excluir</button>
                            </td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 px-4">20/10/2025</td>
                            <td class="py-2 px-4">Supermercado</td>
                            <td class="py-2 px-4">Alimentação</td>
                            <td class="py-2 px-4 text-crimson-500 font-semibold">R$ 450,00</td>
                            <td class="py-2 px-4 text-red-500">Saída</td>
                            <td class="py-2 px-4 flex gap-2">
                                <button class="text-blue-500 hover:underline">Editar</button>
                                <button class="text-red-500 hover:underline">Excluir</button>
                            </td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 px-4">19/10/2025</td>
                            <td class="py-2 px-4">Conta de luz</td>
                            <td class="py-2 px-4">Contas</td>
                            <td class="py-2 px-4 text-crimson-500 font-semibold">R$ 200,00</td>
                            <td class="py-2 px-4 text-red-500">Saída</td>
                            <td class="py-2 px-4 flex gap-2">
                                <button class="text-blue-500 hover:underline">Editar</button>
                                <button class="text-red-500 hover:underline">Excluir</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

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
    </script>

</body>

</html>