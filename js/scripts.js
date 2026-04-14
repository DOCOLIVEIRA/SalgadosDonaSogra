/* ─────────────────────────────────────────────────────────
   DADOS DOS PRODUTOS
───────────────────────────────────────────────────────── */
let PRODUTOS = [];

/* ─────────────────────────────────────────────────────────
   CONSTANTES / CONFIGURAÇÕES DINÂMICAS
───────────────────────────────────────────────────────── */
let MIN_QTY = 50;
let STEP_QTY_INDEX = 50;
let STEP_QTY_CART = 5;
const WHATSAPP_NUMBER = '5514996748488'; // ← Altere aqui

/* ─────────────────────────────────────────────────────────
   MODO EVENTO E NORMAL
───────────────────────────────────────────────────────── */
let isEvento = false;

function iniciarModoEvento() {
    isEvento = true;
    localStorage.setItem('ds_modo_evento', 'true');
    mostrarToast('🎉 Modo Festa ativado! Ao clicar no carrinho você irá para o Orçamento.', 'ok');
    setTimeout(() => { document.getElementById('produtos').scrollIntoView({ behavior: 'smooth' }); }, 300);
}

function iniciarModoNormal() {
    isEvento = false;
    localStorage.removeItem('ds_modo_evento');
    mostrarToast('🛒 Modo Pedido Padrão ativado! O carrinho funcionará normalmente.', 'ok');
}

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
        mostrarToast('🛒 Sua lista está vazia!', 'warn');
        return;
    }
    
    // Roteador: Evento ou Carrinho Normal?
    const hasEventoLocal = localStorage.getItem('ds_modo_evento') === 'true';
    if (isEvento || hasEventoLocal) {
        window.location.href = 'evento.html';
    } else {
        window.location.href = 'cart.html';
    }
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

    if (PRODUTOS.length === 0) {
        grid.innerHTML = '<p class="col-span-full text-center text-gray-500 py-10">Nenhum produto disponível no momento.</p>';
        return;
    }

    grid.innerHTML = PRODUTOS.map(p => {
        const precoCentoFmt = (p.preco * 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        const precoMinFmt = (p.preco * MIN_QTY).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        return `
<article class="product-card bg-white rounded-2xl overflow-hidden shadow-md flex flex-col" id="card-${p.id}">
<!-- Imagem do produto -->
<div class="relative overflow-hidden h-52 bg-gray-100 flex items-center justify-center">
<img src="${p.img}" alt="${p.nome}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" loading="lazy" onerror="this.src='img/logo.png'" />
<span class="absolute top-3 left-3 bg-brand-red text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wide">Artesanal</span>
</div>

<!-- Conteúdo do card -->
<div class="p-5 flex flex-col flex-1">
<h3 class="font-black text-lg text-brand-dark leading-tight">${p.nome}</h3>
<p class="text-gray-500 text-sm mt-1 flex-1">${p.desc || ''}</p>

<!-- Preços -->
<div class="mt-4 flex items-end justify-between">
  <div>
    <p class="text-gray-400 text-xs">Preço por cento (100 un.)</p>
    <p class="text-brand-red font-black text-2xl">${precoCentoFmt}<span class="text-sm font-normal text-gray-400"> /cento</span></p>
    <p class="text-gray-400 text-xs mt-0.5">${MIN_QTY} un. = <strong class="text-gray-600">${precoMinFmt}</strong></p>
  </div>
</div>

<!-- CONTROLE DE QUANTIDADE -->
<div class="mt-4">
  <label class="text-xs text-gray-500 font-semibold mb-1 block">Quantidade <span class="text-brand-red">(mín. ${MIN_QTY})</span></label>
  <div class="flex items-center gap-2">
    <button onclick="alterarQty('${p.id}', -${STEP_QTY_INDEX})" aria-label="Diminuir"
      class="w-9 h-9 rounded-full bg-gray-100 hover:bg-red-100 text-brand-red font-bold text-lg flex items-center justify-center transition border border-gray-200">−</button>

    <input id="qty-${p.id}" type="number" value="${MIN_QTY}" min="${MIN_QTY}" step="${STEP_QTY_INDEX}"
      class="w-16 text-center border border-gray-200 rounded-lg py-1.5 font-bold text-brand-dark text-sm focus:outline-none focus:ring-2 focus:ring-brand-red transition"
      aria-label="Quantidade de ${p.nome}" />

    <button onclick="alterarQty('${p.id}', ${STEP_QTY_INDEX})" aria-label="Aumentar"
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

    // Frete foi removido. Apenas retirada no local.

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
<button onclick="alterarQtyCarrinho('${item.id}', ${item.qty - STEP_QTY_CART})"
  class="w-7 h-7 rounded-full bg-gray-100 hover:bg-red-100 text-brand-red font-bold text-sm flex items-center justify-center border border-gray-200 transition">−</button>
<input type="number" value="${item.qty}" min="${MIN_QTY}" step="${STEP_QTY_CART}"
  onchange="alterarQtyCarrinho('${item.id}', this.value)"
  class="w-14 text-center border border-gray-200 rounded-lg py-1 font-bold text-sm focus:outline-none focus:ring-1 focus:ring-brand-red" />
<button onclick="alterarQtyCarrinho('${item.id}', ${item.qty + STEP_QTY_CART})"
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
    const dataEl = document.getElementById('data_pedido');
    const horaEl = document.getElementById('hora_pedido');

    if (!pagEl || !estEl) return;

    const div = document.getElementById('summary-selects');
    const sumP = document.getElementById('sum-pagamento');
    const sumE = document.getElementById('sum-estado');
    const dhContainer = document.getElementById('sum-data-hora-container');
    const sumDH = document.getElementById('sum-data-hora');

    if (pagEl.value || estEl.value || (dataEl && dataEl.value) || (horaEl && horaEl.value)) {
        div.classList.remove('hidden');
        sumP.textContent = pagEl.value || '—';
        sumE.textContent = estEl.value || '—';

        if (dataEl && dataEl.value && horaEl && horaEl.value) {
            // Formatar data YYYY-MM-DD para DD/MM/YYYY
            const partesData = dataEl.value.split('-');
            const dataFormatada = partesData.length === 3 ? `${partesData[2]}/${partesData[1]}/${partesData[0]}` : dataEl.value;
            sumDH.textContent = `${dataFormatada} às ${horaEl.value}`;
            dhContainer.classList.remove('hidden');
        } else {
            dhContainer.classList.add('hidden');
        }

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
    const dataPedido = document.getElementById('data_pedido').value;
    const horaPedido = document.getElementById('hora_pedido').value;
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
    if (!dataPedido) {
        document.getElementById('data_pedido').focus();
        document.getElementById('data_pedido').classList.add('border-red-500');
        alert('⚠️ Por favor, informe a data de entrega ou retirada.');
        return;
    }
    if (!horaPedido) {
        document.getElementById('hora_pedido').focus();
        document.getElementById('hora_pedido').classList.add('border-red-500');
        alert('⚠️ Por favor, informe o horário do pedido.');
        return;
    }

    const partesData = dataPedido.split('-');
    const dataFormatada = partesData.length === 3 ? `${partesData[2]}/${partesData[1]}/${partesData[0]}` : dataPedido;

    const total = calcularTotais();
    const qtyTotal = carrinho.reduce((s, i) => s + i.qty, 0);

    const clienteNomeInfo = document.getElementById('cliente_nome');
    const clienteNome = clienteNomeInfo ? clienteNomeInfo.value.trim() : '';
    const clienteTelefoneInfo = document.getElementById('cliente_telefone');
    const clienteTelefone = clienteTelefoneInfo ? clienteTelefoneInfo.value.trim() : '';

    if (!clienteNome) {
        clienteNomeInfo.focus();
        clienteNomeInfo.classList.add('border-red-500');
        alert('⚠️ Por favor, informe seu nome completo.');
        return;
    }

    if (!clienteTelefone) {
        clienteTelefoneInfo.focus();
        clienteTelefoneInfo.classList.add('border-red-500');
        alert('⚠️ Por favor, informe seu WhatsApp.');
        return;
    }

    const btn = document.getElementById('btn-whatsapp');
    const bkpHTML = btn.innerHTML;
    btn.innerHTML = 'Processando...';
    btn.disabled = true;

    // Enviar para o banco primeiro
    fetch('api/salvar_pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            nome: clienteNome,
            telefone: clienteTelefone,
            valor_total: total,
            itens: carrinho
        })
    }).then(r => r.json()).then(resp => {
        if (!resp.sucesso) {
            alert('Erro ao registrar pedido no sistema: ' + (resp.erro || 'Desconhecido'));
        }
        
        // Continua montando a mensagem para o whatsapp
        let msg = isEvento ? '*🚀 ORÇAMENTO FESTA / EVENTO*\n' : '*PEDIDO – Salgados Dona Sogra*\n';
        msg += `👩‍🦱 *Cliente:* ${clienteNome} - Cel: ${clienteTelefone}\n`;
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
        msg += ` *Para quando:* ${dataFormatada} às ${horaPedido}\n`;

        if (isEvento) {
            msg += ` *⚠️ Solicitou fritar no local. Aguardando combinação de valores.*\n`;
        }

        if (obs) {
            msg += `📝 *Obs:* ${obs}\n`;
        }

        msg += '─────────────────────\n';
        msg += '_Agradecemos a preferência!_ 🍽';

        const url = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(msg)}`;
        
        // Esvazia carrinho se houver sucesso garantido
        carrinho = [];
        salvarCarrinho();
        
        window.location.href = url;
    }).catch(err => {
        alert('Erro de comunicação. Tente novamente.');
        btn.innerHTML = bkpHTML;
        btn.disabled = false;
    });
}

function enviarOrcamentoWhatsApp() {
    if (carrinho.length === 0) {
        alert('⚠️ Sua lista está vazia!');
        return;
    }

    const clienteNomeInfo = document.getElementById('cliente_nome');
    const clienteNome = clienteNomeInfo ? clienteNomeInfo.value.trim() : '';
    const clienteTelefoneInfo = document.getElementById('cliente_telefone');
    const clienteTelefone = clienteTelefoneInfo ? clienteTelefoneInfo.value.trim() : '';
    const dataEvento = document.getElementById('data_evento') ? document.getElementById('data_evento').value : '';
    const horaEvento = document.getElementById('hora_evento') ? document.getElementById('hora_evento').value : '';
    const localFesta = document.getElementById('local_festa') ? document.getElementById('local_festa').value.trim() : '';

    if (!clienteNome) {
        clienteNomeInfo.focus();
        clienteNomeInfo.classList.add('border-red-500');
        alert('⚠️ Por favor, informe seu nome completo.');
        return;
    }

    if (!clienteTelefone) {
        clienteTelefoneInfo.focus();
        clienteTelefoneInfo.classList.add('border-red-500');
        alert('⚠️ Por favor, informe seu WhatsApp.');
        return;
    }

    if (!dataEvento) {
        alert('⚠️ Por favor, informe a data da festa.');
        return;
    }
    if (!horaEvento) {
        alert('⚠️ Por favor, informe a hora da festa.');
        return;
    }
    if (!localFesta) {
        alert('⚠️ Por favor, informe a cidade e o local.');
        return;
    }

    const partesData = dataEvento.split('-');
    const dataFormatada = partesData.length === 3 ? `${partesData[2]}/${partesData[1]}/${partesData[0]}` : dataEvento;

    const total = calcularTotais();
    const qtyTotal = carrinho.reduce((s, i) => s + i.qty, 0);

    const btn = document.getElementById('btn-whatsapp-orcamento');
    const bkpHTML = btn.innerHTML;
    btn.innerHTML = 'Processando...';
    btn.disabled = true;

    // Obs: Em orçamentos, nós não salvamos no banco na tabela pedidos porque o preço não está fechado.
    // Vamos direto pro zap.
    
    let msg = '*🚀 ORÇAMENTO FESTA / EVENTO*\n';
    msg += `👩‍🦱 *Cliente:* ${clienteNome} - Cel: ${clienteTelefone}\n`;
    msg += `📍 *Local:* ${localFesta}\n`;
    msg += `📅 *Quando:* ${dataFormatada} às ${horaEvento}\n`;
    msg += '─────────────────────\n';
    msg += ' *📦 LISTA DE SALGADOS ESCOLHIDOS:*\n';

    carrinho.forEach(item => {
        const sub = (item.preco * item.qty).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        msg += `• *${item.nome}*\n`;
        msg += `  ${item.qty} un. × ${item.preco.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })} = ${sub}\n`;
    });

    msg += '─────────────────────\n';
    msg += ` *Total de unidades:* ${qtyTotal}\n`;
    msg += ` *Estimativa bruta (Só Salgados):* ${total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}\n`;
    msg += ` *⚠️ Lembrete:* Fritura no local e deslocamento pendente de cálculo pela Sogra.\n`;
    msg += '─────────────────────\n';
    msg += '_Aguardo retorno para fechar negócio!_ 🎉';

    const url = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(msg)}`;
    
    carrinho = [];
    salvarCarrinho();
    
    // Limpar modo evento do LocalStorage
    localStorage.removeItem('ds_modo_evento');
    isEvento = false;

    window.location.href = url;
}

function carregarDadosDaLoja() {
    fetch('api/get_loja_dados.php')
        .then(r => r.json())
        .then(data => {
            if (data.sucesso) {
                PRODUTOS = data.produtos;
                const conf = data.configuracoes;
                if (conf) {
                    if (conf.min_qty) MIN_QTY = parseInt(conf.min_qty, 10);
                    if (conf.step_qty_index) STEP_QTY_INDEX = parseInt(conf.step_qty_index, 10);
                    if (conf.step_qty_cart) STEP_QTY_CART = parseInt(conf.step_qty_cart, 10);
                }
                
                // Funções index
                if (document.getElementById('product-grid')) {
                    renderizarProdutos();
                    iniciarCarousel();
                }

                // Funções cart
                if (document.getElementById('cart-items-list')) {
                    
                    // O alerta-evento só servia no modo integrado, mas como o usuário quis
                    // a rota nova, não faz sentido aqui, porém vamos deixar robusto
                    const params = new URLSearchParams(window.location.search);
                    const hasEventoParam = params.get('mode') === 'evento';
                    const hasEventoLocal = localStorage.getItem('ds_modo_evento') === 'true';
                    
                    if (hasEventoParam || hasEventoLocal) {
                        isEvento = true;
                        const alertEl = document.getElementById('alerta-evento');
                        if (alertEl) alertEl.classList.remove('hidden');
                        const obsEl = document.getElementById('obs');
                        if (obsEl) obsEl.placeholder = 'Informe a cidade e local da festa...';
                    }

                    renderizarCarrinho();

                    // Listeners para selects do Resumo
                    const pagamentoEl = document.getElementById('pagamento');
                    if (pagamentoEl) pagamentoEl.addEventListener('change', atualizarResumoSelects);

                    const estadoEl = document.getElementById('estado');
                    if (estadoEl) estadoEl.addEventListener('change', atualizarResumoSelects);

                    const dataEl = document.getElementById('data_pedido');
                    if (dataEl) dataEl.addEventListener('change', atualizarResumoSelects);

                    const horaEl = document.getElementById('hora_pedido');
                    if (horaEl) horaEl.addEventListener('change', atualizarResumoSelects);
                }
            } else {
                console.error("Erro ao carregar dados:", data.erro);
            }
        })
        .catch(err => {
            console.error("Problema no fetch:", err);
            
            // FALLBACK: Como vc está testando localmente sem banco, vamos popular os produtos falsos
            // para que a tela não fique em branco e você consiga testar o visual de Eventos.
            if (PRODUTOS.length === 0) {
                PRODUTOS = [
                    { id: 'coxinha de frango', nome: 'Coxinha de Frango', desc: 'Massa crocante.', preco: 0.85, img: 'img/coxinha.png' },
                    { id: 'bolinha de queijo', nome: 'Bolinha de Queijo', desc: 'Massa crocante.', preco: 0.85, img: 'img/bolinha_queijo.jpg' },
                    { id: 'kibe', nome: 'Kibe', desc: 'Kibe tradicional.', preco: 0.85, img: 'img/kibe.png' },
                    { id: 'trouxinha de calabresa', nome: 'Trouxinha de Calabresa', desc: 'Massa crocante.', preco: 0.85, img: 'img/almofadinha_calabresa_e_queijo.jpg' },
                    { id: 'enrroladinho de salsicha', nome: 'Enrroladinho de Salsicha', desc: 'Massa crocante.', preco: 0.85, img: 'img/croquete_de_salsicha.png' },
                    { id: 'coxinha de carne', nome: 'Coxinha de Carne', desc: 'Massa crocante.', preco: 0.85, img: 'img/coxinha_de_carne.png' },
                    { id: 'kibe com queijo', nome: 'Kibolinha', desc: 'Kibe com queijo.', preco: 0.85, img: 'img/kibolinha.png' },
                    { id: 'fataya', nome: 'Fataya', desc: 'Massa fininha.', preco: 1.00, img: 'img/fataya.jpeg' }
                ];
            }

            // Fallback render...
            if (document.getElementById('product-grid')) renderizarProdutos();
            if (document.getElementById('cart-items-list')) renderizarCarrinho();
        });
}

/* ─────────────────────────────────────────────────────────
   HOOKS DE DOM CARREGADO GLOBAL
───────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    // Carrega produtos dinamicamente e então renderiza a parte correta
    carregarDadosDaLoja();

    // Atualiza badges em qualquer tela (só requer a div badge)
    atualizarBadges();
});
