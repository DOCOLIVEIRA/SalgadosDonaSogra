<?php
// ============================================================================
// admin/api/cancelar_pedido.php - Cancela um pedido e estorna estoque
// ============================================================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método não permitido.");
}

$order_id = $_POST['order_id'] ?? null;

if (!$order_id) {
    $_SESSION['flash'] = "ID do pedido não informado.";
    header("Location: ../index.php");
    exit();
}

$pdo = get_connection();

try {
    // INICIA A TRANSAÇÃO: ou tudo funciona ou nada é salvo
    $pdo->beginTransaction();

    // 1. Verifica estado atual do pedido (evita cancelar duas vezes)
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        throw new Exception("Pedido de ID $order_id não encontrado.");
    }

    if ($pedido['status'] === 'Cancelado') {
        throw new Exception("Este pedido já estava cancelado.");
    }

    // 2. Muda o status para cancelado e registra quem cancelou
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = 'Cancelado', cancelado_por_id = ?, cancelado_em = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $order_id]);

    // 3. Estorna o estoque: primeiro buscamos todos os itens do pedido
    $stmtItems = $pdo->prepare("SELECT product_id, quantidade FROM order_items WHERE order_id = ?");
    $stmtItems->execute([$order_id]);
    $items = $stmtItems->fetchAll();

    // Para cada item, atualizamos a tabela produtos somando a quantidade de volta
    $stmtUpdateEstoque = $pdo->prepare("UPDATE products SET quantidade_estoque = quantidade_estoque + ? WHERE id = ?");

    foreach ($items as $item) {
        $stmtUpdateEstoque->execute([$item['quantidade'], $item['product_id']]);
    }

    // 4. Salva a transação!
    $pdo->commit();
    $_SESSION['flash'] = "✅ Pedido #$order_id cancelado com sucesso. O estoque de " . count($items) . " produto(s) foi restaurado.";

} catch (Exception $e) {
    // Se der erro, desfaz tudo
    $pdo->rollBack();
    $_SESSION['flash'] = "❌ Erro ao cancelar pedido: " . $e->getMessage();
}

header("Location: ../index.php");
exit();
?>