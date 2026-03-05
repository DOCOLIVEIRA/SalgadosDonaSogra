<?php
// ============================================================================
// admin/includes/base.php - Layout Base do Painel Admin
// ============================================================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../db.php';

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
        <link rel="stylesheet" href="/admin/css/admin.css" /> <!-- Estilos movidos para arquivo -->
        <style>
            /* --- Estilos base rápidos transferidos do Jinja --- */
            *,
            *::before,
            *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-family: 'Outfit', sans-serif;
                background: #0f0f0f;
                color: #e0e0e0;
                min-height: 100vh;
                display: flex;
            }

            .sidebar {
                width: 240px;
                min-height: 100vh;
                background: #1a1a1a;
                border-right: 1px solid #2a2a2a;
                display: flex;
                flex-direction: column;
                flex-shrink: 0;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 100;
                transition: transform 0.3s ease;
            }

            .sidebar-brand {
                padding: 1.4rem 1.2rem;
                border-bottom: 1px solid #2a2a2a;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .sidebar-brand img {
                height: 44px;
                width: auto;
                object-fit: contain;
            }

            .sidebar-brand-text h2 {
                color: #fff;
                font-size: 0.95rem;
                font-weight: 900;
            }

            .sidebar-brand-text p {
                color: #F0A500;
                font-size: 0.6rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.12em;
            }

            .sidebar-nav {
                padding: 1rem 0.75rem;
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .nav-section-label {
                color: #555;
                font-size: 0.65rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                padding: 0.75rem 0.75rem 0.3rem;
            }

            .nav-link {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.65rem 0.9rem;
                border-radius: 10px;
                color: #aaa;
                text-decoration: none;
                font-size: 0.9rem;
                font-weight: 500;
                transition: background 0.2s, color 0.2s;
            }

            .nav-link:hover {
                background: #252525;
                color: #fff;
            }

            .nav-link.active {
                background: rgba(192, 57, 43, 0.2);
                color: #e74c3c;
                font-weight: 700;
            }

            .nav-icon {
                font-size: 1.05rem;
                width: 22px;
                text-align: center;
            }

            .sidebar-footer {
                padding: 1rem 0.75rem;
                border-top: 1px solid #2a2a2a;
            }

            .user-info {
                display: flex;
                align-items: center;
                gap: 0.6rem;
                margin-bottom: 0.75rem;
            }

            .user-avatar {
                width: 34px;
                height: 34px;
                border-radius: 50%;
                background: #C0392B;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 900;
                font-size: 0.85rem;
                flex-shrink: 0;
            }

            .user-details p {
                color: #fff;
                font-size: 0.82rem;
                font-weight: 700;
            }

            .user-details span {
                color: #F0A500;
                font-size: 0.65rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            .btn-logout {
                width: 100%;
                padding: 0.55rem;
                background: #2a2a2a;
                color: #888;
                border: 1px solid #333;
                border-radius: 8px;
                font-family: 'Outfit', sans-serif;
                font-size: 0.82rem;
                font-weight: 600;
                cursor: pointer;
                text-align: center;
                text-decoration: none;
                display: block;
                transition: background 0.2s, color 0.2s;
            }

            .btn-logout:hover {
                background: rgba(192, 57, 43, 0.2);
                color: #e74c3c;
                border-color: rgba(192, 57, 43, 0.3);
            }

            .main-content {
                margin-left: 240px;
                flex: 1;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }

            .topbar {
                background: #151515;
                border-bottom: 1px solid #2a2a2a;
                padding: 1rem 1.75rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .topbar h1 {
                color: #fff;
                font-size: 1.2rem;
                font-weight: 800;
            }

            .topbar-right {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .badge-role {
                background: rgba(240, 165, 0, 0.15);
                color: #F0A500;
                font-size: 0.7rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                border: 1px solid rgba(240, 165, 0, 0.3);
            }

            .page-body {
                padding: 1.75rem;
                flex: 1;
            }

            /* Utils globais admin */
            .card {
                background: #1a1a1a;
                border: 1px solid #2a2a2a;
                border-radius: 14px;
                overflow: hidden;
            }

            .card-header {
                padding: 1rem 1.25rem;
                border-bottom: 1px solid #2a2a2a;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .card-header h2 {
                color: #fff;
                font-size: 1rem;
                font-weight: 700;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            thead tr {
                background: #111;
            }

            th {
                text-align: left;
                padding: 0.7rem 1rem;
                color: #666;
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                border-bottom: 1px solid #2a2a2a;
            }

            td {
                padding: 0.75rem 1rem;
                color: #ccc;
                font-size: 0.9rem;
                border-bottom: 1px solid #222;
            }

            tbody tr:last-child td {
                border-bottom: none;
            }

            tbody tr:hover {
                background: #1f1f1f;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.2rem 0.65rem;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .status-Pendente {
                background: rgba(240, 165, 0, 0.15);
                color: #F0A500;
                border: 1px solid rgba(240, 165, 0, 0.3);
            }

            .status-Em-preparo {
                background: rgba(52, 152, 219, 0.15);
                color: #5dade2;
                border: 1px solid rgba(52, 152, 219, 0.3);
            }

            .status-Pronto {
                background: rgba(155, 89, 182, 0.15);
                color: #9b59b6;
                border: 1px solid rgba(155, 89, 182, 0.3);
            }

            .status-Entregue {
                background: rgba(39, 174, 96, 0.15);
                color: #2ecc71;
                border: 1px solid rgba(39, 174, 96, 0.3);
            }

            .status-Cancelado {
                background: rgba(192, 57, 43, 0.12);
                color: #e74c3c;
                border: 1px solid rgba(192, 57, 43, 0.25);
            }

            .btn {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                padding: 0.45rem 0.9rem;
                border-radius: 8px;
                font-family: 'Outfit', sans-serif;
                font-size: 0.82rem;
                font-weight: 700;
                cursor: pointer;
                border: none;
                text-decoration: none;
                transition: opacity 0.2s, transform 0.15s;
            }

            .btn:hover {
                opacity: 0.85;
                transform: translateY(-1px);
            }

            .btn:active {
                transform: scale(0.97);
            }

            .btn-danger {
                background: rgba(192, 57, 43, 0.8);
                color: #fff;
            }

            .btn-primary {
                background: #C0392B;
                color: #fff;
            }

            .btn-ghost {
                background: #2a2a2a;
                color: #aaa;
                border: 1px solid #333;
            }

            .btn-success {
                background: rgba(39, 174, 96, 0.8);
                color: #fff;
            }

            .btn-warning {
                background: rgba(240, 165, 0, 0.8);
                color: #111;
            }

            .form-input {
                padding: 0.65rem 0.9rem;
                background: #111;
                border: 1px solid #333;
                border-radius: 9px;
                color: #fff;
                font-family: 'Outfit', sans-serif;
                font-size: 0.95rem;
            }

            .form-input:focus {
                outline: none;
                border-color: #C0392B;
            }
        </style>
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
    ?>
            </div> <!-- end page-body -->
        </div> <!-- end main-content -->
    </body>

    </html>
    <?php
}
?>