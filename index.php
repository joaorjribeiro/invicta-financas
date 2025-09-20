<?php
session_start();

// Se o usuário estiver logado, redireciona para o dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invicta - Dashboard de Finanças</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"?v=<?php echo time(); ?>">"
    
</head>
<body class="bg-light">
    <header class="bg-dark text-white text-center p-3">
        <h2><i class="bi bi-wallet2"></i> Invicta - Controle Financeiro Pessoal</h2>
    </header>
    <main>
        <section class="hero-section">
            <div class="container">
                <h1>Bem-vindo ao Invicta</h1>
                <p>Gerencie suas finanças com facilidade: controle receitas, despesas, metas e visualize relatórios interativos.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="usuario/login.php" class="btn btn-light btn-lg"><i class="bi bi-box-arrow-in-right"></i> Entrar</a>
                    <a href="usuario/cadastro_usuario.php" class="btn btn-outline-light btn-lg"><i class="bi bi-person-plus"></i> Cadastrar</a>
                </div>
            </div>
        </section>
        <section class="container my-5">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="bi bi-pie-chart-fill text-primary" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-3">Relatórios Interativos</h5>
                            <p class="card-text">Visualize seu fluxo de caixa com gráficos dinâmicos e exporte relatórios em PDF ou Excel.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="bi bi-bell-fill text-warning" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-3">Alertas Inteligentes</h5>
                            <p class="card-text">Receba notificações sobre gastos acima do previsto ou metas financeiras atingidas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="bi bi-gear-fill text-success" style="font-size: 2rem;"></i>
                            <h5 class="card-title mt-3">Personalização</h5>
                            <p class="card-text">Ajuste o layout e as cores do dashboard conforme suas preferências.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer class="bg-dark text-white text-center py-4">
        <p>&copy; <?= date("Y") ?> Invicta. Todos os direitos reservados.</p>
        <p>Desenvolvido por João Pedro Lemos Ribeiro e Wallace Pereira Cruz</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>