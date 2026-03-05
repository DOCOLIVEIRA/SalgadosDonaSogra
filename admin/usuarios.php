<?php
// ============================================================================
// admin/usuarios.php - Gestão de Usuários (Apenas Admin)
// ============================================================================
require_once __DIR__ . '/includes/base.php';
require_admin(); // Bloqueia quem não for role = 'admin'

$pdo = get_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // -- Criar novo usuário --
    if ($acao === 'criar') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'staff';

        if ($username && $password) {
            // Verifica duplicidade
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);

            if ($stmt->fetch()) {
                $_SESSION['flash'] = "O usuário '$username' já existe.";
            } else {
                // Insere com Hash BCRYPT
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hash, $role]);
                $_SESSION['flash'] = "Usuário '$username' cadastrado com sucesso!";
            }
        }
    }

    // -- Alternar Status (Ativo/Inativo) --
    if ($acao === 'toggle') {
        $user_id = $_POST['user_id'] ?? null;
        if ($user_id && $user_id != $_SESSION['user_id']) { // Impede desativar a si próprio
            $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['flash'] = "Status do usuário modificado!";
        } else {
            $_SESSION['flash'] = "Erro ou tentativa de desativar a própria conta.";
        }
    }

    header("Location: usuarios.php");
    exit();
}

// Lista os usuários
$usuarios = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

render_admin_header('Usuários', '👥 Gestão de Acessos');
?>

<div style="display:flex; gap:1.5rem; align-items:start; flex-wrap:wrap;">

    <!-- Tabela de Usuários (2/3 da tela) -->
    <div class="card" style="flex:2; min-width:300px;">
        <div class="card-header">
            <h2>Usuários do Sistema</h2>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Perfil</th>
                        <th>Criado em</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:0.6rem;">
                                    <div class="user-avatar" style="width:28px; height:28px; font-size:0.75rem;">
                                        <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                    </div>
                                    <span style="font-weight:700; color:#ddd;">
                                        <?= htmlspecialchars($u['username']) ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-role">
                                    <?= htmlspecialchars($u['role']) ?>
                                </span>
                            </td>
                            <td style="color:#888; font-size:0.85rem;">
                                <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                            </td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="status-badge status-Entregue">Ativo</span>
                                <?php else: ?>
                                    <span class="status-badge status-Cancelado">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" action="usuarios.php">
                                        <input type="hidden" name="acao" value="toggle">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn <?= $u['is_active'] ? 'btn-danger' : 'btn-success' ?>"
                                            style="padding:0.3rem 0.6rem; font-size:0.75rem;">
                                            <?= $u['is_active'] ? '🚫 Desativar' : '✅ Reativar' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#555; font-size:0.75rem;">Sua conta</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form de Criação (1/3 da tela) -->
    <div class="card" style="flex:1; min-width:280px;">
        <div class="card-header">
            <h2>Novo Usuário</h2>
        </div>
        <div style="padding:1.5rem;">
            <form method="POST" action="usuarios.php">
                <input type="hidden" name="acao" value="criar">
                <div class="form-group">
                    <label class="form-label">Nome de Usuário</label>
                    <input type="text" name="username" class="form-input" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" class="form-input" required autocomplete="new-password">
                </div>
                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label class="form-label">Nível de Acesso</label>
                    <select name="role" class="form-input">
                        <option value="staff">Staff (Atendente)</option>
                        <option value="admin">Administrador Geral</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">➕ Cadastrar
                    Usuário</button>
            </form>
        </div>
    </div>

</div>

<?php render_admin_footer(); ?>