<?php
require_once __DIR__ . '/../../php/includes/auth_check.php';
require_once __DIR__ . '/../../db/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido");
}

try {
    $pdo = get_connection();
    
    $id = (int)$_POST['produto_id'];
    $ativo = (int)$_POST['ativo']; // 1 or 0
    
    $stmt = $pdo->prepare("UPDATE produtos SET ativo = ? WHERE id = ?");
    $stmt->execute([$ativo, $id]);

    $statusStr = $ativo ? "ativado" : "desativado";
    $_SESSION['flash'] = "Produto {$statusStr} com sucesso!";
    header("Location: /admin/produtos.php");
    exit;
} catch (Exception $e) {
    die("Erro ao alterar status do produto: " . $e->getMessage());
}
