# =============================================================================
# admin.py – Blueprint do Painel Administrativo
# =============================================================================
# Todas as rotas aqui exigem login (@login_required).
# O cancelamento de pedido usa TRANSAÇÃO ATÔMICA: ou cancela E estorna o
# estoque, ou nenhum dos dois acontece. Isso evita inconsistências no banco.
# =============================================================================

from datetime import datetime, timedelta
from flask import Blueprint, render_template, redirect, url_for, request, flash, jsonify
from flask_login import login_required, current_user
from sqlalchemy import func
from models import db, User, Product, Order, OrderItem, PriceLog

# Cria o "mini-app" do painel sob o prefixo /admin
admin_bp = Blueprint('admin', __name__, url_prefix='/admin')


# ─────────────────────────────────────────────────────────────────────────────
# HELPER: verificar se é admin
# ─────────────────────────────────────────────────────────────────────────────
def apenas_admin():
    """Retorna True se o usuário logado tiver role 'admin'."""
    return current_user.is_authenticated and current_user.role == 'admin'


# ─────────────────────────────────────────────────────────────────────────────
# DASHBOARD  →  GET /admin/
# ─────────────────────────────────────────────────────────────────────────────
@admin_bp.route('/')
@login_required
def dashboard():
    """
    Página inicial do painel.
    Exibe os 50 pedidos mais recentes com ID, Data, Cliente, Total e Status.
    """
    pedidos = (Order.query
               .order_by(Order.created_at.desc())
               .limit(50)
               .all())

    # Resumo rápido para os cards do topo
    total_pendentes  = Order.query.filter_by(status='Pendente').count()
    total_hoje = (Order.query
                  .filter(Order.created_at >= datetime.utcnow().date())
                  .filter(Order.status != 'Cancelado')
                  .count())
    faturamento_hoje = (db.session.query(func.sum(Order.total))
                        .filter(Order.created_at >= datetime.utcnow().date())
                        .filter(Order.status != 'Cancelado')
                        .scalar() or 0.0)

    return render_template(
        'admin/dashboard.html',
        pedidos=pedidos,
        total_pendentes=total_pendentes,
        total_hoje=total_hoje,
        faturamento_hoje=faturamento_hoje,
    )


# ─────────────────────────────────────────────────────────────────────────────
# CANCELAR PEDIDO  →  POST /admin/pedidos/<id>/cancelar
# ─────────────────────────────────────────────────────────────────────────────
@admin_bp.route('/pedidos/<int:order_id>/cancelar', methods=['POST'])
@login_required
def cancelar_pedido(order_id):
    """
    LÓGICA NUCLEAR DO SISTEMA – TRANSAÇÃO ATÔMICA.

    O que acontece aqui:
    1. Busca o pedido pelo ID (erro 404 se não existir)
    2. Verifica se já não está cancelado
    3. Dentro de uma única transação do banco:
       a. Muda o status para 'Cancelado'
       b. Registra quem cancelou e quando (auditoria)
       c. Para cada item do pedido, DEVOLVE a quantidade ao estoque do produto
    4. Se QUALQUER etapa falhar → rollback automático (tudo volta ao estado anterior)
    5. Se tudo der certo → commit (salva tudo junto de uma vez)

    Isso garante que nunca haverá um pedido cancelado com estoque não estornado
    ou um estoque estornado com pedido ainda como 'Pendente'.
    """
    pedido = Order.query.get_or_404(order_id)

    if pedido.status == 'Cancelado':
        flash(f'Pedido #{order_id} já estava cancelado.', 'warning')
        return redirect(url_for('admin.dashboard'))

    try:
        # ── INÍCIO DA TRANSAÇÃO ATÔMICA ──────────────────────────────────────
        # 1. Marca o pedido como cancelado
        pedido.status           = 'Cancelado'
        pedido.cancelado_por_id = current_user.id
        pedido.cancelado_em     = datetime.utcnow()

        # 2. Estorna o estoque: para CADA item, devolvemos a quantidade
        for item in pedido.itens:
            produto = Product.query.get(item.product_id)
            if produto:
                produto.quantidade_estoque += item.quantidade
                # Log opcional: mostra o que foi estornado
                # ex: "Estornados 50 unidades de Coxinha de Frango"

        # 3. Salva tudo numa única operação (commit)
        db.session.commit()
        # ── FIM DA TRANSAÇÃO ATÔMICA ─────────────────────────────────────────

        flash(
            f'✅ Pedido #{order_id} cancelado com sucesso. '
            f'Estoque de {len(pedido.itens)} produto(s) foi restaurado.',
            'success'
        )

    except Exception as e:
        # Se qualquer coisa falhar, desfaz TUDO (rollback)
        db.session.rollback()
        flash(f'❌ Erro ao cancelar pedido #{order_id}: {str(e)}', 'error')

    return redirect(url_for('admin.dashboard'))


# ─────────────────────────────────────────────────────────────────────────────
# ATUALIZAR STATUS  →  POST /admin/pedidos/<id>/status
# ─────────────────────────────────────────────────────────────────────────────
@admin_bp.route('/pedidos/<int:order_id>/status', methods=['POST'])
@login_required
def atualizar_status(order_id):
    """Atualiza o status de um pedido (ex: Pendente → Em preparo → Pronto)."""
    pedido = Order.query.get_or_404(order_id)
    novo_status = request.form.get('status')

    if novo_status not in Order.STATUSES or novo_status == 'Cancelado':
        flash('Status inválido.', 'error')
        return redirect(url_for('admin.dashboard'))

    if pedido.status == 'Cancelado':
        flash('Pedido cancelado não pode ser reativado.', 'warning')
        return redirect(url_for('admin.dashboard'))

    pedido.status = novo_status
    db.session.commit()
    flash(f'Status do pedido #{order_id} atualizado para "{novo_status}".', 'success')
    return redirect(url_for('admin.dashboard'))


# ─────────────────────────────────────────────────────────────────────────────
# PRODUTOS  →  GET /admin/produtos
# ─────────────────────────────────────────────────────────────────────────────
@admin_bp.route('/produtos')
@login_required
def produtos():
    """Lista todos os produtos com estoque atual e histórico de preços."""
    lista = Product.query.order_by(Product.nome).all()
    return render_template('admin/produtos.html', produtos=lista)


# ─────────────────────────────────────────────────────────────────────────────
# ATUALIZAR ESTOQUE  →  POST /admin/produtos/<id>/estoque
# ─────────────────────────────────────────────────────────────────────────────
@admin_bp.route('/produtos/<int:product_id>/estoque', methods=['POST'])
@login_required
def atualizar_estoque(product_id):
    """Ajusta manualmente o estoque de um produto."""
    produto = Product.query.get_or_404(product_id)
    nova_qtd = request.form.get('quantidade', type=int)

    if nova_qtd is None or nova_qtd < 0:
        flash('Quantidade inválida.', 'error')
        return redirect(url_for('admin.produtos'))

    produto.quantidade_estoque = nova_qtd
    db.session.commit()
    flash(f'Estoque de "{produto.nome}" atualizado para {nova_qtd} unidades.', 'success')
    return redirect(url_for('admin.produtos'))


# ─────────────────────────────────────────────────────────────────────────────
# ALTERAR PREÇO (com dupla confirmação + PriceLog)
# POST /admin/produtos/<id>/preco
# ─────────────────────────────────────────────────────────────────────────────
@admin_bp.route('/produtos/<int:product_id>/preco', methods=['POST'])
@login_required
def alterar_preco(product_id):
    """
    Altera o preço de um produto e registra no histórico (PriceLog).

    Dupla confirmação: o formulário deve enviar o campo 'confirmado=sim'
    (o template mostra um modal de confirmação antes de submeter).
    """
    produto = Product.query.get_or_404(product_id)
    novo_preco  = request.form.get('novo_preco', type=float)
    confirmado  = request.form.get('confirmado', '')

    if novo_preco is None or novo_preco <= 0:
        flash('Preço inválido.', 'error')
        return redirect(url_for('admin.produtos'))

    if confirmado != 'sim':
        flash('Alteração de preço requer confirmação.', 'warning')
        return redirect(url_for('admin.produtos'))

    try:
        # Registra o log ANTES de alterar (captura o preço anterior)
        log = PriceLog(
            product_id     = produto.id,
            preco_anterior = produto.preco_unitario,
            preco_novo     = novo_preco,
            changed_by_id  = current_user.id,
        )
        db.session.add(log)

        # Agora atualiza o preço do produto
        produto.preco_unitario = novo_preco
        db.session.commit()

        flash(
            f'Preço de "{produto.nome}" alterado de '
            f'R${log.preco_anterior:.2f} para R${novo_preco:.2f}.',
            'success'
        )
    except Exception as e:
        db.session.rollback()
        flash(f'Erro ao alterar preço: {str(e)}', 'error')

    return redirect(url_for('admin.produtos'))


# ─────────────────────────────────────────────────────────────────────────────
# USUÁRIOS  →  GET/POST /admin/usuarios   (apenas admin)
# ─────────────────────────────────────────────────────────────────────────────
@admin_bp.route('/usuarios', methods=['GET', 'POST'])
@login_required
def usuarios():
    """
    GET:  Lista usuários do sistema.
    POST: Cria um novo usuário (apenas administradores podem fazer isso).
    """
    if not apenas_admin():
        flash('Acesso restrito a administradores.', 'error')
        return redirect(url_for('admin.dashboard'))

    if request.method == 'POST':
        username = request.form.get('username', '').strip()
        password = request.form.get('password', '')
        role     = request.form.get('role', 'staff')

        if not username or not password:
            flash('Usuário e senha são obrigatórios.', 'error')
        elif User.query.filter_by(username=username).first():
            flash(f'Usuário "{username}" já existe.', 'error')
        else:
            novo = User(username=username, role=role)
            novo.set_password(password)
            db.session.add(novo)
            db.session.commit()
            flash(f'Usuário "{username}" criado com sucesso!', 'success')

    lista = User.query.order_by(User.created_at.desc()).all()
    return render_template('admin/usuarios.html', usuarios=lista)


# ─────────────────────────────────────────────────────────────────────────────
# TOGGLE ATIVO/INATIVO  →  POST /admin/usuarios/<id>/toggle
# ─────────────────────────────────────────────────────────────────────────────
@admin_bp.route('/usuarios/<int:user_id>/toggle', methods=['POST'])
@login_required
def toggle_usuario(user_id):
    """Ativa ou desativa um usuário sem apagá-lo do banco."""
    if not apenas_admin():
        flash('Acesso restrito a administradores.', 'error')
        return redirect(url_for('admin.dashboard'))

    usuario = User.query.get_or_404(user_id)

    if usuario.id == current_user.id:
        flash('Você não pode desativar sua própria conta.', 'warning')
        return redirect(url_for('admin.usuarios'))

    usuario.is_active = not usuario.is_active
    db.session.commit()
    estado = 'ativado' if usuario.is_active else 'desativado'
    flash(f'Usuário "{usuario.username}" foi {estado}.', 'success')
    return redirect(url_for('admin.usuarios'))


# ─────────────────────────────────────────────────────────────────────────────
# RELATÓRIOS  →  GET /admin/relatorios
# ─────────────────────────────────────────────────────────────────────────────
@admin_bp.route('/relatorios')
@login_required
def relatorios():
    """
    Centraliza três tipos de inteligência de negócio:

    1. FINANCEIRO: total de vendas por período (filtro por data)
    2. CURVA ABC:  produtos ranqueados por receita total gerada
       - A (top 20% da receita) → itens que mais faturam
       - B (próximos 30%)       → itens de desempenho médio
       - C (últimos 50%)        → itens de baixo impacto financeiro
    3. HISTÓRICO DE PREÇOS: log completo de alterações com responsável
    """
    # ── Parâmetros do filtro de data ─────────────────────────────────────────
    hoje = datetime.utcnow().date()
    data_inicio_str = request.args.get('inicio', (hoje - timedelta(days=30)).isoformat())
    data_fim_str    = request.args.get('fim',    hoje.isoformat())

    try:
        data_inicio = datetime.fromisoformat(data_inicio_str)
        data_fim    = datetime.fromisoformat(data_fim_str) + timedelta(days=1)
    except ValueError:
        data_inicio = datetime.utcnow() - timedelta(days=30)
        data_fim    = datetime.utcnow() + timedelta(days=1)

    # ── 1. FINANCEIRO ────────────────────────────────────────────────────────
    q_financeiro = (Order.query
                    .filter(Order.created_at >= data_inicio)
                    .filter(Order.created_at < data_fim)
                    .filter(Order.status != 'Cancelado'))

    pedidos_periodo    = q_financeiro.all()
    total_vendas       = sum(p.total for p in pedidos_periodo)
    qtd_pedidos        = len(pedidos_periodo)
    ticket_medio       = (total_vendas / qtd_pedidos) if qtd_pedidos > 0 else 0.0
    total_cancelados   = (Order.query
                          .filter(Order.created_at >= data_inicio)
                          .filter(Order.created_at < data_fim)
                          .filter(Order.status == 'Cancelado').count())

    # ── 2. CURVA ABC ─────────────────────────────────────────────────────────
    # Agrupa OrderItems pelo produto e soma a receita (qty × preco_snapshot)
    abc_raw = (db.session.query(
                    Product.id,
                    Product.nome,
                    func.sum(OrderItem.quantidade).label('total_unidades'),
                    func.sum(OrderItem.quantidade * OrderItem.preco_unitario_snapshot).label('receita_total')
               )
               .join(OrderItem, OrderItem.product_id == Product.id)
               .join(Order, Order.id == OrderItem.order_id)
               .filter(Order.status != 'Cancelado')
               .group_by(Product.id, Product.nome)
               .order_by(func.sum(OrderItem.quantidade * OrderItem.preco_unitario_snapshot).desc())
               .all())

    # Calcula receita acumulada para classificar A/B/C
    receita_total_geral = sum(r.receita_total for r in abc_raw) or 1  # evita divisão por zero
    acumulado = 0
    curva_abc = []
    for row in abc_raw:
        acumulado += row.receita_total
        pct_acumulado = acumulado / receita_total_geral
        if pct_acumulado <= 0.70:
            classe = 'A'
        elif pct_acumulado <= 0.90:
            classe = 'B'
        else:
            classe = 'C'
        curva_abc.append({
            'nome':          row.nome,
            'total_unidades': row.total_unidades,
            'receita_total':  row.receita_total,
            'classe':         classe,
        })

    # ── 3. HISTÓRICO DE PREÇOS ───────────────────────────────────────────────
    historico_precos = (PriceLog.query
                        .order_by(PriceLog.changed_at.desc())
                        .limit(100)
                        .all())

    return render_template(
        'admin/relatorios.html',
        # Financeiro
        total_vendas=total_vendas,
        qtd_pedidos=qtd_pedidos,
        ticket_medio=ticket_medio,
        total_cancelados=total_cancelados,
        data_inicio_str=data_inicio_str,
        data_fim_str=data_fim_str,
        # Curva ABC
        curva_abc=curva_abc,
        # Histórico
        historico_precos=historico_precos,
    )
