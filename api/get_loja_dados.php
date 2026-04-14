<?php
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once __DIR__ . '/../db/db.php';

try {
    $pdo = get_connection();
    
    // Buscar produtos ativos
    $stmt = $pdo->query("SELECT id, slug, nome, descricao, preco_unitario, estoque_atual, imagem FROM produtos WHERE ativo = 1 ORDER BY nome ASC");
    $produtos = $stmt->fetchAll();
    
    // Formatar produtos para o padrão do frontend
    $produtosFormatados = array_map(function($p) {
        return [
            'id' => $p['slug'], // O JS espera 'id' como o slug, para compatibilidade. Podemos retornar o id real tambem.
            'db_id' => $p['id'],
            'nome' => $p['nome'],
            'desc' => $p['descricao'],
            'preco' => (float)$p['preco_unitario'],
            'img' => $p['imagem'] ? $p['imagem'] : 'img/logo.png',
            'estoque' => (int)$p['estoque_atual']
        ];
    }, $produtos);

    // Buscar configurações
    // Tabela configuracoes pode não existir no servidor antigo se não rodou migration, então previmos erro.
    $configuracoes = [];
    try {
        $stmtConf = $pdo->query("SELECT chave, valor FROM configuracoes");
        $confs = $stmtConf->fetchAll();
        foreach($confs as $c) {
            $configuracoes[$c['chave']] = $c['valor'];
        }
    } catch(Exception $e) {
        // Se a tabela não existir, usamos defaults
        $configuracoes = [
            'min_qty' => 50,
            'step_qty_index' => 50,
            'step_qty_cart' => 5
        ];
    }

    echo json_encode([
        'sucesso' => true,
        'produtos' => $produtosFormatados,
        'configuracoes' => $configuracoes
    ]);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
