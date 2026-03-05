<?php
// ============================================================================
// includes/auth_check.php - Proteção de Rotas
// ============================================================================
// Inclua este arquivo no topo de qualquer página que precise de login.
// Ele verifica se a sessão existe e, se não, redireciona para o login.
// ============================================================================

session_start();

// Verifica se o usuário não está logado
if (!isset($_SESSION['user_id'])) {
    // Salva a página que ele tentou acessar (opcional, para redirecionar depois do login)
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    // Redireciona para o login
    header("Location: /admin/login.php");
    exit();
}

// Helpers para verificar permissão
function is_admin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function require_admin()
{
    if (!is_admin()) {
        die("Acesso negado. Apenas administradores podem acessar esta página.");
    }
}
?>