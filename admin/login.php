<?php
// ============================================================================
// admin/login.php - Tela de Login do Sistema
// ============================================================================
session_start();
require_once __DIR__ . '/../db/db.php';

// Se já estiver logado, redireciona para o painel
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Por favor, preencha usuário e senha.';
    } else {
        $pdo = get_connection();
        // Busca o usuário na tabela 'usuarios'
        $stmt = $pdo->prepare("SELECT id, usuario, senha_hash, nivel_acesso, ativo FROM usuarios WHERE usuario = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $user['ativo']) {
            // Verifica a senha
            if (password_verify($password, $user['senha_hash'])) {
                // Senha correta, cria a sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nivel_acesso'] = $user['nivel_acesso'];

                // Redireciona
                header("Location: index.php");
                exit();
            } else {
                $error = 'Usuário ou senha incorretos.';
            }
        } else {
            $error = 'Usuário incorreto ou inativo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dona Sogra Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="login-box">
        <img src="/img/logo.png" alt="Logo Dona Sogra">
        <h2>Acesso Administrativo</h2>
        <p>Insira suas credenciais para continuar.</p>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label class="form-label">Usuário</label>
                <input type="text" name="username" class="form-input" required autocomplete="username">
            </div>
            <div class="form-group">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            <button type="submit" class="btn-primary">Entrar no Painel</button>
        </form>
    </div>
    <script src="js/login.js"></script>
</body>

</html>