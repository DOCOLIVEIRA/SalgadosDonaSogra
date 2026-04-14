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
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2>Cardápio e Estoque Atual</h2>
            <span style="color:#555; font-size:0.82rem;"><?= count($produtos) ?> produto(s) cadastrado(s)</span>
        </div>
        <button class="btn btn-success" onclick="abrirModalAdicionarProduto()">+ Adicionar Produto</button>
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
                    <td style="display:flex; gap:0.5rem;">
                        <button class="btn btn-warning"
                            style="padding:0.3rem 0.7rem; font-size:0.8rem; white-space:nowrap;"
                            onclick="abrirModalPreco(<?= $p['id'] ?>, '<?= addslashes($p['nome']) ?>', <?= $p['preco_unitario'] ?>)">
                            ✏ Preço
                        </button>
                        <!-- Ativar/Desativar -->
                        <form method="POST" action="api/toggle_produto.php" style="margin:0;">
                            <input type="hidden" name="produto_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="ativo" value="<?= $p['ativo'] ? 0 : 1 ?>">
                            <button type="submit" class="btn <?= $p['ativo'] ? 'btn-danger' : 'btn-success' ?>" style="padding:0.3rem 0.7rem; font-size:0.8rem; white-space:nowrap;">
                                <?= $p['ativo'] ? 'Desativar' : 'Ativar' ?>
                            </button>
                        </form>
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

<div class="modal-overlay" id="precoModal">
    <div class="modal">
        <h3>✏ Alterar Preço de Produto</h3>
        <p id="precoModalDesc" style="margin-bottom:1rem;"></p>

        <form method="POST" id="precoForm" action="api/alterar_preco.php">
            <input type="hidden" name="produto_id" id="modalProductId" value="" />
            <input type="hidden" name="confirmado" value="sim" />
            
            <div class="form-group">
                <label class="form-label">Novo preço por unidade (R$)</label>
                <input type="text" name="novo_preco" id="novoPrecoInput" class="form-input" style="width:100%" placeholder="Ex: 0,80 ou 0.80" required />
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

<!-- Modal Adicionar Produto -->
<div class="modal-overlay" id="adicionarProdutoModal">
    <div class="modal" style="max-width:500px;">
        <h3>🥟 Adicionar Novo Produto</h3>
        <p style="margin-bottom:1rem; color:#555;">Preencha os dados do novo salgado abaixo.</p>

        <form method="POST" action="api/adicionar_produto.php">
            <div class="form-group">
                <label class="form-label">Nome do Produto</label>
                <input type="text" name="nome" class="form-input" style="width:100%" placeholder="Ex: Risólis de Presunto" required onkeyup="gerarSlug(this.value)" />
            </div>
            
            <div class="form-group">
                <label class="form-label">Identificador (Slug - sem espaços/acentos)</label>
                <input type="text" name="slug" id="slugInput" class="form-input" style="width:100%" placeholder="ex: risolis-de-presunto" required readonly />
            </div>

            <div class="form-group">
                <label class="form-label">Descrição Breve</label>
                <input type="text" name="descricao" class="form-input" style="width:100%" placeholder="Delicioso salgado frito..."/>
            </div>

            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Preço por Unidade (R$)</label>
                    <input type="text" name="preco_unitario" class="form-input" style="width:100%" placeholder="Ex: 0,80 ou 0.80" required />
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Estoque Inicial</label>
                    <input type="number" name="estoque_atual" min="0" class="form-input" style="width:100%" value="500" required />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Caminho da Imagem (Opcional)</label>
                <input type="text" name="imagem" class="form-input" style="width:100%" placeholder="img/coxinha.png" value="img/logo.png" />
                <p style="margin-top:0.4rem; font-size:0.75rem; color:#555;">
                    💡 Para alterar a imagem, adicione o arquivo PNG na pasta img/ e digite o caminho acima.
                </p>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="fecharModalAdicionarProduto()">Cancelar</button>
                <button type="submit" class="btn btn-success">✓ Salvar Novo Produto</button>
            </div>
        </form>
    </div>
</div>

<script>
    function gerarSlug(text) {
        let slug = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        slug = slug.replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        document.getElementById('slugInput').value = slug;
    }
    
    function abrirModalAdicionarProduto() {
        document.getElementById('adicionarProdutoModal').classList.add('active');
    }
    function fecharModalAdicionarProduto() {
        document.getElementById('adicionarProdutoModal').classList.remove('active');
    }

    // Modal de Preço Restaurado
    function abrirModalPreco(id, nome, precoAtual) {
        document.getElementById('modalProductId').value = id;
        document.getElementById('precoModalDesc').innerHTML = `Salgado: <strong>${nome}</strong><br>Preço Atual: R$ ${precoAtual.toFixed(2).replace('.', ',')}`;
        document.getElementById('novoPrecoInput').value = precoAtual.toFixed(2);
        atualizarPrecoCento();
        document.getElementById('precoModal').classList.add('active');
    }
    function fecharModalPreco() {
        document.getElementById('precoModal').classList.remove('active');
    }
    
    document.getElementById('novoPrecoInput').addEventListener('input', atualizarPrecoCento);
    
    function atualizarPrecoCento() {
        let textVal = document.getElementById('novoPrecoInput').value.replace(',', '.');
        let val = parseFloat(textVal) || 0;
        document.getElementById('precoCento').textContent = (val * 100).toFixed(2).replace('.', ',');
    }
</script>


<?php render_admin_footer(); ?>