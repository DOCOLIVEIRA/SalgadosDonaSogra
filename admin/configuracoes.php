<?php
require_once __DIR__ . '/includes/base.php';
$pdo = get_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $min_qty = (int) $_POST['min_qty'];
    $step_qty_index = (int) $_POST['step_qty_index'];
    $step_qty_cart = (int) $_POST['step_qty_cart'];

    $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt->execute(['min_qty', $min_qty, $min_qty]);
    $stmt->execute(['step_qty_index', $step_qty_index, $step_qty_index]);
    $stmt->execute(['step_qty_cart', $step_qty_cart, $step_qty_cart]);

    $_SESSION['flash'] = "Configurações atualizadas com sucesso!";
    header('Location: configuracoes.php');
    exit;
}

// Buscar configs atuais
$min_qty = 50;
$step_qty_index = 50;
$step_qty_cart = 50;

try {
    $stmt = $pdo->query("SELECT chave, valor FROM configuracoes");
    while ($row = $stmt->fetch()) {
        if ($row['chave'] === 'min_qty')
            $min_qty = $row['valor'];
        if ($row['chave'] === 'step_qty_index')
            $step_qty_index = $row['valor'];
        if ($row['chave'] === 'step_qty_cart')
            $step_qty_cart = $row['valor'];
    }
} catch (Exception $e) {
    // Tabela não existe ainda
}

render_admin_header('Configurações', '⚙️ Configurações da Loja');
?>

<div class="card" style="max-width: 600px;">
    <div class="card-header">
        <h2>Regras de Venda e Carrinho</h2>
    </div>

    <form method="POST" action="">
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="font-size: 1.1rem;">Quantidade Mínima (Frito / Congelado)</label>
            <p style="font-size:0.85rem; color:#888; margin-bottom: 0.5rem;">Qual o número mínimo de salgados para
                formar um pedido?</p>
            <input type="number" name="min_qty" value="<?= htmlspecialchars($min_qty) ?>" class="form-input"
                style="font-size: 1.2rem; padding: 0.75rem;" required />
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="font-size: 1.1rem;">Intervalo dos Botões (+ / -) na Página Inicial</label>
            <p style="font-size:0.85rem; color:#888; margin-bottom: 0.5rem;">De quanto em quanto o número aumenta ou
                diminui ao clicar nos botões do Cardápio?</p>
            <input type="number" name="step_qty_index" value="<?= htmlspecialchars($step_qty_index) ?>"
                class="form-input" style="font-size: 1.2rem; padding: 0.75rem;" required />
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label class="form-label" style="font-size: 1.1rem;">Intervalo dos Botões (+ / -) Dentro do Carrinho</label>
            <p style="font-size:0.85rem; color:#888; margin-bottom: 0.5rem;">Dentro do carrinho, de quanto em quanto o
                cliente pode ajustar a quantidade?</p>
            <input type="number" name="step_qty_cart" value="<?= htmlspecialchars($step_qty_cart) ?>" class="form-input"
                style="font-size: 1.2rem; padding: 0.75rem;" required />
        </div>

        <button type="submit" class="btn btn-success" style="width: 100%; font-size: 1.1rem; padding: 1rem;">
            💾 Salvar Configurações
        </button>
    </form>
</div>

<?php render_admin_footer(); ?>