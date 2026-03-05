<?php
// ============================================================================
// admin/api/alterar_preco.php
// ============================================================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método não permitido.");
}

$product_id = $_POST['product_id'] ?? null;
$novo_preco = $_POST['novo_preco'] ?? null;
$confirmado = $_POST['confirmado'] ?? '';

if (!$product_id || !$novo_preco || $novo_preco <= 0 || $confirmado !== 'sim') {
    $_SESSION['flash'] = "Dados inválidos ou alteração não confirmada.";
    header("Location: ../produtos.php");
    exit();
}

$pdo = get_connection();

try {
    // TRANSAÇÃO ATÔMICA: Garante que o LOG e o UPDATE são salvos juntos
    $pdo->beginTransaction();

    // Pega o preço atual
    $stmt = $pdo->prepare("SELECT nome, preco_unitario FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $produto = $stmt->fetch();

    if (!$produto) {
        throw new Exception("Produto não encontrado.");
    }

    $preco_anterior = $produto['preco_unitario'];

    // 1. Loga a mudança na tabela price_logs
    $stmtLog = $pdo->prepare("INSERT INTO price_logs (product_id, preco_anterior, preco_novo, changed_by_id) VALUES (?, ?, ?, ?)");
    $stmtLog->execute([$product_id, $preco_anterior, $novo_preco, $_SESSION['user_id']]);

    // 2. Atualiza o preço na tabela products
    $stmtUpdate = $pdo->prepare("UPDATE products SET preco_unitario = ? WHERE id = ?");
    $stmtUpdate->execute([$novo_preco, $product_id]);

    $pdo->commit();
    $_SESSION['flash'] = "✅ Preço de '{$produto['nome']}' alterado de R$ " . number_format($preco_anterior, 2, ',', '.') . " para R$ " . number_format($novo_preco, 2, ',', '.') . ".";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = "❌ Erro ao alterar preço: " . $e->getMessage();
}

header("Location: ../produtos.php");
exit();
?>