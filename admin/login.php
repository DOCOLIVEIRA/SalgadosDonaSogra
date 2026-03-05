<?php
// ============================================================================
// admin/login.php - Tela de Login do Sistema
// ============================================================================
session_start();
require_once '../db.php';

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
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: #0f0f0f;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-box {
            background: #1a1a1a;
            padding: 2.5rem;
            border-radius: 16px;
            border: 1px solid #333;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-box img {
            max-width: 120px;
            margin-bottom: 1.5rem;
        }

        .login-box h2 {
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .login-box p {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
            text-align: left;
        }

        .form-label {
            display: block;
            color: #aaa;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.8rem 1rem;
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
        }

        .form-input:focus {
            outline: none;
            border-color: #C0392B;
        }

        .btn-primary {
            width: 100%;
            padding: 0.8rem;
            background: #C0392B;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 1rem;
        }

        .btn-primary:hover {
            background: #a93226;
        }

        .error-msg {
            background: rgba(192, 57, 43, 0.15);
            color: #e74c3c;
            padding: 0.8rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(192, 57, 43, 0.3);
        }
    </style>
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
</body>

</html>