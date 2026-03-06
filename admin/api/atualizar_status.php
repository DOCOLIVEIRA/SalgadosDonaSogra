<?php
// ============================================================================
// admin/api/atualizar_status.php - Atualiza status de um pedido
// ============================================================================
require_once __DIR__ . '/../../php/includes/auth_check.php';
require_once __DIR__ . '/../../db/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método não permitido.");
}

$pedido_id = $_POST['pedido_id'] ?? null;
$status = $_POST['status'] ?? null;

// Lista de status válidos que não são 'Cancelado' (que tem rota própria)
$valid_statuses = ['Pendente', 'Em preparo', 'Pronto', 'Entregue'];

if (!$pedido_id || !in_array($status, $valid_statuses)) {
    $_SESSION['flash'] = "Dados inválidos para alterar status.";
    header("Location: ../index.php");
    exit();
}

$pdo = get_connection();

try {
    // Tenta atualizar onde status != Cancelado
    $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ? AND status != 'Cancelado'");
    $stmt->execute([$status, $pedido_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['flash'] = "Status do pedido #$pedido_id atualizado para '$status'.";
    } else {
        $_SESSION['flash'] = "Pedido não encontrado ou não pode ser alterado (pois já está Cancelado).";
    }

} catch (Exception $e) {
    $_SESSION['flash'] = "Erro ao atualizar status: " . $e->getMessage();
}

header("Location: ../index.php");
exit();
?>