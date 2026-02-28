# =============================================================================
# auth.py – Blueprint de Autenticação
# =============================================================================
# Responsável por todo o fluxo de login e logout do painel administrativo.
# Usa Flask-Login para gerenciar a sessão e werkzeug para verificar as senhas.
# =============================================================================

from flask import Blueprint, render_template, redirect, url_for, request, flash
from flask_login import login_user, logout_user, login_required, current_user
from models import db, User

# Cria o "mini-app" de autenticação sob o prefixo /auth
auth_bp = Blueprint('auth', __name__, url_prefix='/auth')


# ─────────────────────────────────────────────────────────────────────────────
# LOGIN  →  GET /auth/login  → exibe formulário
#           POST /auth/login → valida e redireciona
# ─────────────────────────────────────────────────────────────────────────────
@auth_bp.route('/login', methods=['GET', 'POST'])
def login():
    """
    GET:  Exibe a tela de login.
    POST: Valida usuário + senha. Se correto, inicia a sessão e redireciona
          para o painel admin. Se incorreto, exibe mensagem de erro.
    """
    # Se o usuário já está logado, não precisa ver a tela de login
    if current_user.is_authenticated:
        return redirect(url_for('admin.dashboard'))

    if request.method == 'POST':
        username = request.form.get('username', '').strip()
        password = request.form.get('password', '')

        # Busca o usuário no banco pelo nome de usuário
        user = User.query.filter_by(username=username).first()

        # Verifica: usuário existe, está ativo E a senha bate com o hash
        if user and user.is_active and user.check_password(password):
            # remember=True mantém a sessão mesmo após fechar o navegador
            login_user(user, remember=True)
            # 'next' é usado pelo Flask-Login para redirecionar ao destino
            # original quando o usuário tentou acessar uma página protegida
            next_page = request.args.get('next')
            return redirect(next_page or url_for('admin.dashboard'))
        else:
            flash('Usuário ou senha incorretos.', 'error')

    return render_template('login.html')


# ─────────────────────────────────────────────────────────────────────────────
# LOGOUT  →  GET /auth/logout
# ─────────────────────────────────────────────────────────────────────────────
@auth_bp.route('/logout')
@login_required
def logout():
    """Encerra a sessão do usuário e redireciona para a tela de login."""
    logout_user()
    flash('Você saiu do sistema com segurança.', 'info')
    return redirect(url_for('auth.login'))
