<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <title>Invicta Finanças</title>

    <style>
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 90px;
        }
    </style>
</head>

<body class="font-sans bg-[#f9f9f9] text-[#0a0706]">

    <!-- Navbar -->
    <nav class="bg-[#0a0706] px-0 py-2 text-white fixed top-0 left-0 right-0 z-50 shadow-md">
        <div class="container mx-auto flex flex-col md:flex-row items-center justify-between py-4 px-6">
            <a href="#home" class="text-[#ef4b2a] text-xl font-bold mb-2 md:mb-0">Invicta Finanças</a>
            <div class="flex justify-center space-x-8 text-center text-sm md:text-base">
                <a href="#sobre" class="hover:text-[#ef4b2a] transition">Sobre</a>
                <a href="#servicos" class="hover:text-[#ef4b2a] transition">Serviços</a>
                <a href="#time" class="hover:text-[#ef4b2a] transition">Equipe</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header id="home" class="bg-[#ef4b2a] text-white text-center py-32 mt-[72px]">
        <h1 class="text-5xl font-bold mb-4">Bem-vindo à Invicta Finanças</h1>
        <p class="text-lg mb-10 max-w-xl mx-auto">Uma solução simples e prática para gestão financeira pessoal.</p>
        <div class="flex justify-center gap-6">
            <a href="login.php"
                class="border-2 border-white px-6 py-3 rounded-md hover:bg-white hover:text-[#ef4b2a] font-semibold transition">Login</a>
            <a href="cadastrar.php"
                class="border-2 border-white px-6 py-3 rounded-md hover:bg-white hover:text-[#ef4b2a] font-semibold transition">Cadastrar</a>
        </div>
    </header>

    <!-- Sobre -->
    <section id="sobre" class="bg-white py-28">
        <div class="max-w-5xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="text-center md:text-left">
                <h2 class="text-3xl font-bold mb-6">Sobre o Projeto</h2>
                <p class="text-gray-700 mb-6 leading-relaxed">
                    A <strong>Invicta Finanças</strong> é um projeto acadêmico criado por estudantes universitários
                    com o objetivo de desenvolver uma ferramenta intuitiva para controle financeiro pessoal.
                </p>
                <p class="text-gray-700 leading-relaxed">
                    O projeto foi pensado para aplicar na prática conceitos de programação e finanças estudados na
                    graduação.
                </p>
                <div class="flex flex-wrap gap-3 justify-center md:justify-start mt-6">
                    <span class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-4 py-2 rounded-full font-medium">Educação</span>
                    <span class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-4 py-2 rounded-full font-medium">Simplicidade</span>
                    <span
                        class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-4 py-2 rounded-full font-medium">Acessibilidade</span>
                </div>
            </div>

            <div
                class="bg-[#ef4b2a]/10 rounded-2xl p-10 flex flex-col items-center justify-center text-center shadow-md border border-gray-200 hover:shadow-lg transition-transform hover:-translate-y-1 max-w-md">
                <i data-feather="dollar-sign" class="w-16 h-16 mb-4 text-[#ef4b2a]"></i>
                <p class="text-gray-600 text-center">
                    Facilitando o controle das finanças pessoais de forma prática e educativa.
                </p>
            </div>
        </div>
    </section>

    <!-- Serviços -->
    <section id="servicos" class="bg-[#f3f3f3] py-28">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold mb-12">Serviços</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div
                    class="bg-white shadow-md rounded-2xl p-10 border border-gray-200 hover:shadow-xl transition-transform hover:-translate-y-1">
                    <i data-feather="trending-up" class="w-12 h-12 mb-4 text-[#ef4b2a] mx-auto"></i>
                    <h3 class="text-xl font-bold mb-2">Investimentos</h3>
                    <p class="text-gray-600">Monitore e cresça seus investimentos com facilidade.</p>
                </div>
                <div
                    class="bg-white shadow-md rounded-2xl p-10 border border-gray-200 hover:shadow-xl transition-transform hover:-translate-y-1">
                    <i data-feather="credit-card" class="w-12 h-12 mb-4 text-[#ef4b2a] mx-auto"></i>
                    <h3 class="text-xl font-bold mb-2">Controle de Gastos</h3>
                    <p class="text-gray-600">Acompanhe suas despesas e mantenha seu orçamento equilibrado.</p>
                </div>
                <div
                    class="bg-white shadow-md rounded-2xl p-10 border border-gray-200 hover:shadow-xl transition-transform hover:-translate-y-1">
                    <i data-feather="bar-chart-2" class="w-12 h-12 mb-4 text-[#ef4b2a] mx-auto"></i>
                    <h3 class="text-xl font-bold mb-2">Relatórios</h3>
                    <p class="text-gray-600">Visualize gráficos e relatórios detalhados de suas finanças.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Equipe -->
    <section id="time" class="bg-white py-28">
        <div class="max-w-5xl mx-auto px-6">
            <h2 class="text-3xl font-bold mb-12 text-center">Nossa Equipe</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <!-- João Pedro -->
                <div
                    class="bg-gray-50 rounded-2xl p-8 text-center shadow-md border border-gray-200 hover:shadow-xl transition-transform hover:-translate-y-2 hover:scale-105">
                    <img src="../assets/img/joao.png" alt="João Pedro"
                        class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-[#ef4b2a]">
                    <h3 class="text-xl font-bold mb-1">João Pedro</h3>
                    <p class="text-[#ef4b2a] font-medium mb-4">Full Stack</p>
                    <p class="text-gray-600 mb-4">
                        Responsável pelo desenvolvimento front-end, incluindo a implementação de funcionalidades,
                        integração de banco de dados e construção da interface do usuário, garantindo uma experiência
                        consistente e eficiente.
                    </p>
                    <div class="flex justify-center gap-4">
                        <a href="https://www.linkedin.com/in/jo%C3%A3o-pedro-lemos-ribeiro-49b942228/" target="_blank"
                            class="text-gray-500 hover:text-[#ef4b2a]"><i data-feather="linkedin"></i></a>
                        <a href="https://github.com/joaorjribeiro" target="_blank"
                            class="text-gray-500 hover:text-[#ef4b2a]"><i data-feather="github"></i></a>
                    </div>
                    <div class="flex flex-wrap justify-center gap-2 mt-4">
                        <span
                            class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-3 py-1 rounded-full text-sm font-medium">HTML</span>
                        <span
                            class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-3 py-1 rounded-full text-sm font-medium">TailwindCSS</span>
                        <span
                            class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-3 py-1 rounded-full text-sm font-medium">JavaScript</span>
                        <span
                            class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-3 py-1 rounded-full text-sm font-medium">SQL</span>
                        <span
                            class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-3 py-1 rounded-full text-sm font-medium">PHP</span>
                    </div>
                </div>

                <!-- Wallace -->
                <div
                    class="bg-gray-50 rounded-2xl p-8 text-center shadow-md border border-gray-200 hover:shadow-xl transition-transform hover:-translate-y-2 hover:scale-105">
                    <img src="../assets/img/wallace.png" alt="Wallace"
                        class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-[#ef4b2a]">
                    <h3 class="text-xl font-bold mb-1">Wallace</h3>
                    <p class="text-[#ef4b2a] font-medium mb-4">Back-End</p>
                    <p class="text-gray-600 mb-4">
                        Contribuiu com a modelagem do banco de dados e apoiou o desenvolvimento de funcionalidades do
                        back-end, garantindo a estrutura e suporte necessários para o funcionamento do sistema.
                    </p>
                    <div class="flex justify-center gap-4">
                        <a href="https://www.linkedin.com/in/wallace-pereira-bb4713296/" target="_blank"
                            class="text-gray-500 hover:text-[#ef4b2a]"><i data-feather="linkedin"></i></a>
                        <a href="https://github.com/Wallace1238" target="_blank"
                            class="text-gray-500 hover:text-[#ef4b2a]"><i data-feather="github"></i></a>
                    </div>
                    <div class="flex flex-wrap justify-center gap-2 mt-4">
                        <span
                            class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-3 py-1 rounded-full text-sm font-medium">HTML</span>
                        <span
                            class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-3 py-1 rounded-full text-sm font-medium">SQL</span>
                        <span
                            class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-3 py-1 rounded-full text-sm font-medium">API</span>
                        <span
                            class="bg-[#ef4b2a]/10 text-[#ef4b2a] px-3 py-1 rounded-full text-sm font-medium">PHP</span>
                        <!-- Novo badge -->
                    </div>
                </div>

            </div>
        </div>
    </section>




    <!-- Footer -->
    <footer class="bg-[#0a0706] text-gray-300">
        <div class="max-w-6xl mx-auto px-6 py-12 grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
            <div>
                <h2 class="text-[#ef4b2a] font-semibold uppercase mb-4 text-sm">Projeto</h2>
                <ul>
                    <li><a href="#sobre" class="hover:underline">Sobre</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-[#ef4b2a] font-semibold uppercase mb-4 text-sm">Contato</h2>
                <ul>
                    <li><a href="#" class="hover:underline">Envie uma mensagem</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-[#ef4b2a] font-semibold uppercase mb-4 text-sm">Informações</h2>
                <ul>
                    <li><a href="#" class="hover:underline">Política de Privacidade</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-[#ef4b2a] py-4 text-center text-sm text-gray-400">
            © 2025 Invicta Finanças — Projeto acadêmico desenvolvido por João Pedro e Wallace.
        </div>
    </footer>

    <script>feather.replace();</script>
</body>

</html>