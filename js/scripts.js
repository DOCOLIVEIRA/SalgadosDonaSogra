/* ─────────────────────────────────────────────────────────
   DADOS DOS PRODUTOS
   preco = valor por UNIDADE (R$80,00 / 100 = R$0,80)
───────────────────────────────────────────────────────── */
const PRODUTOS = [
    { id: 'coxinha de frango', nome: 'Coxinha de Frango', desc: 'Massa crocante, recheio de frango desfiado temperado.', preco: 0.70, img: 'img/coxinha.png' },
    { id: 'coxinha de carne', nome: 'Coxinha de Carne', desc: 'Coxinha frita com recheio de carne moída temperada.', preco: 0.85, img: 'img/coxinha_de_carne.png' },
    { id: 'kibe', nome: 'Kibe', desc: 'Kibe tradicional, crocante por fora e suculento por dentro.', preco: 0.70, img: 'img/kibe.png' },
    { id: 'kibe com queijo', nome: 'Kibolinha', desc: 'Kibe com queijo, crocante por fora com queijo derretido por dentro.', preco: 0.85, img: 'img/kibolinha.png' },
    { id: 'fataya', nome: 'Fataya', desc: 'Massa com recheio cremoso de carne moída temperada.', preco: 1.10, img: 'img/fataya.png' },
    { id: 'croquete de salsicha', nome: 'Croquete de Salsicha', desc: 'Crocante por fora com recheio cremoso de salsicha por dentro.', preco: 0.70, img: 'img/croquete_de_salsicha.png' },
    { id: 'bolinha de queijo', nome: 'Bolinha de Queijo', desc: 'Bolinhas crocantes com mozzarella derretida por dentro.', preco: 0.80, img: 'img/bolinha_queijo.png' },
    { id: 'bolinho de bacalhau', nome: 'Bolinho de Bacalhau', desc: 'Crocante por fora com recheio cremoso de bacalhau por dentro.', preco: 1.00, img: 'img/bolinho_de_bacalhau.png' },
    { id: 'almofadinha de calabresa e queijo', nome: 'Almofadinha de Calabresa e Queijo', desc: 'Crocante por fora com recheio cremoso de calabresa e queijo por dentro.', preco: 0.80, img: 'img/almofadinha_calabresa_e_queijo.png' },
];

/* ─────────────────────────────────────────────────────────
   CONSTANTES
───────────────────────────────────────────────────────── */
const MIN_QTY = 25;
const WHATSAPP_NUMBER = '5514996748488'; // ← Altere aqui

/* ─────────────────────────────────────────────────────────
   ESTADO DO CARRINHO – armazenado no localStorage
───────────────────────────────────────────────────────── */
let carrinho = JSON.parse(localStorage.getItem('ds_carrinho') || '[]');

/**
 * Salva o estado atual do carrinho no LocalStorage e atualiza os badges visuais.
 */
function salvarCarrinho() {
    localStorage.setItem('ds_carrinho', JSON.stringify(carrinho));
    atualizarBadges();
}

/**
 * Atualiza os contadores (badges) nos botões de carrinho (header e flutuante).
 */
function atualizarBadges() {
    const totalItens = carrinho.reduce((sum, i) => sum + i.qty, 0);
    ['cart-badge', 'cart-fab-badge'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (totalItens > 0) {
            el.textContent = totalItens;
            el.classList.remove('hidden');
            el.classList.add('flex');
        } else {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
    });
}

/**
 * Retorna o valor em formato de moeda (Real Brasileiro).
 * @param {number} val Valor numérico a ser formatado.
 * @returns {string} Valor convertido em String de moeda.
 */
function moeda(val) {
    return val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

/**
 * Calcula o custo total (Soma de preço * quantidade) de todos os itens no carrinho.
 * @returns {number} Valor total.
 */
function calcularTotais() {
    return carrinho.reduce((acc, item) => acc + item.preco * item.qty, 0);
}

/* ─────────────────────────────────────────────────────────
   TOAST DE NOTIFICAÇÃO (INDEX)
───────────────────────────────────────────────────────── */
/**
 * Exibe um alerta visual temporário.
 * @param {string} msg Mensagem a ser exibida.
 * @param {string} tipo Tipo de toast (warn ou ok).
 */
function mostrarToast(msg, tipo = 'ok') {
    const existente = document.getElementById('toast');
    if (existente) existente.remove();

    const toast = document.createElement('div');
    toast.id = 'toast';
    const bg = tipo === 'warn' ? 'bg-yellow-500' : 'bg-green-600';
    toast.className = `fixed bottom-24 sm:bottom-8 left-1/2 -translate-x-1/2 z-[999] ${bg} text-white text-sm font-semibold px-5 py-3 rounded-full shadow-xl transition-all`;
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2500);
}

/* ─────────────────────────────────────────────────────────
   NAVEGAÇÃO
───────────────────────────────────────────────────────── */
/**
 * Redireciona o usuário para a página do carrinho (Somente se o mesmo não estiver vazio).
 */
function irParaCarrinho() {
    if (carrinho.length === 0) {
        mostrarToast('🛒 Seu carrinho está vazio!', 'warn');
        return;
    }
    window.location.href = 'cart.html';
}

/* =========================================================
   FUNÇÕES ESPECÍFICAS DA PÁGINA INDEX (Dashboard da Loja)
   ========================================================= */

/**
 * Adiciona uma determinada quantidade de um produto da lista visual ao carrinho logico.
 * Valida a quantidade mínima baseando-se no constante MIN_QTY.
 * @param {string} produtoId ID único do produto vindo de PRODUTOS.
 */
function adicionarAoCarrinho(produtoId) {
    const qtyInput = document.getElementById('qty-' + produtoId);
    let qty = parseInt(qtyInput.value, 10);

    if (isNaN(qty) || qty < MIN_QTY) {
        qtyInput.value = MIN_QTY;
        qtyInput.classList.add('qty-warn', 'border-red-500', 'ring-2', 'ring-red-400');
        setTimeout(() => qtyInput.classList.remove('qty-warn', 'border-red-500', 'ring-2', 'ring-red-400'), 800);
        mostrarToast(`⚠️ Mínimo de ${MIN_QTY} unidades por sabor!`, 'warn');
        return;
    }

    const produto = PRODUTOS.find(p => p.id === produtoId);
    const existente = carrinho.find(i => i.id === produtoId);

    if (existente) {
        existente.qty += qty;
    } else {
        carrinho.push({ ...produto, qty });
    }

    salvarCarrinho();
    mostrarToast(`✅ ${produto.nome} adicionado! (${qty} unid.)`);
}

/**
 * Modifica o valor do input de quantidade de um card específico (Index page)
 * @param {string} produtoId Id do produto
 * @param {number} delta Valor a incrementar/decrementar
 */
function alterarQty(produtoId, delta) {
    const input = document.getElementById('qty-' + produtoId);
    let val = parseInt(input.value, 10) + delta;

    if (val < MIN_QTY) val = MIN_QTY;

    input.value = val;
}

/**
 * Injeta via JS os produtos contidos na constante PRODUTOS na malha de exibição HTML.
 */
function renderizarProdutos() {
    const grid = document.getElementById('product-grid');
    if (!grid) return;

    grid.innerHTML = PRODUTOS.map(p => {
        const precoCentoFmt = (p.preco * 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        const precoMinFmt = (p.preco * 25).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        return `
<article class="product-card bg-white rounded-2xl overflow-hidden shadow-md flex flex-col" id="card-${p.id}">
<!-- Imagem do produto -->
<div class="relative overflow-hidden h-52 bg-gray-100">
<img src="${p.img}" alt="${p.nome}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" loading="lazy" />
<span class="absolute top-3 left-3 bg-brand-red text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wide">Artesanal</span>
</div>

<!-- Conteúdo do card -->
<div class="p-5 flex flex-col flex-1">
<h3 class="font-black text-lg text-brand-dark leading-tight">${p.nome}</h3>
<p class="text-gray-500 text-sm mt-1 flex-1">${p.desc}</p>

<!-- Preços (modelo: R$80,00 por cento) -->
<div class="mt-4 flex items-end justify-between">
  <div>
    <p class="text-gray-400 text-xs">Preço por cento (100 un.)</p>
    <p class="text-brand-red font-black text-2xl">${precoCentoFmt}<span class="text-sm font-normal text-gray-400"> /cento</span></p>
    <p class="text-gray-400 text-xs mt-0.5">25 un. = <strong class="text-gray-600">${precoMinFmt}</strong></p>
  </div>
</div>

<!-- CONTROLE DE QUANTIDADE (mínimo = 25) -->
<div class="mt-4">
  <label class="text-xs text-gray-500 font-semibold mb-1 block">Quantidade <span class="text-brand-red">(mín. 25)</span></label>
  <div class="flex items-center gap-2">
    <!-- Botão "–": usa alterarQty que respeita MIN_QTY -->
    <button onclick="alterarQty('${p.id}', -25)" aria-label="Diminuir"
      class="w-9 h-9 rounded-full bg-gray-100 hover:bg-red-100 text-brand-red font-bold text-lg flex items-center justify-center transition border border-gray-200">−</button>

    <input id="qty-${p.id}" type="number" value="${MIN_QTY}" min="${MIN_QTY}" step="25"
      class="w-16 text-center border border-gray-200 rounded-lg py-1.5 font-bold text-brand-dark text-sm focus:outline-none focus:ring-2 focus:ring-brand-red transition"
      aria-label="Quantidade de ${p.nome}" />

    <button onclick="alterarQty('${p.id}', 25)" aria-label="Aumentar"
      class="w-9 h-9 rounded-full bg-gray-100 hover:bg-green-100 text-green-700 font-bold text-lg flex items-center justify-center transition border border-gray-200">+</button>
  </div>
</div>

<!-- Botão Adicionar -->
<button onclick="adicionarAoCarrinho('${p.id}')"
  class="mt-5 w-full bg-brand-red hover:bg-red-700 text-white font-bold py-3 rounded-xl transition-all shadow hover:shadow-lg active:scale-95 text-sm">
  🛒 Adicionar ao Carrinho
</button>
</div>
</article>`;
    }).join('');
}

/* ── CARROSSEL (Index) ── */
let carouselIndex = 0;
const TOTAL_SLIDES = 4;

/**
 * Move o índice do Carousel.
 * @param {number} dir -1 Volta, 1 Avança
 */
function moveCarousel(dir) {
    carouselIndex = (carouselIndex + dir + TOTAL_SLIDES) % TOTAL_SLIDES;
    aplicarCarousel();
}

/**
 * Atualiza o visual (Trilação das imagens + troca de highlights nos dots).
 */
function aplicarCarousel() {
    const track = document.getElementById('carouselTrack');
    if (!track) return;
    track.style.transform = `translateX(-${carouselIndex * 100}%)`;

    document.querySelectorAll('.carousel-dot').forEach((d, i) => {
        d.classList.toggle('bg-white', i === carouselIndex);
        d.classList.toggle('bg-white/40', i !== carouselIndex);
    });
}

/**
 * Inicialização dos dots baseados no TOTAL_SLIDES e injeção do autoplay.
 */
function iniciarCarousel() {
    const dots = document.getElementById('carouselDots');
    if (!dots) return;
    for (let i = 0; i < TOTAL_SLIDES; i++) {
        const dot = document.createElement('button');
        dot.className = `carousel-dot w-2.5 h-2.5 rounded-full transition-all ${i === 0 ? 'bg-white' : 'bg-white/40'}`;
        dot.setAttribute('aria-label', `Slide ${i + 1}`);
        dot.onclick = () => { carouselIndex = i; aplicarCarousel(); };
        dots.appendChild(dot);
    }
    setInterval(() => moveCarousel(1), 5000);
}


/* =========================================================
   FUNÇÕES ESPECÍFICAS DA PÁGINA CART (Revisão e Pagamento)
   ========================================================= */

/**
 * Remove inteiramente a existência de um item no array carrinho usando o id.
 * @param {string} id 
 */
function removerItem(id) {
    carrinho = carrinho.filter(i => i.id !== id);
    localStorage.setItem('ds_carrinho', JSON.stringify(carrinho));
    renderizarCarrinho();
}

/**
 * Substituto de alteração de delta do card da página inical mas desta vez para os items persistentes no carrinho
 * Sempre valida a cota minima.
 * @param {string} id 
 * @param {*} novaQty Integer
 */
function alterarQtyCarrinho(id, novaQty) {
    novaQty = parseInt(novaQty, 10);

    if (isNaN(novaQty) || novaQty < MIN_QTY) novaQty = MIN_QTY;

    const item = carrinho.find(i => i.id === id);
    if (item) item.qty = novaQty;
    localStorage.setItem('ds_carrinho', JSON.stringify(carrinho));
    renderizarCarrinho();
}

/**
 * Regera as linhas HTML do carrinho de compras visual relendo os items
 * do array `carrinho`.
 */
function renderizarCarrinho() {
    const list = document.getElementById('cart-items-list');
    const empty = document.getElementById('cart-empty');
    if (!list || !empty) return; // ignora se não estiver na pag do carrinho

    const summary = document.getElementById('summary-lines');
    const subtotalEl = document.getElementById('subtotal-val');
    const totalEl = document.getElementById('total-val');
    const freteEl = document.getElementById('frete-val');
    const countLabel = document.getElementById('item-count-label');

    if (carrinho.length === 0) {
        list.innerHTML = '';
        empty.classList.remove('hidden');
        summary.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">Nenhum item no carrinho.</p>';
        subtotalEl.textContent = moeda(0);
        totalEl.textContent = moeda(0);
        countLabel.textContent = '';
        return;
    }

    empty.classList.add('hidden');
    countLabel.textContent = `(${carrinho.length} ${carrinho.length === 1 ? 'item' : 'itens'})`;

    const total = calcularTotais();
    const qtyTotal = carrinho.reduce((s, i) => s + i.qty, 0);

    // Frete grátis acima de 100 unidades
    const freteGratis = qtyTotal >= 100;
    freteEl.textContent = freteGratis ? 'GRÁTIS 🎉' : 'A combinar';
    freteEl.className = freteGratis ? 'text-green-600 font-black' : 'text-gray-500 font-semibold';

    // ── Linhas da tabela ──
    list.innerHTML = carrinho.map(item => {
        const subtItem = moeda(item.preco * item.qty);
        return `
<div class="cart-row grid grid-cols-[64px_1fr] sm:grid-cols-[80px_1fr_120px_110px_40px] gap-3 sm:gap-4 items-center px-4 sm:px-6 py-3 border-b border-gray-50 last:border-0">
<!-- Imagem -->
<img src="${item.img}" alt="${item.nome}" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-xl border border-gray-100 shadow-sm" />

<!-- Nome + preço unitário (mobile) -->
<div class="flex flex-col justify-center">
<p class="font-bold text-sm sm:text-base text-brand-dark leading-tight">${item.nome}</p>
<p class="text-gray-400 text-xs mt-0.5">${moeda(item.preco)} / unidade</p>
<p class="text-brand-red font-black text-sm sm:hidden mt-1">${subtItem}</p>
</div>

<!-- Preço (desktop) -->
<div class="hidden sm:flex flex-col items-center">
<p class="font-semibold text-sm">${moeda(item.preco)}</p>
<p class="text-gray-400 text-xs">/ un.</p>
</div>

<!-- Quantidade – com trava MIN_QTY -->
<div class="hidden sm:flex items-center gap-1 justify-center">
<button onclick="alterarQtyCarrinho('${item.id}', ${item.qty - 5})"
  class="w-7 h-7 rounded-full bg-gray-100 hover:bg-red-100 text-brand-red font-bold text-sm flex items-center justify-center border border-gray-200 transition">−</button>
<input type="number" value="${item.qty}" min="${MIN_QTY}" step="5"
  onchange="alterarQtyCarrinho('${item.id}', this.value)"
  class="w-14 text-center border border-gray-200 rounded-lg py-1 font-bold text-sm focus:outline-none focus:ring-1 focus:ring-brand-red" />
<button onclick="alterarQtyCarrinho('${item.id}', ${item.qty + 5})"
  class="w-7 h-7 rounded-full bg-gray-100 hover:bg-green-100 text-green-700 font-bold text-sm flex items-center justify-center border border-gray-200 transition">+</button>
</div>

<!-- Remover -->
<div class="hidden sm:flex justify-center">
<button onclick="removerItem('${item.id}')"
  title="Remover item"
  class="w-8 h-8 rounded-full bg-red-50 hover:bg-brand-red hover:text-white text-brand-red text-sm flex items-center justify-center transition border border-red-100">✕</button>
</div>
</div>`;
    }).join('');

    // ── Resumo do card lateral ──
    summary.innerHTML = carrinho.map(item => `
<div class="flex justify-between text-sm">
<span class="text-gray-600 truncate max-w-[180px]">${item.nome} <span class="text-gray-400">(${item.qty}un.)</span></span>
<span class="font-bold text-brand-dark whitespace-nowrap ml-2">${moeda(item.preco * item.qty)}</span>
</div>
`).join('');

    subtotalEl.textContent = moeda(total);
    totalEl.textContent = moeda(total);
    atualizarResumoSelects();
}

/**
 * Atualiza o bloco summary lateral com as escolhas de pagamento e estado do salgado.
 */
function atualizarResumoSelects() {
    const pagEl = document.getElementById('pagamento');
    const estEl = document.getElementById('estado');
    if (!pagEl || !estEl) return;

    const div = document.getElementById('summary-selects');
    const sumP = document.getElementById('sum-pagamento');
    const sumE = document.getElementById('sum-estado');

    if (pagEl.value || estEl.value) {
        div.classList.remove('hidden');
        sumP.textContent = pagEl.value || '—';
        sumE.textContent = estEl.value || '—';
    } else {
        div.classList.add('hidden');
    }
}

/**
 * Formata os dados no estado e envia o pedido pronto pela URL do WhatsApp.
 * Bloqueia se existir pendências nulas como falha de método de pagamento.
 */
function enviarWhatsApp() {
    const pagamento = document.getElementById('pagamento').value;
    const estado = document.getElementById('estado').value;
    const obs = document.getElementById('obs').value.trim();

    if (carrinho.length === 0) {
        alert('⚠️ Seu carrinho está vazio!');
        return;
    }
    if (!pagamento) {
        document.getElementById('pagamento').focus();
        document.getElementById('pagamento').classList.add('border-red-500');
        alert('⚠️ Por favor, selecione a forma de pagamento.');
        return;
    }
    if (!estado) {
        document.getElementById('estado').focus();
        document.getElementById('estado').classList.add('border-red-500');
        alert('⚠️ Por favor, selecione o estado do salgado (Fritos ou Congelados).');
        return;
    }

    const total = calcularTotais();
    const qtyTotal = carrinho.reduce((s, i) => s + i.qty, 0);

    let msg = '*PEDIDO – Salgados Dona Sogra*\n';
    msg += '─────────────────────\n';

    carrinho.forEach(item => {
        const sub = (item.preco * item.qty).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        msg += `• *${item.nome}*\n`;
        msg += `  ${item.qty} un. × ${item.preco.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })} = ${sub}\n`;
    });

    msg += '─────────────────────\n';
    msg += ` *Total de unidades:* ${qtyTotal}\n`;
    msg += ` *Valor total:* ${total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}\n`;
    msg += ` *Pagamento:* ${pagamento}\n`;
    msg += ` *Estado:* ${estado}\n`;

    if (obs) {
        msg += `📝 *Obs:* ${obs}\n`;
    }

    msg += '─────────────────────\n';
    msg += '_Pedido enviado pelo site Salgados Dona Sogra_ 🍽';

    const url = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(msg)}`;
    window.open(url, '_blank');
}

/* ─────────────────────────────────────────────────────────
   HOOKS DE DOM CARREGADO GLOBAL
───────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    // Funções index
    if (document.getElementById('product-grid')) {
        renderizarProdutos();
        iniciarCarousel();
    }

    // Funções cart
    if (document.getElementById('cart-items-list')) {
        renderizarCarrinho();

        // Listeners para selects do Resumo
        const pagamentoEl = document.getElementById('pagamento');
        if (pagamentoEl) pagamentoEl.addEventListener('change', atualizarResumoSelects);

        const estadoEl = document.getElementById('estado');
        if (estadoEl) estadoEl.addEventListener('change', atualizarResumoSelects);
    }

    // Atualiza badges em qualquer tela (só requer a div badge)
    atualizarBadges();
});
