<?php
// ============================================================================
// admin/api/atualizar_estoque.php
// ============================================================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método não permitido.");
}

$product_id = $_POST['product_id'] ?? null;
$quantidade = $_POST['quantidade'] ?? null;

if (!$product_id || $quantidade === null || $quantidade < 0) {
    $_SESSION['flash'] = "Dados inválidos para atualizar o estoque.";
    header("Location: ../produtos.php");
    exit();
}

$pdo = get_connection();

try {
    $stmt = $pdo->prepare("UPDATE products SET quantidade_estoque = ? WHERE id = ?");
    $stmt->execute([$quantidade, $product_id]);
    $_SESSION['flash'] = "✅ Estoque atualizado para $quantidade unidades.";
} catch (Exception $e) {
    $_SESSION['flash'] = "❌ Erro ao atualizar estoque: " . $e->getMessage();
}

header("Location: ../produtos.php");
exit();
?>