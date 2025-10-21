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


    <?php
    // ============================================================================
    // Inclui a barra lateral (sidebar) e define qual página está ativa.
    // ============================================================================
    $activePage = 'dashboard'; // ou 'metas', 'transacoes', etc.
    include __DIR__ . '/../includes/sidebar.php';
    ?>

    <!-- Main -->
    <div class="flex-1 flex flex-col">
        <!-- Topbar -->
        <header
            class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center transition-colors duration-300">
            <h2 class="text-xl font-bold">Dashboard</h2>

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

        <!-- Conteúdo -->
        <main class="p-6 flex-1 overflow-y-auto space-y-6">
            <!-- Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Saldo</p>
                        <h3 class="text-2xl font-bold text-crimson-500">R$ 12.345,67</h3>
                    </div>
                    <i data-feather="dollar-sign" class="text-gray-400"></i>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Receitas</p>
                        <h3 class="text-2xl font-bold text-crimson-500">R$ 8.200,00</h3>
                    </div>
                    <i data-feather="arrow-up" class="text-gray-400"></i>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Despesas</p>
                        <h3 class="text-2xl font-bold text-crimson-500">R$ 4.155,00</h3>
                    </div>
                    <i data-feather="arrow-down" class="text-gray-400"></i>
                </div>
            </div>

            <!-- Metas -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-4">Metas Financeiras</h4>
                <div class="space-y-3">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Comprar um carro - R$ 20.000</p>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-crimson-500 h-3 rounded-full" style="width: 45%;"></div>
                        </div>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Viagem - R$ 5.000</p>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-crimson-500 h-3 rounded-full" style="width: 70%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                    <h4 class="font-semibold mb-4">Despesas Mensais</h4>
                    <canvas id="despesasChart" class="w-full h-64"></canvas>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                    <h4 class="font-semibold mb-4">Receitas x Despesas</h4>
                    <canvas id="receitasChart" class="w-full h-64"></canvas>
                </div>
            </div>

            <!-- Transações -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h4 class="font-semibold mb-4">Últimas Transações</h4>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Data</th>
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Descrição</th>
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Valor</th>
                            <th class="py-2 px-4 text-gray-500 dark:text-gray-400 text-sm">Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 px-4">21/10/2025</td>
                            <td class="py-2 px-4">Salário</td>
                            <td class="py-2 px-4 text-crimson-500 font-semibold">R$ 4.000,00</td>
                            <td class="py-2 px-4 text-green-500">Entrada</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 px-4">20/10/2025</td>
                            <td class="py-2 px-4">Supermercado</td>
                            <td class="py-2 px-4 text-crimson-500 font-semibold">R$ 450,00</td>
                            <td class="py-2 px-4 text-red-500">Saída</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 px-4">19/10/2025</td>
                            <td class="py-2 px-4">Conta de luz</td>
                            <td class="py-2 px-4 text-crimson-500 font-semibold">R$ 200,00</td>
                            <td class="py-2 px-4 text-red-500">Saída</td>
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

        // Tema escuro - só ativa se o usuário clicar
        if (localStorage.theme === 'dark') html.classList.add('dark');

        toggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
        });

        // === Acessibilidade: controle global de fonte ===
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

        increaseText.addEventListener('click', () => {
            fontSize = Math.min(150, fontSize + 10);
            updateFontSize();
        });

        decreaseText.addEventListener('click', () => {
            fontSize = Math.max(80, fontSize - 10);
            updateFontSize();
        });

        resetText.addEventListener('click', () => {
            fontSize = 100;
            updateFontSize();
        });

        resetText.style.display = (fontSize !== 100) ? 'inline-flex' : 'none';

        // === Gráficos ===
        const ctx1 = document.getElementById('despesasChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [{ label: 'Despesas', data: [1200, 1500, 1000, 1700, 1400, 1600], backgroundColor: 'rgba(239, 75, 42, 0.7)', borderRadius: 5 }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        const ctx2 = document.getElementById('receitasChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [
                    { label: 'Receitas', data: [2000, 1800, 2200, 2400, 2300, 2500], borderColor: '#2aef4b', backgroundColor: 'rgba(42,239,75,0.1)', tension: 0.4, fill: true },
                    { label: 'Despesas', data: [1200, 1500, 1000, 1700, 1400, 1600], borderColor: '#ef4b2a', backgroundColor: 'rgba(239,75,42,0.1)', tension: 0.4, fill: true }
                ]
            },
            options: { responsive: true }
        });
    </script>
</body>

</html>