<?php
// ============================================================================
// admin/index.php - Dashboard Principal
// ============================================================================
require_once __DIR__ . '/includes/base.php';
$pdo = get_connection();

// Buscas rápidas para os cards
$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pendente'");
$total_pendentes = $stmt->fetchColumn();

// Usamos DATE(created_at) = CURRENT_DATE para "hoje"
$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURRENT_DATE AND status != 'Cancelado'");
$total_hoje = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(total) FROM orders WHERE DATE(created_at) = CURRENT_DATE AND status != 'Cancelado'");
$faturamento_hoje = $stmt->fetchColumn() ?: 0.00;

// Buscar últimos 50 pedidos (Substitui o SQLAlchemy pela Query Direta)
// Fazemos um LEFT JOIN com users para pegar quem cancelou
$sql_pedidos = "
    SELECT o.*, u.username as cancelado_por_nome 
    FROM orders o 
    LEFT JOIN users u ON o.cancelado_por_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 50
";
$stmt = $pdo->query($sql_pedidos);
$pedidos = $stmt->fetchAll();

render_admin_header('Dashboard', '📋 Dashboard de Pedidos');
?>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 14px;
        padding: 1.2rem 1.25rem;
    }

    .stat-label {
        color: #777;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .stat-value {
        color: #fff;
        font-size: 1.8rem;
        font-weight: 900;
        margin-top: 0.25rem;
        line-height: 1;
    }

    .stat-sub {
        color: #555;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
</style>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">⏳ Pendentes</div>
        <div class="stat-value">
            <?= $total_pendentes ?>
        </div>
        <div class="stat-sub">aguardando preparo</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">📦 Pedidos Hoje</div>
        <div class="stat-value">
            <?= $total_hoje ?>
        </div>
        <div class="stat-sub">excluindo cancelados</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">💰 Faturamento Hoje</div>
        <div class="stat-value" style="font-size:1.3rem;">
            R$
            <?= number_format($faturamento_hoje, 2, ',', '.') ?>
        </div>
        <div class="stat-sub">pedidos não cancelados</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Últimos 50 Pedidos</h2>
    </div>

    <?php if (count($pedidos) > 0): ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Telefone</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido):
                        $data = date('d/m/Y H:i', strtotime($pedido['created_at']));
                        $cls = str_replace(' ', '-', $pedido['status']);
                        ?>
                        <tr>
                            <td style="color:#666; font-size:0.8rem;">#
                                <?= $pedido['id'] ?>
                            </td>
                            <td style="white-space:nowrap; font-size:0.82rem; color:#888;">
                                <?= $data ?>
                            </td>
                            <td style="font-weight:600; color:#ddd;">
                                <?= htmlspecialchars($pedido['cliente_nome']) ?>
                            </td>
                            <td style="color:#888; font-size:0.85rem;">
                                <?= htmlspecialchars($pedido['cliente_tel'] ?: '—') ?>
                            </td>
                            <td style="font-weight:700; color:#F0A500; white-space:nowrap;">
                                R$
                                <?= number_format($pedido['total'], 2, ',', '.') ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $cls ?>">
                                    <?= $pedido['status'] ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap:0.4rem; align-items:center; flex-wrap:wrap;">

                                    <?php if ($pedido['status'] !== 'Cancelado'): ?>

                                        <form method="POST" action="api/atualizar_status.php"
                                            style="display:inline-flex; gap:0.3rem;">
                                            <input type="hidden" name="order_id" value="<?= $pedido['id'] ?>">
                                            <select name="status" class="form-input"
                                                style="padding:0.3rem 0.5rem; font-size:0.78rem; width:auto;">
                                                <?php foreach (['Pendente', 'Em preparo', 'Pronto', 'Entregue'] as $st): ?>
                                                    <option value="<?= $st ?>" <?= $pedido['status'] === $st ? 'selected' : '' ?>>
                                                        <?= $st ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-ghost"
                                                style="padding:0.3rem 0.6rem; font-size:0.78rem;">✓</button>
                                        </form>

                                        <form method="POST" action="api/cancelar_pedido.php"
                                            onsubmit="return confirm('Cancelar Pedido #<?= $pedido['id'] ?>?\n\nO estoque será restaurado automaticamente.\nEsta ação não pode ser desfeita.');">
                                            <input type="hidden" name="order_id" value="<?= $pedido['id'] ?>">
                                            <button type="submit" class="btn btn-danger"
                                                style="padding:0.3rem 0.7rem; font-size:0.78rem;">
                                                ✕ Cancelar
                                            </button>
                                        </form>

                                    <?php else: ?>
                                        <span style="color:#555; font-size:0.78rem; white-space:nowrap;">
                                            <?php if ($pedido['cancelado_por_nome']): ?>
                                                por <strong style="color:#777;">
                                                    <?= htmlspecialchars($pedido['cancelado_por_nome']) ?>
                                                </strong>
                                            <?php endif; ?>
                                            <?php if ($pedido['cancelado_em']): ?>
                                                em
                                                <?= date('d/m H:i', strtotime($pedido['cancelado_em'])) ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align:center; padding:3rem; color:#555;">
            <p style="font-size:2.5rem;">📭</p>
            <p style="margin-top:0.75rem; font-size:0.95rem;">Nenhum pedido ainda.</p>
        </div>
    <?php endif; ?>
</div>

<?php render_admin_footer(); ?>