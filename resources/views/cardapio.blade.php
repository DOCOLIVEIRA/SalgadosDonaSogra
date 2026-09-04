<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salgados Dona Sogra - Cardápio Online</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :focus-visible {
            outline: 3px solid #ff6b00;
            outline-offset: 3px;
        }
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: #ff6b00;
            color: #fff;
            padding: 8px 16px;
            z-index: 9999;
            text-decoration: none;
            border-radius: 0 0 4px 0;
        }
        .skip-link:focus {
            top: 0;
        }
        .touch-btn {
            min-width: 44px;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 8px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
        }
        .touch-btn:hover, .touch-btn:focus {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <a href="#conteudo-principal" class="skip-link">Pular para o conteúdo principal</a>

    <header role="banner" class="main-header">
        <div class="container">
            <h1>Salgados Dona Sogra</h1>
            <p>Os melhores salgados artesanais de Pirajuí/SP</p>
        </div>
    </header>

    <main id="conteudo-principal" role="main" class="container">
        <section aria-labelledby="titulo-cardapio">
            <h2 id="titulo-cardapio">Nosso Cardápio</h2>

            <div class="products-grid" role="list">
                @foreach($produtos as $produto)
                    <article class="product-card" role="listitem" aria-labelledby="prod-title-{{ $produto->id }}">
                        <img src="{{ asset($produto->imagem) }}" alt="Imagem do salgado {{ $produto->nome }}" loading="lazy">
                        <div class="product-info">
                            <h3 id="prod-title-{{ $produto->id }}">{{ $produto->nome }}</h3>
                            <p>{{ $produto->descricao }}</p>
                            <p class="price">R$ {{ number_format($produto->preco_unitario, 2, ',', '.') }} un.</p>
                            <p class="stock"><small>Estoque: {{ $produto->estoque_atual }} un.</small></p>

                            <div class="quantity-controls" aria-label="Controle de quantidade para {{ $produto->nome }}">
                                <button type="button" class="touch-btn" onclick="alterarQtd({{ $produto->id }}, -50)" aria-label="Remover 50 {{ $produto->nome }}">-</button>
                                <input type="number" id="qtd-{{ $produto->id }}" value="0" min="0" max="{{ $produto->estoque_atual }}" aria-label="Quantidade de {{ $produto->nome }}">
                                <button type="button" class="touch-btn" onclick="alterarQtd({{ $produto->id }}, 50)" aria-label="Adicionar 50 {{ $produto->nome }}">+</button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </main>

    <footer role="contentinfo" class="main-footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} Salgados Dona Sogra - Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
        function alterarQtd(id, delta) {
            const input = document.getElementById('qtd-' + id);
            let val = parseInt(input.value) || 0;
            val = Math.max(0, val + delta);
            input.value = val;
        }
    </script>
</body>
</html>
