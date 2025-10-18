<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Invicta Finanças</title>
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
    <style>
        .bg-crimson-pattern {
            background-image: radial-gradient(circle, rgba(239, 75, 42, 0.2) 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>
</head>

<body class="bg-crimson-pattern font-sans flex items-center justify-center min-h-screen">

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden w-full max-w-md mx-4">
        <div class="bg-crimson-500 p-6 text-center">
            <h1 class="text-3xl font-bold text-white">Criar Conta</h1>
            <p class="text-white opacity-90 mt-2">Comece sua jornada financeira com a Invicta Finanças</p>
        </div>

        <div class="p-8 space-y-6">
            <form action="#" method="POST" class="space-y-5">
                <!-- Nome -->
                <div class="relative">
                    <i data-feather="user" class="absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" id="nome" name="nome" placeholder="Nome completo"
                        class="w-full pl-10 border-b-2 border-gray-200 py-3 focus:outline-none focus:border-crimson-500 transition">
                </div>

                <!-- Email -->
                <div class="relative">
                    <i data-feather="mail" class="absolute left-3 top-3 text-gray-400"></i>
                    <input type="email" id="email" name="email" placeholder="Email"
                        class="w-full pl-10 border-b-2 border-gray-200 py-3 focus:outline-none focus:border-crimson-500 transition">
                </div>

                <!-- Senha -->
                <div class="relative">
                    <i data-feather="lock" class="absolute left-3 top-3 text-gray-400"></i>
                    <input type="password" id="senha" name="senha" placeholder="Senha"
                        class="w-full pl-10 border-b-2 border-gray-200 py-3 focus:outline-none focus:border-crimson-500 transition">
                </div>

                <!-- Confirmar Senha -->
                <div class="relative">
                    <i data-feather="key" class="absolute left-3 top-3 text-gray-400"></i>
                    <input type="password" id="confirmarSenha" name="confirmarSenha" placeholder="Confirmar Senha"
                        class="w-full pl-10 border-b-2 border-gray-200 py-3 focus:outline-none focus:border-crimson-500 transition">
                </div>

                <!-- Termos -->
                <div class="flex items-center">
                    <input type="checkbox" id="terms" class="rounded text-crimson-500 focus:ring-crimson-500">
                    <label for="terms" class="ml-2 text-sm text-gray-600">
                        Concordo com os <a href="#" class="text-crimson-500 hover:underline">Termos</a> e <a href="#"
                            class="text-crimson-500 hover:underline">Política de Privacidade</a>
                    </label>
                </div>

                <!-- Botão Registrar -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-crimson-500 to-crimson-600 text-white font-semibold py-3 rounded-lg hover:shadow-lg transition transform hover:-translate-y-1">
                    Registrar
                </button>
            </form>

            <!-- Separator -->
            <div class="relative flex items-center justify-center my-4">
                <div class="absolute inset-0 border-t border-gray-200"></div>
                <span class="relative bg-white px-4 text-gray-500 text-sm">OU CONTINUAR COM</span>
            </div>

            <!-- Social Login -->
            <div class="grid grid-cols-2 gap-4">
                <button
                    class="border border-gray-200 rounded-lg py-2 flex items-center justify-center hover:bg-gray-50 transition">
                    <i data-feather="github" class="mr-2"></i> GitHub
                </button>
                <button
                    class="border border-gray-200 rounded-lg py-2 flex items-center justify-center hover:bg-gray-50 transition">
                    <i data-feather="mail" class="mr-2"></i> Google
                </button>
            </div>

            <!-- Link para login -->
            <p class="text-center text-gray-500 text-sm mt-4">
                Já tem uma conta?
                <a href="login.html" class="text-crimson-500 font-medium hover:underline">Entrar</a>
            </p>
        </div>
    </div>

    <script>
        feather.replace();

        // Mudar cor do ícone ao focar
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', () => {
                const featherIcon = input.previousElementSibling;
                if (featherIcon && featherIcon.tagName === 'I') {
                    featherIcon.classList.add('text-crimson-500');
                    featherIcon.classList.remove('text-gray-400');
                }
            });
            input.addEventListener('blur', () => {
                const featherIcon = input.previousElementSibling;
                if (featherIcon && featherIcon.tagName === 'I') {
                    featherIcon.classList.remove('text-crimson-500');
                    featherIcon.classList.add('text-gray-400');
                }
            });
        });
    </script>

</body>

</html>