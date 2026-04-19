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
    
    // Insere o pedido com valor 0 inicialmente
    $stmt = $pdo->prepare("INSERT INTO pedidos (nome_cliente, telefone_cliente, valor_total, status) VALUES (?, ?, 0.00, 'Pendente')");
    $stmt->execute([$nome, $telefone]);
    $pedido_id = $pdo->lastInsertId();

    $valor_total_real = 0;
    $itens_atualizados = [];

    if (isset($data['itens']) && is_array($data['itens'])) {
        $stmtItem = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmtEstoque = $pdo->prepare("UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id = ?");
        $stmtPreco = $pdo->prepare("SELECT preco_unitario, nome FROM produtos WHERE id = ?");

        foreach ($data['itens'] as $item) {
            if (!isset($item['db_id'])) continue;
            $db_id = $item['db_id']; 
            $qtd = (int)$item['qty'];
            
            // Busca o preço REAL do banco de dados na hora de salvar, IGNORANDO O CLIENT SIDE (anti-fraude)
            $stmtPreco->execute([$db_id]);
            $produto = $stmtPreco->fetch(PDO::FETCH_ASSOC);
            
            if ($produto) {
                $preco_real = (float)$produto['preco_unitario'];
                $valor_total_real += ($preco_real * $qtd);
                
                // Inserir item com valor correto
                $stmtItem->execute([$pedido_id, $db_id, $qtd, $preco_real]);
                
                // Subtrair do estoque
                $stmtEstoque->execute([$qtd, $db_id]);
                
                $item['preco'] = $preco_real;
                $item['nome'] = $produto['nome'];
                $itens_atualizados[] = $item;
            }
        }
        
        // Atualiza o valor_total do pedido com a soma real calculada pelo banco
        $stmtUpdateTotal = $pdo->prepare("UPDATE pedidos SET valor_total = ? WHERE id = ?");
        $stmtUpdateTotal->execute([$valor_total_real, $pedido_id]);
    }

    $pdo->commit();
    echo json_encode([
        'sucesso' => true, 
        'pedido_id' => $pedido_id,
        'valor_total_real' => $valor_total_real,
        'itens_atualizados' => $itens_atualizados
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
