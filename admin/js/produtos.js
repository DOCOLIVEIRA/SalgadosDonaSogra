// Arquivo JS para a tela de produtos
// Comentários em todas as funções para facilitar a leitura

/**
 * Função para abrir o modal de alteração de preço para um produto específico.
 * @param {number} productId - ID do produto.
 * @param {string} nome - Nome do produto.
 * @param {number} precoAtual - Preço atual do produto.
 */
function abrirModalPreco(productId, nome, precoAtual) {
    document.getElementById('precoModalDesc').textContent = `Produto: ${nome} | Preço atual: R$ ${precoAtual.toFixed(2)}/un. (R$ ${(precoAtual * 100).toFixed(2)}/cento)`;
    document.getElementById('novoPrecoInput').value = precoAtual.toFixed(2);
    document.getElementById('precoCento').textContent = (precoAtual * 100).toFixed(2);
    document.getElementById('modalProductId').value = productId;
    document.getElementById('precoModal').classList.add('open');
}

/**
 * Função para fechar o modal de preço.
 */
function fecharModalPreco() {
    document.getElementById('precoModal').classList.remove('open');
}

// Event listeners que devem ser adicionados após o DOM carregar
document.addEventListener('DOMContentLoaded', function () {

    /**
     * Atualiza o valor do preço por cento conforme o usuário digita o novo preço.
     */
    const novoPrecoInput = document.getElementById('novoPrecoInput');
    if (novoPrecoInput) {
        novoPrecoInput.addEventListener('input', function () {
            const v = parseFloat(this.value) || 0;
            document.getElementById('precoCento').textContent = (v * 100).toFixed(2);
        });
    }

    /**
     * Habilita fechar o modal clicando fora dele.
     */
    const precoModal = document.getElementById('precoModal');
    if (precoModal) {
        precoModal.addEventListener('click', function (e) {
            if (e.target === this) fecharModalPreco();
        });
    }
});
