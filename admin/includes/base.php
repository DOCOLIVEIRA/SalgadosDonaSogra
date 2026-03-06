<?php
// ============================================================================
// admin/includes/base.php - Layout Base do Painel Admin
// ============================================================================
require_once __DIR__ . '/../../php/includes/auth_check.php';
require_once __DIR__ . '/../../db/db.php';

function render_admin_header($title = 'Painel', $page_title = 'Painel')
{
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?= htmlspecialchars($title) ?> – Dona Sogra Admin</title>
        <link rel="icon" type="image/png" href="/img/logo.png">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;900&display=swap"
            rel="stylesheet" />
        <link rel="stylesheet" href="/admin/css/admin.css" /> <!-- Estilos globais -->
        <?php $page_name = pathinfo($current_page, PATHINFO_FILENAME); ?>
        <?php if (file_exists(__DIR__ . "/../css/{$page_name}.css")): ?>
            <link rel="stylesheet" href="/admin/css/<?= $page_name ?>.css" />
        <?php endif; ?>
    </head>

    <body>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <img src="/img/logo.png" alt="Logo Dona Sogra" />
                <div class="sidebar-brand-text">
                    <h2>Dona Sogra</h2>
                    <p>Administração</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <span class="nav-section-label">Principal</span>
                <a href="/admin/index.php" class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
                    <span class="nav-icon">📋</span> Dashboard
                </a>

                <span class="nav-section-label">Estoque</span>
                <a href="/admin/produtos.php" class="nav-link <?= $current_page == 'produtos.php' ? 'active' : '' ?>">
                    <span class="nav-icon">🥟</span> Produtos & Estoque
                </a>

                <span class="nav-section-label">Inteligência</span>
                <a href="/admin/relatorios.php" class="nav-link <?= $current_page == 'relatorios.php' ? 'active' : '' ?>">
                    <span class="nav-icon">📊</span> Relatórios
                </a>

                <?php if (is_admin()): ?>
                    <span class="nav-section-label">Configurações</span>
                    <a href="/admin/usuarios.php" class="nav-link <?= $current_page == 'usuarios.php' ? 'active' : '' ?>">
                        <span class="nav-icon">👥</span> Usuários
                    </a>
                <?php endif; ?>

                <span class="nav-section-label">Loja</span>
                <a href="/" class="nav-link" target="_blank">
                    <span class="nav-icon">🏪</span> Ver Loja
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr($_SESSION['usuario'], 0, 1)) ?></div>
                    <div class="user-details">
                        <p><?= htmlspecialchars($_SESSION['usuario']) ?></p>
                        <span><?= htmlspecialchars($_SESSION['nivel_acesso']) ?></span>
                    </div>
                </div>
                <a href="/admin/logout.php" class="btn-logout">⎋ Sair do sistema</a>
            </div>
        </aside>

        <div class="main-content">
            <div class="topbar">
                <h1><?= htmlspecialchars($page_title) ?></h1>
                <div class="topbar-right">
                    <span class="badge-role"><?= htmlspecialchars($_SESSION['nivel_acesso']) ?></span>
                </div>
            </div>
            <div class="page-body">
                <!-- As mensagens de flash podem ser implementadas aqui via SESSION -->
                <?php if (isset($_SESSION['flash'])): ?>
                    <div
                        style="background: rgba(39, 174, 96, 0.15); color: #2ecc71; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid rgba(39, 174, 96, 0.3);">
                        <?= htmlspecialchars($_SESSION['flash']);
                        unset($_SESSION['flash']); ?>
                    </div>
                <?php endif; ?>
                <?php
}

function render_admin_footer()
{
    $current_page = basename($_SERVER['PHP_SELF']);
    $page_name = pathinfo($current_page, PATHINFO_FILENAME);
    ?>
            </div> <!-- end page-body -->
        </div> <!-- end main-content -->
        <?php if (file_exists(__DIR__ . "/../js/{$page_name}.js")): ?>
            <script src="/admin/js/<?= $page_name ?>.js"></script>
        <?php endif; ?>
    </body>

    </html>
    <?php
}
?>