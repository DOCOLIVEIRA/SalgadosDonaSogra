<?php
// ============================================================================
// admin/api/alterar_preco.php
// ============================================================================
require_once __DIR__ . '/../../php/includes/auth_check.php';
require_once __DIR__ . '/../../db/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método não permitido.");
}

$produto_id = $_POST['produto_id'] ?? null;
$novo_preco = $_POST['novo_preco'] ?? null;
$confirmado = $_POST['confirmado'] ?? '';

if (!$produto_id || !$novo_preco || $novo_preco <= 0 || $confirmado !== 'sim') {
    $_SESSION['flash'] = "Dados inválidos ou alteração não confirmada.";
    header("Location: ../produtos.php");
    exit();
}

$pdo = get_connection();

try {
    // TRANSAÇÃO ATÔMICA: Garante que o LOG e o UPDATE são salvos juntos
    $pdo->beginTransaction();

    // Pega o preço atual
    $stmt = $pdo->prepare("SELECT nome, preco_unitario FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch();

    if (!$produto) {
        throw new Exception("Produto não encontrado.");
    }

    $preco_anterior = $produto['preco_unitario'];

    // 1. Loga a mudança na tabela historico_precos
    $stmtLog = $pdo->prepare("INSERT INTO historico_precos (produto_id, preco_anterior, preco_novo, alterado_por_id) VALUES (?, ?, ?, ?)");
    $stmtLog->execute([$produto_id, $preco_anterior, $novo_preco, $_SESSION['user_id']]);

    // 2. Atualiza o preço na tabela produtos
    $stmtUpdate = $pdo->prepare("UPDATE produtos SET preco_unitario = ? WHERE id = ?");
    $stmtUpdate->execute([$novo_preco, $produto_id]);

    $pdo->commit();
    $_SESSION['flash'] = "✅ Preço de '{$produto['nome']}' alterado de R$ " . number_format($preco_anterior, 2, ',', '.') . " para R$ " . number_format($novo_preco, 2, ',', '.') . ".";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = "❌ Erro ao alterar preço: " . $e->getMessage();
}

header("Location: ../produtos.php");
exit();
?>