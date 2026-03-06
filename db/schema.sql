-- ============================================================================
-- schema.sql - Estrutura do Banco de Dados MySQL (HostGator)
-- ============================================================================
-- Execute este script no phpMyAdmin ou via terminal MySQL para criar as
-- tabelas necessárias para o sistema Dona Sogra.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS do_oliveira_salgados;
USE do_oliveira_salgados;

-- ----------------------------------------------------------------------------
-- TABELA: usuarios (Usuários do painel administrativo)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(64) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserindo um usuário de teste (admin / admin)
-- A senha 'admin' foi hasheada com BCRYPT padrão do PHP password_hash()
INSERT IGNORE INTO usuarios (usuario, senha_hash, nivel_acesso) 
VALUES ('admin', '$2y$10$wE9s0g/8Qo.S6oT.ZgS6V.I3T.V9Hw.9wV6rV7fQ7l4V.V.V.V.V.', 'admin');


-- ----------------------------------------------------------------------------
-- TABELA: produtos (Produtos e controle de estoque)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    estoque_atual INT NOT NULL DEFAULT 0,
    imagem VARCHAR(200),
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);

-- Inserindo produtos base
INSERT IGNORE INTO produtos (slug, nome, descricao, preco_unitario, estoque_atual, imagem) VALUES
('coxinha-de-frango', 'Coxinha de Frango', 'Massa crocante, recheio de frango desfiado temperado.', 0.70, 500, 'img/coxinha.png'),
('coxinha-de-carne', 'Coxinha de Carne', 'Coxinha frita com recheio de carne moída temperada.', 0.85, 500, 'img/coxinha_de_carne.png'),
('kibe', 'Kibe', 'Kibe tradicional, crocante por fora e suculento por dentro.', 0.70, 500, 'img/kibe.png'),
('kibe-com-queijo', 'Kibolinha', 'Kibe com queijo, crocante por fora com queijo derretido por dentro.', 0.85, 500, 'img/kibolinha.png'),
('fataya', 'Fataya', 'Massa com recheio cremoso de carne moída temperada.', 1.10, 500, 'img/fataya.png'),
('croquete-de-salsicha', 'Croquete de Salsicha', 'Crocante por fora com recheio cremoso de salsicha por dentro.', 0.70, 500, 'img/croquete_de_salsicha.png'),
('bolinha-de-queijo', 'Bolinha de Queijo', 'Bolinhas crocantes com mozzarella derretida por dentro.', 0.80, 500, 'img/bolinha_queijo.png'),
('bolinho-de-bacalhau', 'Bolinho de Bacalhau', 'Crocante por fora com recheio cremoso de bacalhau por dentro.', 1.00, 500, 'img/bolinho_de_bacalhau.png'),
('almofadinha-calabresa-queijo', 'Almofadinha de Calabresa e Queijo', 'Crocante por fora com recheio cremoso de calabresa e queijo por dentro.', 0.80, 500, 'img/almofadinha_calabresa_e_queijo.png');


-- ----------------------------------------------------------------------------
-- TABELA: pedidos (Pedidos dos clientes)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cliente VARCHAR(150) NOT NULL,
    telefone_cliente VARCHAR(30),
    valor_total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    status ENUM('Pendente', 'Em preparo', 'Pronto', 'Entregue', 'Cancelado') NOT NULL DEFAULT 'Pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cancelado_por_id INT NULL,
    cancelado_em TIMESTAMP NULL,
    FOREIGN KEY (cancelado_por_id) REFERENCES usuarios(id) ON DELETE SET NULL
);


-- ----------------------------------------------------------------------------
-- TABELA: itens_pedido (Itens de cada pedido)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT
);


-- ----------------------------------------------------------------------------
-- TABELA: historico_precos (Histórico de alterações de preço)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS historico_precos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    preco_anterior DECIMAL(10, 2) NOT NULL,
    preco_novo DECIMAL(10, 2) NOT NULL,
    alterado_por_id INT NOT NULL,
    alterado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (alterado_por_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
