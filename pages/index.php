<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <title>Invicta Finanças</title>
</head>

<body class="flex flex-col min-h-screen font-sans bg-white text-[#0a0706]">
    <!-- Navbar -->
    <nav class="bg-[#0a0706] text-white py-2">
        <div class="container mx-auto flex flex-col md:flex-row items-center justify-between py-4 px-6">
            <a href="#" class="text-[#ef4b2a] text-xl font-bold mb-2 md:mb-0">Invicta Finanças</a>

            <!-- Links centralizados -->
            <div class="flex justify-center space-x-8 text-center">
                <a href="#" class="hover:text-[#ef4b2a] transition">Sobre</a>
                <a href="#" class="hover:text-[#ef4b2a] transition">Serviços</a>
                <a href="#" class="hover:text-[#ef4b2a] transition">Contato</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="bg-[#ef4b2a] text-white text-center py-20">
        <h1 class="text-5xl font-bold mb-3">Bem-vindo à Invicta Finanças</h1>
        <p class="text-lg mb-8">Sua solução completa para gestão financeira pessoal.</p>
        <div class="flex justify-center gap-6">
            <a href="#"
                class="border-2 border-white px-6 py-3 rounded-md hover:bg-white hover:text-[#ef4b2a] font-semibold transition">
                Login
            </a>
            <a href="#"
                class="border-2 border-white px-6 py-3 rounded-md hover:bg-white hover:text-[#ef4b2a] font-semibold transition">
                Cadastrar
            </a>
        </div>
    </header>

    <!-- Cards -->
    <section class="bg-white py-16">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-3 gap-10 px-6">
            <!-- Card 1 -->
            <div
                class="bg-white shadow-md rounded-2xl p-8 flex flex-col items-center justify-center text-center border border-gray-200 hover:shadow-xl transition-transform hover:-translate-y-1">
                <i data-feather="trending-up" class="w-12 h-12 mb-4 text-[#ef4b2a]"></i>
                <h3 class="text-xl font-bold mb-2 text-[#0a0706]">Investimentos</h3>
                <p class="text-gray-600">Monitore e cresça seus investimentos com facilidade.</p>
            </div>

            <!-- Card 2 -->
            <div
                class="bg-white shadow-md rounded-2xl p-8 flex flex-col items-center justify-center text-center border border-gray-200 hover:shadow-xl transition-transform hover:-translate-y-1">
                <i data-feather="credit-card" class="w-12 h-12 mb-4 text-[#ef4b2a]"></i>
                <h3 class="text-xl font-bold mb-2 text-[#0a0706]">Controle de Gastos</h3>
                <p class="text-gray-600">Acompanhe suas despesas e mantenha seu orçamento equilibrado.</p>
            </div>

            <!-- Card 3 -->
            <div
                class="bg-white shadow-md rounded-2xl p-8 flex flex-col items-center justify-center text-center border border-gray-200 hover:shadow-xl transition-transform hover:-translate-y-1">
                <i data-feather="bar-chart-2" class="w-12 h-12 mb-4 text-[#ef4b2a]"></i>
                <h3 class="text-xl font-bold mb-2 text-[#0a0706]">Relatórios</h3>
                <p class="text-gray-600">Visualize gráficos e relatórios detalhados de suas finanças.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#0a0706] text-gray-300 mt-auto">
        <div class="container mx-auto px-6 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
            <div>
                <h2 class="text-[#ef4b2a] font-semibold uppercase mb-4 text-sm">Empresa</h2>
                <ul>
                    <li><a href="#" class="hover:underline">Sobre</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-[#ef4b2a] font-semibold uppercase mb-4 text-sm">Central de Ajuda</h2>
                <ul>
                    <li><a href="#" class="hover:underline">Contato</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-[#ef4b2a] font-semibold uppercase mb-4 text-sm">Legal</h2>
                <ul>
                    <li><a href="#" class="hover:underline">Política de Privacidade</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-[#ef4b2a] py-4 text-center text-sm text-gray-400">
            © 2025 Invicta Finanças. Todos os direitos reservados.
        </div>
    </footer>

    <script>
        feather.replace();
    </script>
</body>

</html>