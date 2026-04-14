<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db/db.php';

// Recebe dados JSON do frontend
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados inválidos']);
    exit;
}

try {
    $pdo = get_connection();
    $pdo->beginTransaction();

    $nome = $data['nome'] ?? 'Cliente (Não Informado)';
    $telefone = $data['telefone'] ?? '';
    $valor_total = isset($data['valor_total']) ? (float)$data['valor_total'] : 0.00;
    
    // Insere o pedido
    $stmt = $pdo->prepare("INSERT INTO pedidos (nome_cliente, telefone_cliente, valor_total, status) VALUES (?, ?, ?, 'Pendente')");
    $stmt->execute([$nome, $telefone, $valor_total]);
    $pedido_id = $pdo->lastInsertId();

    if (isset($data['itens']) && is_array($data['itens'])) {
        $stmtItem = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmtEstoque = $pdo->prepare("UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id = ?");

        foreach ($data['itens'] as $item) {
            $db_id = $item['db_id']; // Enviado pelo JS
            $qtd = (int)$item['qty'];
            $preco = (float)$item['preco'];
            
            // Inserir item
            $stmtItem->execute([$pedido_id, $db_id, $qtd, $preco]);
            
            // Subtrair do estoque
            $stmtEstoque->execute([$qtd, $db_id]);
        }
    }

    $pdo->commit();
    echo json_encode(['sucesso' => true, 'pedido_id' => $pedido_id]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
