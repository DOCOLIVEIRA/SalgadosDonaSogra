<?php
require_once __DIR__ . '/../../php/includes/auth_check.php';
require_once __DIR__ . '/../../db/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido");
}

try {
    $pdo = get_connection();
    
    $nome = trim($_POST['nome'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco_string = str_replace(',', '.', $_POST['preco_unitario'] ?? '');
    $preco = (float)$preco_string;
    $estoque = (int)($_POST['estoque_atual'] ?? 0);
    // Para simplificar, sem upload de imagem agora, usa string
    $imagem = trim($_POST['imagem'] ?? 'img/logo.png');

    if (empty($nome) || empty($slug)) {
        $_SESSION['flash'] = "O nome e slug do produto são obrigatórios!";
        header("Location: /admin/produtos.php");
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO produtos (nome, slug, descricao, preco_unitario, estoque_atual, imagem, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->execute([$nome, $slug, $descricao, $preco, $estoque, $imagem]);

    $_SESSION['flash'] = "Ba-dum-tss! Produto adicionado com sucesso!";
    header("Location: /admin/produtos.php");
    exit;
} catch (Exception $e) {
    session_start(); // Garante sessão caso não inicie no base auth
    $_SESSION['flash'] = "ERRO AO ADICIONAR: " . $e->getMessage();
    header("Location: /admin/produtos.php");
    exit;
}
