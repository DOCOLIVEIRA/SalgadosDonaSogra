CREATE TABLE IF NOT EXISTS configuracoes (
    chave VARCHAR(50) PRIMARY KEY,
    valor VARCHAR(255) NOT NULL,
    descricao TEXT NULL
);

INSERT IGNORE INTO configuracoes (chave, valor, descricao) VALUES 
('min_qty', '50', 'Quantidade Mínima de salgados no pedido'),
('step_qty_index', '50', 'Intervalo dos botões +/- na página inicial'),
('step_qty_cart', '5', 'Intervalo dos botões +/- no carrinho (para remover ou adicionar)');
