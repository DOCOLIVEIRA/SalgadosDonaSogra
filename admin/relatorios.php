<?php
// ============================================================================
// admin/relatorios.php - Inteligência de Negócios e Relatórios
// ============================================================================
require_once __DIR__ . '/includes/base.php';
$pdo = get_connection();

// ── Parâmetros do filtro de data ─────────────────────────────────────────
$hoje = date('Y-m-d');
$data_inicio_str = $_GET['inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$data_fim_str = $_GET['fim'] ?? $hoje;

// Validação básica de datas
$data_inicio = $data_inicio_str . " 00:00:00";
$data_fim = $data_fim_str . " 23:59:59";

// ── 1. CARD CONTÁBIL MENSAL (Pedido do Usuário) ─────────────────────────
// Faturamento usando SELECT SUM(valor_total) FROM pedidos WHERE MONTH(criado_em) = MONTH(CURRENT_DATE())
$sql_contabil = "
    SELECT 
        SUM(valor_total) as faturamento_mes,
        COUNT(id) as qtd_transacoes
    FROM pedidos 
    WHERE MONTH(criado_em) = MONTH(CURRENT_DATE())
      AND YEAR(criado_em) = YEAR(CURRENT_DATE())
      AND status != 'Cancelado'
";
$contabil = $pdo->query($sql_contabil)->fetch();
$faturamento_mes = $contabil['faturamento_mes'] ?: 0.00;
$qtd_transacoes = $contabil['qtd_transacoes'] ?: 0;


// ── 2. FINANCEIRO DO PERÍODO SELECIONADO ──────────────────────────────────
$sql_financeiro = "
    SELECT 
        SUM(valor_total) as total_vendas, 
        COUNT(id) as qtd_pedidos 
    FROM pedidos 
    WHERE criado_em BETWEEN ? AND ? 
      AND status != 'Cancelado'
";
$stmt = $pdo->prepare($sql_financeiro);
$stmt->execute([$data_inicio, $data_fim]);
$fin = $stmt->fetch();

$total_vendas = $fin['total_vendas'] ?: 0.00;
$qtd_pedidos = $fin['qtd_pedidos'] ?: 0;
$ticket_medio = $qtd_pedidos > 0 ? ($total_vendas / $qtd_pedidos) : 0.00;

$stmt_canc = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE criado_em BETWEEN ? AND ? AND status = 'Cancelado'");
$stmt_canc->execute([$data_inicio, $data_fim]);
$total_cancelados = $stmt_canc->fetchColumn();


// ── 3. CURVA ABC (Pedido do Usuário: GROUP BY e ORDER BY SUM DESC) ────────
// Agrupa produtos mais vendidos pela receita e quantidade
$sql_abc = "
    SELECT 
        p.nome, 
        SUM(i.quantidade) as total_unidades,
        SUM(i.quantidade * i.preco_unitario) as receita_total
    FROM produtos p 
    JOIN itens_pedido i ON p.id = i.produto_id
    JOIN pedidos o ON o.id = i.pedido_id
    WHERE o.status != 'Cancelado'
    GROUP BY p.id
    ORDER BY receita_total DESC
";
$abc_raw = $pdo->query($sql_abc)->fetchAll();

// Calcula Totais para Classificação A, B, C
$receita_total_geral = array_sum(array_column($abc_raw, 'receita_total')) ?: 1;
$acumulado = 0;
$curva_abc = [];

foreach ($abc_raw as $row) {
    $acumulado += $row['receita_total'];
    $pct_acumulado = $acumulado / $receita_total_geral;

    if ($pct_acumulado <= 0.70)
        $classe = 'A';
    elseif ($pct_acumulado <= 0.90)
        $classe = 'B';
    else
        $classe = 'C';

    $row['classe'] = $classe;
    $curva_abc[] = $row;
}


// ── 4. HISTÓRICO DE PREÇOS ───────────────────────────────────────────────
$sql_historico = "
    SELECT 
        l.*, 
        p.nome as produto_nome, 
        u.usuario as changed_by_nome
    FROM historico_precos l
    JOIN produtos p ON l.produto_id = p.id
    JOIN usuarios u ON l.alterado_por_id = u.id
    ORDER BY l.alterado_em DESC LIMIT 100
";
$historico_precos = $pdo->query($sql_historico)->fetchAll();


render_admin_header('Relatórios', '📊 Inteligência e Relatórios');
?>

<style>
    /* Estilos para o modo de Impressão do Relatório Contábil */
    @media print {
        body * {
            visibility: hidden;
        }

        .sidebar,
        .topbar {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
        }

        #area-impressao,
        #area-impressao * {
            visibility: visible;
        }

        #area-impressao {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .no-print {
            display: none !important;
        }
    }
</style>

<!-- =========================================================================
     CARD CONTÁBIL MENSAL (Destaque Principal)
     ========================================================================= -->
<div class="card" id="area-impressao" style="margin-bottom:2rem; border-color:#C0392B;">
    <div class="card-header" style="background:rgba(192, 57, 43, 0.1);">
        <div style="display:flex; align-items:center; gap:0.5rem;">
            <span style="font-size:1.5rem;">📅</span>
            <div>
                <h2 style="color:#e74c3c;">Relatório Contábil do Mês Atual</h2>
                <span style="color:#aaa; font-size:0.8rem;"><?= date('01/m/Y') ?> até <?= date('t/m/Y') ?></span>
            </div>
        </div>
        <button class="btn btn-primary no-print" onclick="window.print()">🖨 Imprimir / PDF</button>
    </div>

    <div style="padding:2rem; display:flex; justify-content:space-around; flex-wrap:wrap; gap:2rem; text-align:center;">
        <div>
            <div
                style="color:#888; font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.5rem;">
                Faturamento Bruto</div>
            <div style="color:#fff; font-size:2.5rem; font-weight:900;">R$
                <?= number_format($faturamento_mes, 2, ',', '.') ?></div>
        </div>
        <div style="width:1px; background:#333;"></div>
        <div>
            <div
                style="color:#888; font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.5rem;">
                Transações Realizadas</div>
            <div style="color:#C0392B; font-size:2.5rem; font-weight:900;"><?= $qtd_transacoes ?></div>
            <div style="color:#555; font-size:0.8rem;">pedidos finalizados</div>
        </div>
    </div>
</div>


<!-- Outros Filtros e Relatórios Gerais (Não entram na impressão nativamente pelo escopo) -->
<div class="no-print">
    <!-- Filtro de Datas -->
    <div class="card" style="margin-bottom:1.5rem;">
        <div style="padding:1rem 1.25rem;">
            <form method="GET" style="display:flex; gap:1rem; align-items:flex-end; flex-wrap:wrap;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Data Inicial</label>
                    <input type="date" name="inicio" class="form-input"
                        value="<?= htmlspecialchars($data_inicio_str) ?>" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Data Final</label>
                    <input type="date" name="fim" class="form-input" value="<?= htmlspecialchars($data_fim_str) ?>"
                        required>
                </div>
                <button type="submit" class="btn btn-primary" style="padding:0.65rem 1.5rem;">Filtrar Período</button>
            </form>
        </div>
    </div>

    <!-- Estatísticas Principais -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">💰 Vendas do Período</div>
            <div class="stat-value" style="font-size:1.4rem;">R$ <?= number_format($total_vendas, 2, ',', '.') ?></div>
            <div class="stat-sub"><?= $qtd_pedidos ?> pedidos finalizados</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">🎫 Ticket Médio</div>
            <div class="stat-value" style="font-size:1.4rem;">R$ <?= number_format($ticket_medio, 2, ',', '.') ?></div>
            <div class="stat-sub">por pedido</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">❌ Cancelamentos</div>
            <div class="stat-value" style="color:#e74c3c; font-size:1.4rem;"><?= $total_cancelados ?></div>
            <div class="stat-sub">pedidos cancelados</div>
        </div>
    </div>

    <!-- Grid: Curva ABC e Histórico de Preços -->
    <div
        style="display:grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap:1.5rem; align-items:start;">

        <!-- Curva ABC -->
        <div class="card">
            <div class="card-header">
                <h2>📈 Curva ABC (Receita Global)</h2>
            </div>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th style="text-align:right;">Unid. Vendidas</th>
                            <th style="text-align:right;">Receita</th>
                            <th style="text-align:center;">Classe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($curva_abc as $row): ?>
                            <tr>
                                <td style="font-weight:600; color:#ddd;"><?= htmlspecialchars($row['nome']) ?></td>
                                <td style="text-align:right; color:#888;"><?= $row['total_unidades'] ?> un.</td>
                                <td style="text-align:right; color:#F0A500; font-weight:700;">
                                    R$ <?= number_format($row['receita_total'], 2, ',', '.') ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php if ($row['classe'] == 'A'): ?>
                                        <span style="color:#2ecc71; font-weight:800; font-size:1.1rem;">A</span>
                                    <?php elseif ($row['classe'] == 'B'): ?>
                                        <span style="color:#F0A500; font-weight:800; font-size:1.1rem;">B</span>
                                    <?php else: ?>
                                        <span style="color:#e74c3c; font-weight:800; font-size:1.1rem;">C</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Histórico de Preços -->
        <div class="card">
            <div class="card-header">
                <h2>📑 Histórico de Preços</h2>
                <span style="font-size:0.75rem; color:#555;">Últimos 100 registros</span>
            </div>
            <div style="max-height:400px; overflow-y:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Produto</th>
                            <th>Alteração</th>
                            <th>Usuário</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico_precos as $log): ?>
                            <tr>
                                <td style="font-size:0.75rem; color:#888; white-space:nowrap;">
                                    <?= date('d/m/y H:i', strtotime($log['alterado_em'])) ?>
                                </td>
                                <td style="font-weight:600; color:#ddd;"><?= htmlspecialchars($log['produto_nome']) ?></td>
                                <td>
                                    <div
                                        style="display:flex; align-items:center; gap:0.4rem; font-size:0.85rem; white-space:nowrap;">
                                        <span style="color:#555;">R$
                                            <?= number_format($log['preco_anterior'], 2, ',', '.') ?></span>
                                        <span style="color:#C0392B;">→</span>
                                        <span style="color:#2ecc71; font-weight:700;">R$
                                            <?= number_format($log['preco_novo'], 2, ',', '.') ?></span>
                                    </div>
                                </td>
                                <td style="color:#555; font-size:0.8rem;">
                                    <?= htmlspecialchars($log['changed_by_nome']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php render_admin_footer(); ?>