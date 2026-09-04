<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Carrinho – Salgados Dona Sogra</title>
    <meta name="description" content="Confira seu pedido e envie via WhatsApp." />
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#C0392B',
                            cream: '#FFF8F0',
                            dark: '#1A1A1A',
                            gold: '#F0A500',
                            yellow: '#FBBF24',
                        }
                    },
                    fontFamily: { sans: ['Outfit', 'sans-serif'] }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :focus-visible {
            outline: 3px solid #F0A500;
            outline-offset: 3px;
        }
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: #C0392B;
            color: #fff;
            padding: 8px 16px;
            z-index: 9999;
            text-decoration: none;
            border-radius: 0 0 4px 0;
            font-weight: bold;
        }
        .skip-link:focus {
            top: 0;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-brand-dark antialiased min-h-screen flex flex-col">
    <a href="#conteudo-carrinho" class="skip-link">Pular para os itens do carrinho</a>

    <!-- ═══════════════════════════════════════════════════════
     BARRA DE AVISOS (estilo BK)
════════════════════════════════════════════════════════ -->
    <div class="bg-brand-dark text-white text-xs text-center py-2 px-4">
        🔥 Faça seu pedido hoje
    </div>

    <!-- ═══════════════════════════════════════════════════════
     HEADER  (estilo Wendy's)
════════════════════════════════════════════════════════ -->
    <header class="sticky top-0 z-50 shadow-lg" role="banner">
        <div class="bg-brand-dark flex items-center justify-between px-5 sm:px-10 py-3">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo Dona Sogra Salgados Artesanais" class="h-16 w-auto object-contain" />
                </div>
                <div class="hidden sm:block">
                    <p class="text-white font-black text-xl leading-tight tracking-wide">Dona Sogra</p>
                    <p class="text-brand-gold text-[11px] font-semibold tracking-widest uppercase">Salgados Artesanais
                    </p>
                </div>
            </a>
            <nav class="flex items-center gap-4 text-white text-sm font-semibold" role="navigation" aria-label="Navegação do Carrinho">
                <a href="{{ url('/') }}" class="flex items-center gap-1 hover:text-brand-gold transition-colors min-h-[44px]">‹ Voltar ao
                    Cardápio</a>
            </nav>
        </div>
        <div class="bg-brand-cream border-b border-red-200 px-6 sm:px-10 py-1 hidden sm:block">
            <p class="text-brand-dark text-xs font-semibold tracking-wide">🛒 Revise seu pedido e escolha a forma de
                pagamento</p>
        </div>
    </header>

    <!-- ═══════════════════════════════════════════════════════
     CONTEÚDO PRINCIPAL
════════════════════════════════════════════════════════ -->
    <main id="conteudo-carrinho" role="main"
        class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-8 py-10 lg:grid lg:grid-cols-[1fr_380px] lg:gap-8 items-start">

        <!-- ── LADO ESQUERDO: Lista de itens do carrinho ── -->
        <div>
            <h1 class="font-black text-2xl sm:text-3xl mb-6 flex items-center gap-2">
                🛒 Meu Carrinho
                <span id="item-count-label" class="text-sm font-semibold text-gray-400"></span>
            </h1>

            <!-- Tabela de itens -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <!-- Cabeçalho da tabela -->
                <div
                    class="hidden sm:grid grid-cols-[80px_1fr_120px_110px_40px] gap-4 items-center px-6 py-3 bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-400 uppercase tracking-wide">
                    <span>Imagem</span>
                    <span>Produto</span>
                    <span class="text-center">Preço</span>
                    <span class="text-center">Qtd</span>
                    <span></span>
                </div>

                <!-- Linhas dos produtos (injetadas pelo JS) -->
                <div id="cart-items-list" role="list"></div>

                <!-- Estado vazio -->
                <div id="cart-empty" class="hidden py-16 text-center">
                    <p class="text-5xl mb-4">🥟</p>
                    <p class="text-gray-400 font-semibold">Seu carrinho está vazio.</p>
                    <a href="{{ url('/') }}"
                        class="mt-4 inline-block bg-brand-red text-white font-bold px-6 py-3 rounded-full text-sm hover:bg-red-700 transition min-h-[44px]">Ver
                        Cardápio</a>
                </div>
            </div>

            <!-- ── DADOS DO CLIENTE ── -->
            <div class="mt-8 mb-6" id="customer-info-section">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">Seus Dados</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="cliente_nome" class="block text-sm font-bold text-brand-dark mb-1">
                            Nome Completo <span class="text-brand-red">*</span>
                        </label>
                        <input type="text" id="cliente_nome" placeholder="Seu nome"
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-red transition font-semibold bg-white" />
                    </div>
                    <div>
                        <label for="cliente_telefone" class="block text-sm font-bold text-brand-dark mb-1">
                            Telefone (WhatsApp) <span class="text-brand-red">*</span>
                        </label>
                        <input type="tel" id="cliente_telefone" placeholder="(  )       -    "
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-red transition font-semibold bg-white" />
                    </div>
                </div>
            </div>

            <!-- AVISO RETIRADA NO LOCAL -->
            <div
                class="mt-2 mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-xl flex gap-3 text-sm">
                <span class="text-lg">📍</span>
                <div>
                    <h4 class="font-bold mb-0.5">Apenas Retirada no Local</h4>
                    <p class="text-yellow-700/80 leading-snug">Não fazemos entregas por enquanto. Todos os pedidos devem
                        ser retirados em nosso endereço (Pirajuí-SP).</br> Rachid Cury nº404 - Centro</p>
                </div>
            </div>

            <!-- ── SELECTS OBRIGATÓRIOS ── -->
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">Opções do Pedido</h3>
            <div class="grid sm:grid-cols-2 gap-4" id="selects-section">

                <!-- 1. Forma de Pagamento -->
                <div>
                    <label for="pagamento" class="block text-sm font-bold text-brand-dark mb-1">
                        💳 Forma de Pagamento <span class="text-brand-red">*</span>
                    </label>
                    <select id="pagamento"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-red transition font-semibold bg-white appearance-none cursor-pointer">
                        <option value="">— Selecione —</option>
                        <option value="Pix">💠 Pix</option>
                        <option value="Dinheiro">💵 Dinheiro</option>
                        <option value="Cartão">💳 Cartão</option>
                    </select>
                </div>

                <!-- 2. Estado do Salgado -->
                <div>
                    <label for="estado" class="block text-sm font-bold text-brand-dark mb-1">
                        🔥 Estado do Salgado <span class="text-brand-red">*</span>
                    </label>
                    <select id="estado"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-red transition font-semibold bg-white appearance-none cursor-pointer">
                        <option value="">— Selecione —</option>
                        <option value="Fritos">🔥 Fritos (prontos para consumo)</option>
                        <option value="Congelados">❄️ Congelados (frite em casa)</option>
                    </select>
                </div>

                <!-- 3. Data do Pedido -->
                <div>
                    <label for="data_pedido" class="block text-sm font-bold text-brand-dark mb-1">
                        📅 Data da Retirada <span class="text-brand-red">*</span>
                    </label>
                    <input type="date" id="data_pedido"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-red transition font-semibold bg-white" />
                </div>

                <!-- 4. Hora do Pedido -->
                <div>
                    <label for="hora_pedido" class="block text-sm font-bold text-brand-dark mb-1">
                        ⏰ Horário <span class="text-brand-red">*</span>
                    </label>
                    <input type="time" id="hora_pedido"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-red transition font-semibold bg-white" />
                </div>
            </div>

            <!-- Mensagem de observação -->
            <div class="mt-4">
                <label for="obs" class="block text-sm font-bold text-brand-dark mb-1">📝 Observações (opcional)</label>
                <textarea id="obs" rows="2" placeholder="Ex: sem gergelim, ponto extra de fritura..."
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-red transition resize-none bg-white"></textarea>
            </div>

            <!-- ALERTA DE EVENTO (INVISÍVEL POR PADRÃO) -->
            <div id="alerta-evento"
                class="hidden mt-6 bg-brand-gold/10 border border-brand-gold/30 rounded-xl p-4 flex gap-3 text-sm">
                <span class="text-xl">🎉</span>
                <div>
                    <h4 class="font-bold text-brand-dark mb-1">Pedido para Festa/Evento</h4>
                    <p class="text-gray-600 leading-snug">Se você desejar que os salgados sejam fritos diretamente no
                        local da festa, <strong>existe um valor adicional a combinar</strong>. Nossa equipe entrará em
                        contato confirmando esse valor após o envio.</p>
                </div>
            </div>
        </div><!-- /esquerda -->

        <!-- ── LADO DIREITO (sticky): Card de Resumo e Checkout ── -->
        <aside class="mt-8 lg:mt-0">
            <div class="summary-sticky bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">

                <!-- Cabeçalho do card -->
                <div class="bg-brand-red text-white px-6 py-4">
                    <h2 class="font-black text-xl">Resumo do Pedido</h2>
                    <p class="text-white/80 text-xs mt-0.5">Verifique antes de enviar</p>
                </div>

                <div class="px-6 py-5 space-y-3" id="summary-lines"></div>

                <div class="px-6 pb-5">
                    <!-- Divisor -->
                    <div class="border-t border-gray-100 pt-4 mb-4">
                        <div class="flex justify-between text-sm text-gray-500 mb-1">
                            <span>Subtotal</span>
                            <span id="subtotal-val" class="font-semibold">R$ 0,00</span>
                        </div>
                        <div class="flex justify-between items-center mt-3">
                            <span class="font-black text-xl text-brand-dark">Total</span>
                            <span id="total-val" class="font-black text-2xl text-brand-red">R$ 0,00</span>
                        </div>
                    </div>

                    <!-- Informações de pagamento e estado (resumo visual) -->
                    <div id="summary-selects" class="mb-4 space-y-2 text-sm hidden">
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                            <span>💳</span>
                            <span class="text-gray-500">Pagamento:</span>
                            <span id="sum-pagamento" class="font-bold text-brand-dark ml-auto"></span>
                        </div>
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                            <span>🔥</span>
                            <span class="text-gray-500">Estado:</span>
                            <span id="sum-estado" class="font-bold text-brand-dark ml-auto"></span>
                        </div>
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2 hidden"
                            id="sum-data-hora-container">
                            <span>📅</span>
                            <span class="text-gray-500">Para:</span>
                            <span id="sum-data-hora" class="font-bold text-brand-dark ml-auto"></span>
                        </div>
                    </div>

                    <!-- BOTÃO ENVIAR VIA WHATSAPP -->
                    <button id="btn-whatsapp" onclick="enviarWhatsApp()"
                        class="w-full bg-green-600 hover:bg-green-500 active:scale-95 text-white font-black py-4 rounded-xl text-base flex items-center justify-center gap-2 transition-all shadow-lg min-h-[44px]"
                        aria-label="Enviar Pedido para o WhatsApp">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current flex-shrink-0">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                            <path
                                d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.123 1.528 5.855L.077 23.077a1 1 0 001.18 1.18l5.222-1.451A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22a9.944 9.944 0 01-5.07-1.38l-.361-.214-3.742 1.04 1.04-3.742-.214-.361A9.944 9.944 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                        </svg>
                        Enviar Pedido via WhatsApp
                    </button>

                    <p class="text-center text-gray-400 text-xs mt-3">
                        Ao clicar, você será redirecionado ao WhatsApp com o pedido formatado.
                    </p>
                </div>
            </div>
        </aside>

    </main><!-- /main -->

    <!-- ═══════════════════════════════════════════════════════
     FOOTER MINI
════════════════════════════════════════════════════════ -->
    <footer class="bg-brand-dark text-gray-500 text-xs text-center py-5 mt-8" role="contentinfo">
        <p>&copy; {{ date('Y') }} Salgados Dona Sogra – Pirajuí/SP. Todos os direitos reservados.</p>
    </footer>

    <!-- ═══════════════════════════════════════════════════════
     JAVASCRIPT – CARRINHO & WHATSAPP
════════════════════════════════════════════════════════ -->
    <script src="{{ asset('js/scripts.js') }}"></script>
</body>

</html>
