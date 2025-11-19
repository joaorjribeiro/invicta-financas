<?php
// $activePage deve ser definido antes do include
?>

<aside class="bg-white dark:bg-gray-800 w-64 min-h-screen shadow-lg flex flex-col transition-colors duration-300">
    <div class="p-6 text-center border-b dark:border-gray-700">
        <h1 class="text-2xl font-bold text-crimson-500">Invicta</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Finanças</p>
    </div>

    <nav class="flex-1 p-4 space-y-2">
        <?php
        $links = [
            'dashboard' => ['url' => 'dashboard.php', 'icon' => 'home', 'label' => 'Dashboard'],
            'metas' => ['url' => 'metas.php', 'icon' => 'target', 'label' => 'Metas'],
            'transacoes' => ['url' => 'transacoes.php', 'icon' => 'credit-card', 'label' => 'Transações'],
            'relatorios' => ['url' => 'relatorios.php', 'icon' => 'bar-chart-2', 'label' => 'Relatórios'],
            'configuracoes' => ['url' => 'configuracoes.php', 'icon' => 'settings', 'label' => 'Configurações'],
        ];

        foreach ($links as $key => $link) {
            $isActive = ($activePage === $key);
            $classes = $isActive
                ? 'bg-gray-200 dark:bg-gray-700 text-crimson-500 font-semibold'
                : 'hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300';

            echo "
            <a href='{$link['url']}' class='flex items-center gap-3 p-2 rounded transition {$classes}'>
                <i data-feather='{$link['icon']}'></i>
                <span>{$link['label']}</span>
            </a>";
        }
        ?>
    </nav>

    <div class="p-4 border-t dark:border-gray-700">
        <a href='../includes/logout.php'
            class='w-full block text-center bg-crimson-500 text-white py-2 rounded hover:bg-crimson-600 transition'>
            Sair
        </a>
    </div>
</aside>