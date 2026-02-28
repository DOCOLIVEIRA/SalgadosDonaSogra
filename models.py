# =============================================================================
# models.py – Mapeamento das tabelas do banco de dados (SQLAlchemy)
# =============================================================================
# Cada classe Python aqui representa uma tabela no banco SQLite.
# O SQLAlchemy cuida de criar as tabelas, montar as queries e manter
# os relacionamentos entre elas.
# =============================================================================

from datetime import datetime
from flask_sqlalchemy import SQLAlchemy
from flask_login import UserMixin
from werkzeug.security import generate_password_hash, check_password_hash

# Instância central do banco. Fica aqui para ser importada em outros módulos.
db = SQLAlchemy()


# ─────────────────────────────────────────────────────────────────────────────
# TABELA: User (Usuários do painel administrativo)
# ─────────────────────────────────────────────────────────────────────────────
class User(UserMixin, db.Model):
    """
    Representa um administrador ou funcionário com acesso ao painel.

    UserMixin: herança do Flask-Login que adiciona as propriedades
    is_authenticated, is_active, is_anonymous e get_id() automaticamente.

    Campos:
        id            – Chave primária (inteiro auto-incremento)
        username      – Nome de usuário único (ex: 'admin', 'maria')
        password_hash – Senha NUNCA salva em texto puro, sempre como hash
        role          – 'admin' pode gerenciar usuários; 'staff' só opera
        is_active     – Permite desativar um usuário sem apagá-lo
        created_at    – Data/hora de criação do registro
    """
    __tablename__ = 'users'

    id            = db.Column(db.Integer, primary_key=True)
    username      = db.Column(db.String(64), unique=True, nullable=False, index=True)
    password_hash = db.Column(db.String(256), nullable=False)
    role          = db.Column(db.String(20), nullable=False, default='staff')  # 'admin' ou 'staff'
    is_active     = db.Column(db.Boolean, nullable=False, default=True)
    created_at    = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)

    # Relacionamentos para auditoria (pedidos cancelados por este usuário, preços alterados)
    cancelamentos = db.relationship('Order',    foreign_keys='Order.cancelado_por_id',    backref='cancelado_por_usuario', lazy='dynamic')
    price_logs    = db.relationship('PriceLog', foreign_keys='PriceLog.changed_by_id',    backref='alterado_por_usuario', lazy='dynamic')

    def set_password(self, password: str) -> None:
        """Gera o hash seguro da senha usando werkzeug (bcrypt-like)."""
        self.password_hash = generate_password_hash(password)

    def check_password(self, password: str) -> bool:
        """Verifica se a senha fornecida bate com o hash armazenado."""
        return check_password_hash(self.password_hash, password)

    def __repr__(self):
        return f'<User {self.username} ({self.role})>'


# ─────────────────────────────────────────────────────────────────────────────
# TABELA: Product (Produtos e controle de estoque)
# ─────────────────────────────────────────────────────────────────────────────
class Product(db.Model):
    """
    Representa um salgado do cardápio com seu estoque atual.

    Campos:
        id                 – Chave primária
        slug               – Identificador único legível (ex: 'coxinha-de-frango')
        nome               – Nome exibido ao cliente
        descricao          – Descrição do produto
        preco_unitario     – Preço por UNIDADE em reais (ex: 0.70 = R$ 0,70/un)
        quantidade_estoque – Quantidade atual disponível (decrementada em pedidos)
        imagem             – Caminho relativo da imagem (ex: 'img/coxinha.png')
        ativo              – Produto visível no cardápio
    """
    __tablename__ = 'products'

    id                 = db.Column(db.Integer, primary_key=True)
    slug               = db.Column(db.String(100), unique=True, nullable=False, index=True)
    nome               = db.Column(db.String(150), nullable=False)
    descricao          = db.Column(db.Text, nullable=True)
    preco_unitario     = db.Column(db.Float, nullable=False)
    quantidade_estoque = db.Column(db.Integer, nullable=False, default=0)
    imagem             = db.Column(db.String(200), nullable=True)
    ativo              = db.Column(db.Boolean, nullable=False, default=True)

    # Relacionamentos
    order_items = db.relationship('OrderItem', backref='produto', lazy='dynamic')
    price_logs  = db.relationship('PriceLog',  backref='produto', lazy='dynamic')

    def __repr__(self):
        return f'<Product {self.nome} – R${self.preco_unitario:.2f}/un estoque:{self.quantidade_estoque}>'


# ─────────────────────────────────────────────────────────────────────────────
# TABELA: Order (Pedidos dos clientes)
# ─────────────────────────────────────────────────────────────────────────────
class Order(db.Model):
    """
    Cabeçalho de um pedido realizado pelo cliente.

    Status possíveis:
        'Pendente'   – Pedido recebido, aguardando preparo
        'Em preparo' – Equipe iniciou a produção
        'Pronto'     – Salgados prontos para retirada/entrega
        'Entregue'   – Pedido concluído
        'Cancelado'  – Pedido cancelado (estoque já foi estornado)

    Campos de auditoria:
        cancelado_por_id    – FK para o User que cancelou (None se não cancelado)
        cancelado_em        – Timestamp do cancelamento
    """
    __tablename__ = 'orders'

    STATUSES = ['Pendente', 'Em preparo', 'Pronto', 'Entregue', 'Cancelado']

    id                  = db.Column(db.Integer, primary_key=True)
    cliente_nome        = db.Column(db.String(150), nullable=False)
    cliente_tel         = db.Column(db.String(30),  nullable=True)
    total               = db.Column(db.Float, nullable=False, default=0.0)
    status              = db.Column(db.String(30), nullable=False, default='Pendente')
    created_at          = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    # Auditoria de cancelamento
    cancelado_por_id    = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=True)
    cancelado_em        = db.Column(db.DateTime, nullable=True)

    # Um pedido tem vários itens
    itens = db.relationship('OrderItem', backref='pedido', lazy='joined', cascade='all, delete-orphan')

    def __repr__(self):
        return f'<Order #{self.id} – {self.cliente_nome} – {self.status}>'


# ─────────────────────────────────────────────────────────────────────────────
# TABELA: OrderItem (Itens de cada pedido)
# ─────────────────────────────────────────────────────────────────────────────
class OrderItem(db.Model):
    """
    Representa um produto dentro de um pedido.

    O campo `preco_unitario_snapshot` guarda o preço no momento da compra.
    Isso é fundamental: se o preço do produto mudar depois, o valor do
    pedido histórico permanece correto.

    Campos:
        order_id                 – FK para Order
        product_id               – FK para Product
        quantidade               – Quantidade pedida (em unidades)
        preco_unitario_snapshot  – Preço no momento do pedido (snapshot)
    """
    __tablename__ = 'order_items'

    id                      = db.Column(db.Integer, primary_key=True)
    order_id                = db.Column(db.Integer, db.ForeignKey('orders.id'),   nullable=False)
    product_id              = db.Column(db.Integer, db.ForeignKey('products.id'), nullable=False)
    quantidade              = db.Column(db.Integer, nullable=False)
    preco_unitario_snapshot = db.Column(db.Float,   nullable=False)

    @property
    def subtotal(self) -> float:
        """Calcula o subtotal deste item: quantidade × preço snapshot."""
        return self.quantidade * self.preco_unitario_snapshot

    def __repr__(self):
        return f'<OrderItem pedido#{self.order_id} produto#{self.product_id} qty:{self.quantidade}>'


# ─────────────────────────────────────────────────────────────────────────────
# TABELA: PriceLog (Histórico de alterações de preço)
# ─────────────────────────────────────────────────────────────────────────────
class PriceLog(db.Model):
    """
    Registra cada alteração de preço de um produto.

    Toda vez que um administrador altera o preço, uma linha aqui é criada.
    Isso forma o histórico completo de precificação para auditoria.

    Campos:
        product_id    – FK para Product
        preco_anterior – Preço antes da alteração
        preco_novo     – Preço após a alteração
        changed_by_id  – FK para o User que fez a alteração
        changed_at     – Timestamp da alteração
    """
    __tablename__ = 'price_logs'

    id             = db.Column(db.Integer, primary_key=True)
    product_id     = db.Column(db.Integer, db.ForeignKey('products.id'), nullable=False)
    preco_anterior = db.Column(db.Float, nullable=False)
    preco_novo     = db.Column(db.Float, nullable=False)
    changed_by_id  = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    changed_at     = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)

    def __repr__(self):
        return f'<PriceLog prod#{self.product_id}: R${self.preco_anterior:.2f}→R${self.preco_novo:.2f}>'
