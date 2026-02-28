from flask import Flask, render_template

# Inicializa o aplicativo
app = Flask(__name__)

# Rota principal (A vitrine da loja de salgados)
@app.route('/')
def home():
    # Aqui o Python procura o arquivo HTML que o agente de frontend gerou
    return render_template('index.html')

# Rota de checkout (Onde o pedido é finalizado)
@app.route('/checkout')
def checkout():
    return render_template('checkout.html')

if __name__ == '__main__':
    # Roda o servidor em modo de desenvolvimento (atualiza sozinho se você mudar o código)
    app.run(debug=True)