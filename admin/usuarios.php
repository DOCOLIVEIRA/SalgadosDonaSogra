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
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
            $stmt->execute([$username]);

            if ($stmt->fetch()) {
                $_SESSION['flash'] = "O usuário '$username' já existe.";
            } else {
                // Insere com Hash BCRYPT
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha_hash, nivel_acesso) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hash, $role]);
                $_SESSION['flash'] = "Usuário '$username' cadastrado com sucesso!";
            }
        }
    }

    // -- Alternar Status (Ativo/Inativo) --
    if ($acao === 'toggle') {
        $user_id = $_POST['user_id'] ?? null;
        if ($user_id && $user_id != $_SESSION['user_id']) { // Impede desativar a si próprio
            $stmt = $pdo->prepare("UPDATE usuarios SET ativo = NOT ativo WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['flash'] = "Status do usuário modificado!";
        } else {
            $_SESSION['flash'] = "Erro ou tentativa de desativar a própria conta.";
        }
    }

    // -- Alterar Senha --
    if ($acao === 'senha') {
        $user_id = $_POST['user_id'] ?? null;
        $nova_senha = $_POST['nova_senha'] ?? '';
        if ($user_id && strlen($nova_senha) >= 4) {
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET senha_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $user_id]);
            $_SESSION['flash'] = "Senha atualizada com sucesso!";
        } else {
            $_SESSION['flash'] = "Erro: A senha precisa ter pelo menos 4 caracteres.";
        }
    }

    header("Location: usuarios.php");
    exit();
}

// Lista os usuários
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY criado_em DESC")->fetchAll();

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
                        <th>Ativar/Desativar</th>
                        <th>Alterar Senha </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:0.6rem;">
                                    <div class="user-avatar" style="width:28px; height:28px; font-size:0.75rem;">
                                        <?= strtoupper(substr($u['usuario'], 0, 1)) ?>
                                    </div>
                                    <span style="font-weight:700; color:#ddd;"><?= htmlspecialchars($u['usuario']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-role"><?= htmlspecialchars($u['nivel_acesso']) ?></span>
                            </td>
                            <td style="color:#888; font-size:0.85rem;">
                                <?= date('d/m/Y', strtotime($u['criado_em'])) ?>
                            </td>
                            <td>
                                <?php if ($u['ativo']): ?>
                                    <span class="status-badge status-Entregue">Ativo</span>
                                <?php else: ?>
                                    <span class="status-badge status-Cancelado">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:0.4rem;">
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" action="usuarios.php" style="margin:0;">
                                            <input type="hidden" name="acao" value="toggle">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn <?= $u['ativo'] ? 'btn-danger' : 'btn-success' ?>"
                                                style="padding:0.3rem 0.6rem; font-size:0.75rem;">
                                                <?= $u['ativo'] ? '🚫 Desativar' : '✅ Reativar' ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:#555; font-size:0.75rem;">Sua conta</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex; gap:0.4rem;">
                                    <button type="button" class="btn btn-warning"
                                        style="padding:0.3rem 0.6rem; font-size:0.75rem;"
                                        onclick="abrirModalSenha(<?= $u['id'] ?>, '<?= htmlspecialchars($u['usuario']) ?>')">
                                        🔑 Senha
                                    </button>
                                </div>
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

                <div class="form-row">
                    <div class="form-group form-col">
                        <label class="form-label">Nome de Usuário</label>
                        <input type="text" name="username" class="form-input" required autocomplete="off"
                            style="width:100%;">
                    </div>
                    <div class="form-group form-col">
                        <label class="form-label">Senha</label>
                        <input type="password" name="password" class="form-input" required autocomplete="new-password"
                            style="width:100%;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:1.5rem;">
                    <label class="form-label">Nível de Acesso</label>
                    <select name="role" class="form-input" style="width:100%;">
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

<!-- Modal Alterar Senha Nativo -->
<dialog id="modal-senha"
    style="margin: auto; border:none; border-radius:12px; background:#1a1a1a; padding:0; color:#fff; width:90%; max-width:400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
    <div
        style="padding: 1.5rem; border-bottom: 1px solid #2a2a2a; display:flex; justify-content:space-between; align-items:center;">
        <h3 style="font-size:1.1rem; font-weight:700;">Alterar Senha</h3>
        <button type="button" onclick="document.getElementById('modal-senha').close()"
            style="background:none; border:none; color:#888; font-size:1.2rem; cursor:pointer;">&times;</button>
    </div>
    <div style="padding: 1.5rem;">
        <p style="margin-bottom: 1rem; color:#aaa; font-size:0.9rem;">Defina a nova senha para <strong
                id="modal-nome-usuario" style="color:#C0392B;"></strong>.</p>
        <form method="POST" action="usuarios.php">
            <input type="hidden" name="acao" value="senha">
            <input type="hidden" id="modal-user-id" name="user_id" value="">
            <div class="form-group">
                <label>Nova Senha</label>
                <input type="password" name="nova_senha" class="form-input" style="width:100%;" required minlength="4">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.5rem; margin-top:1.5rem;">
                <button type="button" class="btn btn-ghost"
                    onclick="document.getElementById('modal-senha').close()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Senha</button>
            </div>
        </form>
    </div>
</dialog>

<script>
    function abrirModalSenha(id, nome) {
        document.getElementById('modal-user-id').value = id;
        document.getElementById('modal-nome-usuario').innerText = nome;
        document.getElementById('modal-senha').showModal();
    }
</script>

<?php render_admin_footer(); ?>