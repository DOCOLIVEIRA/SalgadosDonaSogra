<?php
// ============================================================================
// setup_admin.php – Setup inicial do sistema Dona Sogra
// ⚠️  APAGUE ESTE ARQUIVO APÓS USAR!
// ============================================================================

// ── Configurações do banco ──────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // ← ajuste se necessário
define('DB_PASS', '');       // ← ajuste se necessário
define('DB_NAME', 'do_oliveira_salgados');

// ── Credenciais do usuário admin a criar/redefinir ──────────────────────────
$admin_usuario = 'admin';
$admin_senha   = 'admin123';   // ← TROQUE PELA SENHA QUE QUISER
$admin_nivel   = 'admin';

// ============================================================================
$erros  = [];
$ok     = [];

try {
    // Conecta SEM banco para poder criá-lo se não existir
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 1. Cria banco se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $ok[] = "✅ Banco de dados <strong>" . DB_NAME . "</strong> verificado/criado.";

    // Seleciona o banco
    $pdo->exec("USE `" . DB_NAME . "`");

    // 2. Cria tabela usuarios
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            usuario      VARCHAR(64)  NOT NULL UNIQUE,
            senha_hash   VARCHAR(255) NOT NULL,
            nivel_acesso ENUM('admin','staff') NOT NULL DEFAULT 'staff',
            ativo        BOOLEAN NOT NULL DEFAULT TRUE,
            criado_em    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $ok[] = "✅ Tabela <strong>usuarios</strong> verificada/criada.";

    // 3. Cria tabela produtos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS produtos (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            slug            VARCHAR(100) NOT NULL UNIQUE,
            nome            VARCHAR(150) NOT NULL,
            descricao       TEXT,
            preco_unitario  DECIMAL(10,2) NOT NULL,
            estoque_atual   INT NOT NULL DEFAULT 0,
            imagem          VARCHAR(200),
            ativo           BOOLEAN NOT NULL DEFAULT TRUE
        )
    ");
    $ok[] = "✅ Tabela <strong>produtos</strong> verificada/criada.";

    // 4. Cria tabela pedidos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pedidos (
            id               INT AUTO_INCREMENT PRIMARY KEY,
            nome_cliente     VARCHAR(150) NOT NULL,
            telefone_cliente VARCHAR(30),
            valor_total      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status           ENUM('Pendente','Em preparo','Pronto','Entregue','Cancelado') NOT NULL DEFAULT 'Pendente',
            criado_em        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            cancelado_por_id INT NULL,
            cancelado_em     TIMESTAMP NULL,
            FOREIGN KEY (cancelado_por_id) REFERENCES usuarios(id) ON DELETE SET NULL
        )
    ");
    $ok[] = "✅ Tabela <strong>pedidos</strong> verificada/criada.";

    // 5. Cria tabela itens_pedido
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS itens_pedido (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            pedido_id       INT NOT NULL,
            produto_id      INT NOT NULL,
            quantidade      INT NOT NULL,
            preco_unitario  DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (pedido_id)  REFERENCES pedidos(id)  ON DELETE CASCADE,
            FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT
        )
    ");
    $ok[] = "✅ Tabela <strong>itens_pedido</strong> verificada/criada.";

    // 6. Cria tabela historico_precos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS historico_precos (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            produto_id      INT NOT NULL,
            preco_anterior  DECIMAL(10,2) NOT NULL,
            preco_novo      DECIMAL(10,2) NOT NULL,
            alterado_por_id INT NOT NULL,
            alterado_em     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (produto_id)      REFERENCES produtos(id)  ON DELETE CASCADE,
            FOREIGN KEY (alterado_por_id) REFERENCES usuarios(id)  ON DELETE CASCADE
        )
    ");
    $ok[] = "✅ Tabela <strong>historico_precos</strong> verificada/criada.";

    // 7. Cria/atualiza usuário admin com hash válido
    $hash = password_hash($admin_senha, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (usuario, senha_hash, nivel_acesso, ativo)
        VALUES (?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE senha_hash = VALUES(senha_hash), ativo = 1
    ");
    $stmt->execute([$admin_usuario, $hash, $admin_nivel]);
    $ok[] = "✅ Usuário <strong>$admin_usuario</strong> criado/atualizado com sucesso.";

    // 8. Insere produtos base (se não existirem)
    $pdo->exec("
        INSERT IGNORE INTO produtos (slug, nome, descricao, preco_unitario, estoque_atual, imagem) VALUES
        ('coxinha-de-frango',          'Coxinha de Frango',                 'Massa crocante, recheio de frango desfiado temperado.',              0.70, 500, 'img/coxinha.png'),
        ('coxinha-de-carne',           'Coxinha de Carne',                  'Coxinha frita com recheio de carne moída temperada.',                0.85, 500, 'img/coxinha_de_carne.png'),
        ('kibe',                       'Kibe',                              'Kibe tradicional, crocante por fora e suculento por dentro.',        0.70, 500, 'img/kibe.png'),
        ('kibe-com-queijo',            'Kibolinha',                         'Kibe com queijo, crocante por fora com queijo derretido por dentro.',0.85, 500, 'img/kibolinha.png'),
        ('fataya',                     'Fataya',                            'Massa com recheio cremoso de carne moída temperada.',                1.10, 500, 'img/fataya.png'),
        ('croquete-de-salsicha',       'Croquete de Salsicha',              'Crocante por fora com recheio cremoso de salsicha por dentro.',      0.70, 500, 'img/croquete_de_salsicha.png'),
        ('bolinha-de-queijo',          'Bolinha de Queijo',                 'Bolinhas crocantes com mozzarella derretida por dentro.',            0.80, 500, 'img/bolinha_queijo.png'),
        ('bolinho-de-bacalhau',        'Bolinho de Bacalhau',               'Crocante por fora com recheio cremoso de bacalhau por dentro.',      1.00, 500, 'img/bolinho_de_bacalhau.png'),
        ('almofadinha-calabresa-queijo','Almofadinha de Calabresa e Queijo','Crocante por fora com recheio cremoso de calabresa e queijo.',       0.80, 500, 'img/almofadinha_calabresa_e_queijo.png')
    ");
    $ok[] = "✅ Produtos base verificados/inseridos.";

} catch (PDOException $e) {
    $erros[] = "❌ Erro de banco: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Setup – Dona Sogra</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Outfit', sans-serif; background: #0f0f0f; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; }
        .card { background: #1a1a1a; border: 1px solid #333; border-radius: 16px; padding: 2.5rem; max-width: 600px; width: 100%; }
        h1 { font-size: 1.6rem; font-weight: 900; margin-bottom: 0.5rem; }
        .sub { color: #888; font-size: 0.9rem; margin-bottom: 2rem; }
        .item { padding: 0.6rem 0.9rem; border-radius: 8px; margin-bottom: 0.5rem; font-size: 0.95rem; }
        .item-ok  { background: rgba(39,174,96,0.15); border: 1px solid rgba(39,174,96,0.3); }
        .item-err { background: rgba(192,57,43,0.15);  border: 1px solid rgba(192,57,43,0.3); color: #e74c3c; }
        .creds { background: #111; border: 2px solid #C0392B; border-radius: 12px; padding: 1.5rem; margin-top: 2rem; }
        .creds h2 { font-size: 1rem; font-weight: 700; color: #C0392B; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 1px; }
        .cred-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #222; font-size: 0.95rem; }
        .cred-row:last-child { border: none; }
        .cred-val { font-weight: 700; color: #F0A500; font-family: monospace; font-size: 1rem; }
        .btn { display: inline-block; margin-top: 1.5rem; background: #C0392B; color: #fff; font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1rem; padding: 0.8rem 2rem; border-radius: 8px; text-decoration: none; transition: 0.2s; }
        .btn:hover { background: #a93226; }
        .warn { margin-top: 1.5rem; background: rgba(240,165,0,0.1); border: 1px solid rgba(240,165,0,0.3); border-radius: 8px; padding: 0.8rem 1rem; color: #F0A500; font-size: 0.85rem; }
    </style>
</head>
<body>
<div class="card">
    <h1>🛠️ Setup – Dona Sogra</h1>
    <p class="sub">Configuração inicial do banco de dados e usuário administrador.</p>

    <?php foreach ($ok as $msg): ?>
        <div class="item item-ok"><?= $msg ?></div>
    <?php endforeach; ?>
    <?php foreach ($erros as $msg): ?>
        <div class="item item-err"><?= $msg ?></div>
    <?php endforeach; ?>

    <?php if (empty($erros)): ?>
    <div class="creds">
        <h2>🔑 Credenciais de Acesso</h2>
        <div class="cred-row"><span>Usuário</span> <span class="cred-val"><?= htmlspecialchars($admin_usuario) ?></span></div>
        <div class="cred-row"><span>Senha</span>   <span class="cred-val"><?= htmlspecialchars($admin_senha) ?></span></div>
    </div>
    <a href="admin/login.php" class="btn">→ Ir para o Login</a>
    <div class="warn">⚠️ <strong>IMPORTANTE:</strong> Apague o arquivo <code>setup_admin.php</code> após fazer login!</div>
    <?php endif; ?>
</div>
</body>
</html>
