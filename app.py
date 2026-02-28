# =============================================================================
# app.py – Servidor Principal Flask (Salgados Dona Sogra)
# =============================================================================
# Ponto de entrada da aplicação. Aqui configuramos o banco de dados,
# o sistema de login, registramos os blueprints e definimos os comandos CLI.
# =============================================================================

import os
import click
from flask import Flask, render_template, send_from_directory
from flask_login import LoginManager

from models import db, User, Product

# ─────────────────────────────────────────────────────────────────────────────
# CRIAÇÃO DO APP
# ─────────────────────────────────────────────────────────────────────────────
app = Flask(__name__, template_folder='templates', static_folder='static')

# ── Configurações de segurança e banco ───────────────────────────────────────
# SECRET_KEY: usada para assinar os cookies de sessão.
# Em produção, troque por uma string longa e aleatória e guarde em variável de ambiente.
app.config['SECRET_KEY'] = os.environ.get('SECRET_KEY', 'dona-sogra-chave-secreta-2025')

# Banco de dados SQLite local (arquivo salgados.db na raiz do projeto)
app.config['SQLALCHEMY_DATABASE_URI'] = os.environ.get(
    'DATABASE_URL',
    'sqlite:///salgados.db'
)
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False  # Desativa overhead desnecessário

# ─────────────────────────────────────────────────────────────────────────────
# INICIALIZAÇÃO DAS EXTENSÕES
# ─────────────────────────────────────────────────────────────────────────────
db.init_app(app)

# Flask-Login: gerencia autenticação e sessões
login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = 'auth.login'          # Redireciona para login se não autenticado
login_manager.login_message = 'Por favor, faça login para acessar o painel.'
login_manager.login_message_category = 'warning'


@login_manager.user_loader
def load_user(user_id: str):
    """
    Callback obrigatório do Flask-Login.
    A cada requisição, o Flask-Login chama esta função para carregar
    o usuário a partir do ID armazenado no cookie de sessão.
    """
    return User.query.get(int(user_id))


# ─────────────────────────────────────────────────────────────────────────────
# REGISTRO DOS BLUEPRINTS
# ─────────────────────────────────────────────────────────────────────────────
from auth import auth_bp
from admin import admin_bp

app.register_blueprint(auth_bp)   # /auth/login, /auth/logout
app.register_blueprint(admin_bp)  # /admin/, /admin/produtos, etc.


# ─────────────────────────────────────────────────────────────────────────────
# ROTAS DO FRONTEND (servem os HTMLs estáticos existentes)
# ─────────────────────────────────────────────────────────────────────────────
@app.route('/')
def home():
    """Página principal do site (vitrine dos salgados)."""
    return send_from_directory('.', 'index.html')


@app.route('/cart.html')
def cart():
    """Página do carrinho de compras."""
    return send_from_directory('.', 'cart.html')


# ─────────────────────────────────────────────────────────────────────────────
# COMANDO CLI: flask init-db
# ─────────────────────────────────────────────────────────────────────────────
@app.cli.command('init-db')
def init_db():
    """
    Cria todas as tabelas no banco de dados e popula com dados iniciais.

    Uso:
        flask init-db

    O que faz:
    1. Cria o arquivo salgados.db (ou atualiza o schema)
    2. Cria o usuário administrador padrão (admin/admin)
    3. Cadastra os 9 produtos do cardápio com estoque inicial
    """
    with app.app_context():
        db.create_all()
        click.echo('✅ Tabelas criadas com sucesso.')

        # Cria usuário admin somente se não existir
        if not User.query.filter_by(username='admin').first():
            admin = User(username='admin', role='admin')
            admin.set_password('admin')
            db.session.add(admin)
            db.session.commit()
            click.echo('✅ Usuário admin criado (senha: admin). TROQUE A SENHA em produção!')
        else:
            click.echo('ℹ️  Usuário admin já existe, pulando criação.')

        # Popula produtos do cardápio (só se o banco estiver vazio)
        if Product.query.count() == 0:
            produtos_iniciais = [
                Product(slug='coxinha-de-frango',          nome='Coxinha de Frango',                 descricao='Massa crocante, recheio de frango desfiado temperado.',                           preco_unitario=0.70, quantidade_estoque=500, imagem='img/coxinha.png'),
                Product(slug='coxinha-de-carne',           nome='Coxinha de Carne',                  descricao='Coxinha frita com recheio de carne moída temperada.',                             preco_unitario=0.85, quantidade_estoque=500, imagem='img/coxinha_de_carne.png'),
                Product(slug='kibe',                       nome='Kibe',                              descricao='Kibe tradicional, crocante por fora e suculento por dentro.',                    preco_unitario=0.70, quantidade_estoque=500, imagem='img/kibe.png'),
                Product(slug='kibe-com-queijo',            nome='Kibolinha',                         descricao='Kibe com queijo, crocante por fora com queijo derretido por dentro.',             preco_unitario=0.85, quantidade_estoque=500, imagem='img/kibolinha.png'),
                Product(slug='fataya',                     nome='Fataya',                            descricao='Massa com recheio cremoso de carne moída temperada.',                             preco_unitario=1.10, quantidade_estoque=500, imagem='img/fataya.png'),
                Product(slug='croquete-de-salsicha',       nome='Croquete de Salsicha',              descricao='Crocante por fora com recheio cremoso de salsicha por dentro.',                  preco_unitario=0.70, quantidade_estoque=500, imagem='img/croquete_de_salsicha.png'),
                Product(slug='bolinha-de-queijo',          nome='Bolinha de Queijo',                 descricao='Bolinhas crocantes com mozzarella derretida por dentro.',                        preco_unitario=0.80, quantidade_estoque=500, imagem='img/bolinha_queijo.png'),
                Product(slug='bolinho-de-bacalhau',        nome='Bolinho de Bacalhau',               descricao='Crocante por fora com recheio cremoso de bacalhau por dentro.',                  preco_unitario=1.00, quantidade_estoque=500, imagem='img/bolinho_de_bacalhau.png'),
                Product(slug='almofadinha-calabresa-queijo', nome='Almofadinha de Calabresa e Queijo', descricao='Crocante por fora com recheio cremoso de calabresa e queijo por dentro.',       preco_unitario=0.80, quantidade_estoque=500, imagem='img/almofadinha_calabresa_e_queijo.png'),
            ]
            db.session.add_all(produtos_iniciais)
            db.session.commit()
            click.echo(f'✅ {len(produtos_iniciais)} produtos cadastrados com estoque inicial de 500 unidades cada.')
        else:
            click.echo(f'ℹ️  Produtos já cadastrados ({Product.query.count()} no banco), pulando seed.')

        click.echo('\n🎉 Banco de dados pronto! Rode: flask run')


# ─────────────────────────────────────────────────────────────────────────────
# COMANDO CLI: flask seed-test (cria pedido de teste para o dashboard)
# ─────────────────────────────────────────────────────────────────────────────
@app.cli.command('seed-test')
def seed_test():
    """
    Cria um pedido de teste no banco para verificar o dashboard e cancelamento.

    Uso:
        flask seed-test
    """
    with app.app_context():
        from models import Order, OrderItem
        from datetime import datetime

        produtos = Product.query.limit(3).all()
        if not produtos:
            click.echo('❌ Rode primeiro: flask init-db')
            return

        pedido = Order(
            cliente_nome='Cliente Teste',
            cliente_tel='(14) 99999-9999',
            status='Pendente',
            total=0.0,
        )
        db.session.add(pedido)
        db.session.flush()  # Gera o ID do pedido sem commitar

        total = 0.0
        for p in produtos:
            qty = 50
            item = OrderItem(
                order_id=pedido.id,
                product_id=p.id,
                quantidade=qty,
                preco_unitario_snapshot=p.preco_unitario,
            )
            db.session.add(item)
            # Desconta do estoque (simula um pedido real)
            p.quantidade_estoque -= qty
            total += qty * p.preco_unitario

        pedido.total = total
        db.session.commit()
        click.echo(f'✅ Pedido #{pedido.id} de teste criado! Total: R${total:.2f}')


# ─────────────────────────────────────────────────────────────────────────────
# PONTO DE ENTRADA
# ─────────────────────────────────────────────────────────────────────────────
if __name__ == '__main__':
    app.run(debug=True)