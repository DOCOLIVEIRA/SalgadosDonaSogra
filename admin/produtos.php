<?php
// ============================================================================
// admin/produtos.php - Gestão de Produtos e Estoque
// ============================================================================
require_once __DIR__ . '/includes/base.php';

$pdo = get_connection();
$stmt = $pdo->query("SELECT * FROM produtos ORDER BY nome ASC");
$produtos = $stmt->fetchAll();

render_admin_header('Produtos & Estoque', '🥟 Produtos & Controle de Estoque');
?>

<div class="card">
    <div class="card-header">
        <h2>Cardápio e Estoque Atual</h2>
        <span style="color:#555; font-size:0.82rem;"><?= count($produtos) ?> produto(s) cadastrado(s)</span>
    </div>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço/un.</th>
                    <th>R$/cento</th>
                    <th>Estoque</th>
                    <th>Status</th>
                    <th>Alterar Estoque</th>
                    <th>Alterar Preço</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $p): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.6rem;">
                            <?php if ($p['imagem']): ?>
                            <img src="/<?= htmlspecialchars($p['imagem']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>"
                                style="width:36px; height:36px; border-radius:8px; object-fit:cover; background:#111;" />
                            <?php endif; ?>
                            <div>
                                <div style="font-weight:700; color:#ddd;"><?= htmlspecialchars($p['nome']) ?></div>
                                <div style="font-size:0.75rem; color:#555;"><?= htmlspecialchars($p['slug']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="color:#F0A500; font-weight:700;">
                        R$ <?= number_format($p['preco_unitario'], 2, ',', '.') ?>
                    </td>
                    <td style="color:#888; font-size:0.88rem;">
                        R$ <?= number_format($p['preco_unitario'] * 100, 2, ',', '.') ?>
                    </td>
                    <td>
                        <?php if ($p['estoque_atual'] <= 50): ?>
                            <span style="color:#e74c3c; font-weight:700;">⚠ <?= $p['estoque_atual'] ?></span>
                        <?php elseif ($p['estoque_atual'] <= 150): ?>
                            <span style="color:#F0A500; font-weight:700;">● <?= $p['estoque_atual'] ?></span>
                        <?php else: ?>
                            <span style="color:#2ecc71; font-weight:700;">● <?= $p['estoque_atual'] ?></span>
                        <?php endif; ?>
                        <span style="color:#555; font-size:0.75rem;"> un.</span>
                    </td>
                    <td>
                        <?php if ($p['ativo']): ?>
                        <span class="status-badge status-Entregue">Ativo</span>
                        <?php else: ?>
                        <span class="status-badge status-Cancelado">Inativo</span>
                        <?php endif; ?>
                    </td>

                    <!-- Alterar Estoque -->
                    <td>
                        <form method="POST" action="api/atualizar_estoque.php" style="display:flex; gap:0.4rem; align-items:center;">
                            <input type="hidden" name="produto_id" value="<?= $p['id'] ?>">
                            <input type="number" name="quantidade" value="<?= $p['estoque_atual'] ?>" min="0"
                                class="form-input" style="width:90px; padding:0.3rem 0.5rem; font-size:0.85rem;" />
                            <button type="submit" class="btn btn-success" style="padding:0.3rem 0.7rem; font-size:0.8rem;">
                                💾
                            </button>
                        </form>
                    </td>

                    <!-- Alterar Preço (abre modal JS) -->
                    <td>
                        <button class="btn btn-warning"
                            style="padding:0.3rem 0.7rem; font-size:0.8rem; white-space:nowrap;"
                            onclick="abrirModalPreco(<?= $p['id'] ?>, '<?= addslashes($p['nome']) ?>', <?= $p['preco_unitario'] ?>)">
                            ✏ Preço
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:0.75rem; display:flex; gap:1.5rem; font-size:0.78rem; color:#555;">
    <span><span style="color:#e74c3c;">⚠ Vermelho</span> = estoque crítico (≤ 50)</span>
    <span><span style="color:#F0A500;">●</span> Amarelo = atenção (≤ 150)</span>
    <span><span style="color:#2ecc71;">●</span> Verde = OK</span>
</div>

<!-- Modal de Preço e Scripts específicos para Produtos -->
<style>
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.7); z-index: 999; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal { background: #1a1a1a; border: 1px solid #333; border-radius: 16px; padding: 2rem; max-width: 420px; width: 90%; animation: slideUp 0.25s ease; }
    .modal h3 { color: #fff; font-size: 1.1rem; font-weight: 800; margin-bottom: 0.75rem; }
    .modal p { color: #aaa; font-size: 0.9rem; line-height: 1.55; margin-bottom: 1.5rem; }
    .modal-actions { display: flex; gap: 0.75rem; justify-content: flex-end; }
    .form-group { margin-bottom: 1rem; }
    .form-label { display: block; color: #888; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.4rem; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="modal-overlay" id="precoModal">
    <div class="modal">
        <h3>✏ Alterar Preço de Produto</h3>
        <p id="precoModalDesc" style="margin-bottom:1rem;"></p>

        <form method="POST" id="precoForm" action="api/alterar_preco.php">
            <input type="hidden" name="produto_id" id="modalProductId" value="" />
            <input type="hidden" name="confirmado" value="sim" />
            
            <div class="form-group">
                <label class="form-label">Novo preço por unidade (R$)</label>
                <input type="number" name="novo_preco" id="novoPrecoInput" step="0.01" min="0.01" class="form-input" style="width:100%" placeholder="0.00" required />
                <p style="margin-top:0.4rem; font-size:0.75rem; color:#555;">
                    💡 Equivale a R$ <span id="precoCento">0,00</span> por cento (100 un.)
                </p>
            </div>

            <div style="background:rgba(240,165,0,0.08); border:1px solid rgba(240,165,0,0.2); border-radius:8px; padding:0.75rem; margin-bottom:1.25rem;">
                <p style="color:#F0A500; font-size:0.82rem; font-weight:600;">
                    ⚠ Esta alteração será registrada no histórico de preços com seu nome de usuário.
                </p>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="fecharModalPreco()">Cancelar</button>
                <button type="submit" class="btn btn-warning" style="color:#111;">✓ Confirmar Alteração</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalPreco(productId, nome, precoAtual) {
        document.getElementById('precoModalDesc').textContent = `Produto: ${nome} | Preço atual: R$ ${precoAtual.toFixed(2)}/un. (R$ ${(precoAtual * 100).toFixed(2)}/cento)`;
        document.getElementById('novoPrecoInput').value = precoAtual.toFixed(2);
        document.getElementById('precoCento').textContent = (precoAtual * 100).toFixed(2);
        document.getElementById('modalProductId').value = productId;
        document.getElementById('precoModal').classList.add('open');
    }

    function fecharModalPreco() {
        document.getElementById('precoModal').classList.remove('open');
    }

    document.getElementById('novoPrecoInput').addEventListener('input', function () {
        const v = parseFloat(this.value) || 0;
        document.getElementById('precoCento').textContent = (v * 100).toFixed(2);
    });

    document.getElementById('precoModal').addEventListener('click', function (e) {
        if (e.target === this) fecharModalPreco();
    });
</script>

<?php render_admin_footer(); ?>