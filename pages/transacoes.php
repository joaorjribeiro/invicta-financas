<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transações - Invicta Finanças</title>
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
            <a href="transacoes.php" class="flex items-center gap-3 p-2 rounded bg-gray-200 hover:bg-gray-300">
                <i data-feather="credit-card"></i>
                <span>Transações</span>
            </a>
            <a href="relatorios.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-200">
                <i data-feather="bar-chart-2"></i>
                <span>Relatórios</span>
            </a>
            <a href="configuracoes.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-200">
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
            <h2 class="text-xl font-bold text-gray-700">Transações</h2>
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

        <!-- Transações content -->
        <main class="p-6 flex-1 overflow-y-auto space-y-6">
            <!-- Filtro e botão adicionar -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="date" class="border border-gray-300 rounded px-3 py-2">
                    <select class="border border-gray-300 rounded px-3 py-2">
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
            <div class="bg-white p-4 rounded-lg shadow overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="py-2 px-4 text-gray-500 text-sm">Data</th>
                            <th class="py-2 px-4 text-gray-500 text-sm">Descrição</th>
                            <th class="py-2 px-4 text-gray-500 text-sm">Categoria</th>
                            <th class="py-2 px-4 text-gray-500 text-sm">Valor</th>
                            <th class="py-2 px-4 text-gray-500 text-sm">Tipo</th>
                            <th class="py-2 px-4 text-gray-500 text-sm">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100">
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
                        <tr class="border-b border-gray-100">
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
                        <tr class="border-b border-gray-100">
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

    <script>
        feather.replace();
    </script>

</body>

</html>