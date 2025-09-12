<?php
function calcularSaldo($pdo, $usuario_id) {
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as total_receitas,
            SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as total_despesas
        FROM transacoes 
        WHERE id_usuario = ?
    ");
    $stmt->execute([$usuario_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($result['total_receitas'] ?? 0) - ($result['total_despesas'] ?? 0);
}

function verificarLimiteGastos($pdo, $usuario_id, $mes_ano) {
    $stmt = $pdo->prepare("
        SELECT 
            m.id_categoria, 
            m.valor_limite, 
            COALESCE(SUM(t.valor), 0) as total_gasto
        FROM metas m
        LEFT JOIN transacoes t ON t.id_categoria = m.id_categoria 
                             AND t.tipo = 'despesa' 
                             AND DATE_FORMAT(t.data_transacao, '%Y-%m') = ?
        WHERE m.usuario_id = ? 
        GROUP BY m.id_categoria, m.valor_limite
        HAVING total_gasto > m.valor_limite
    ");
    $stmt->execute([$mes_ano, $usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
#manobira

function sanitizeMoney($valor) {
    $valor = str_replace(['R$', 'r$', '.', ','], ['', '', '', '.'], $valor);
    return (float) $valor;
}

function formatMoney($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function redirect($url) {
    header("Location: $url");
    exit;
}