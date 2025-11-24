<?php
// includes/sidebar.php
include __DIR__ . '/auth.php';
include __DIR__ . '/config.php';

$id = $_SESSION['usuario_id'] ?? 0;

$avatar = "../assets/img/avatar_default.png";
$nome   = "Usuário";

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT nome_completo, avatar FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $nome = htmlspecialchars($user['nome_completo'] ?? 'Usuário');

        if (!empty($user['avatar']) && file_exists(__DIR__ . "/../assets/img/" . $user['avatar'])) {
            $avatar = "../assets/img/" . $user['avatar'] . "?v=" . time(); // evita cache
        }
    }
}

// Atualiza sessão para nome sempre atual
$_SESSION['nome_completo'] = $nome;
?>

<aside class="bg-white dark:bg-gray-800 w-64 min-h-screen shadow-lg flex flex-col transition-colors duration-300">
    <div class="p-6 text-center border-b dark:border-gray-700">
        <h1 class="text-2xl font-bold text-crimson-500">Invicta</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Finanças</p>
    </div>

    <nav class="flex-1 p-4 space-y-2">
        <?php
        $links = [
            'dashboard'     => ['url' => 'dashboard.php',     'icon' => 'home',          'label' => 'Dashboard'],
            'valores'       => ['url' => 'valores.php',       'icon' => 'dollar-sign',   'label' => 'Valores'],
            'metas'         => ['url' => 'metas.php',         'icon' => 'target',        'label' => 'Metas'],
            'transacoes'    => ['url' => 'transacoes.php',    'icon' => 'credit-card',   'label' => 'Transações'],
            'relatorios'    => ['url' => 'relatorios.php',    'icon' => 'bar-chart-2',   'label' => 'Relatórios'],
            'configuracoes' => ['url' => 'configuracoes.php', 'icon' => 'settings',      'label' => 'Configurações'],
        ];

        foreach ($links as $key => $link) {
            $active = ($activePage ?? '') === $key;
            $class = $active
                ? 'bg-gray-200 dark:bg-gray-700 text-crimson-500 font-semibold'
                : 'hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300';

            echo "<a href='../pages/{$link['url']}' class='flex items-center gap-3 p-3 rounded-lg transition $class'>
                <i data-feather='{$link['icon']}' class='w-5 h-5'></i>
                <span>{$link['label']}</span>
            </a>";
        }
        ?>
    </nav>

    <div class="p-4 border-t dark:border-gray-700">
        <a href="../includes/logout.php" class="block text-center bg-crimson-500 hover:bg-crimson-600 text-white py-3 rounded-lg font-medium transition">
            Sair
        </a>
    </div>
</aside>


<?php
// includes/alerta_gastos.php
// COLOQUE ESTE ARQUIVO NA PASTA includes/

if (!isset($pdo) || !isset($_SESSION['usuario_id'])) return;

$id_usuario = $_SESSION['usuario_id'];

// Pega o limite do usuário
$limite = $pdo->prepare("SELECT limite_gastos FROM valores_usuarios WHERE id_usuario = ? LIMIT 1");
$limite->execute([$id_usuario]);
$limite = $limite->fetchColumn() ?? 0;

if ($limite <= 0) return; // Não tem limite configurado

// Total gasto no mês atual
$mesAtual = date('Y-m');
$gasto = $pdo->prepare("SELECT COALESCE(SUM(valor), 0) FROM transacoes WHERE id_usuario = ? AND tipo = 'Saída' AND DATE_FORMAT(data_transacao, '%Y-%m') = ?");
$gasto->execute([$id_usuario, $mesAtual]);
$gastoMes = $gasto->fetchColumn();

$porcentagem = ($gastoMes / $limite) * 100;

if ($porcentagem < 50) return; // Só mostra a partir de 50%

// Configuração do alerta
if ($porcentagem >= 95) {
    $titulo = "VOCÊ ULTRAPASSOU O LIMITE!";
    $texto  = "Gastou R$ " . number_format($gastoMes,2,',','.') . " de R$ " . number_format($limite,2,',','.');
    $cor    = "bg-red-600 text-white border-red-800";
    $icone  = "alert-triangle";
} elseif ($porcentagem >= 90) {
    $titulo = "QUASE NO LIMITE!";
    $texto  = "Você já usou " . number_format($porcentagem,1) . "% do seu limite mensal.";
    $cor    = "bg-red-500 text-white border-red-700";
    $icone  = "alert-octagon";
} elseif ($porcentagem >= 75) {
    $titulo = "Cuidado com os gastos!";
    $texto  = "Você já gastou " . number_format($porcentagem,1) . "% do limite.";
    $cor    = "bg-orange-500 text-white border-orange-700";
    $icone  = "bell-ring";
} else {
    $titulo = "Metade do limite alcançada";
    $texto  = "Você já usou " . number_format($porcentagem,1) . "% do orçamento mensal.";
    $cor    = "bg-yellow-500 text-gray-900 border-yellow-700";
    $icone  = "bell";
}


?>
<?php
// Atualiza os ícones do Feather dentro do alerta
echo "<script>feather.replace();</script>";
?>



