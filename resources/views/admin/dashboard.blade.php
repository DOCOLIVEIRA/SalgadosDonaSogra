<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin – Salgados Dona Sogra</title>
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
                        }
                    },
                    fontFamily: { sans: ['Outfit', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans text-brand-dark min-h-screen flex flex-col">

    <header class="bg-brand-dark text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" class="h-10 w-auto">
                <span class="font-black text-xl text-brand-gold tracking-wide">Dona Sogra - Painel Admin</span>
            </div>
            <a href="{{ url('/') }}" target="_blank" class="text-xs bg-brand-gold text-brand-dark font-bold px-4 py-2 rounded-full hover:bg-yellow-300 transition">
                🌐 Ver Loja Pública
            </a>
        </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto w-full px-6 py-8">
        
        <!-- CARDS DE METRICAS E ANALISE DE DADOS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-brand-red">
                <p class="text-xs font-bold text-gray-400 uppercase">Total de Pedidos</p>
                <p class="font-black text-3xl text-brand-dark mt-2">{{ $totalPedidos }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-yellow-500">
                <p class="text-xs font-bold text-gray-400 uppercase">Pedidos Pendentes</p>
                <p class="font-black text-3xl text-yellow-600 mt-2">{{ $pedidosPendentes }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-green-500">
                <p class="text-xs font-bold text-gray-400 uppercase">Faturamento Estimado</p>
                <p class="font-black text-3xl text-green-600 mt-2">R$ {{ number_format($faturamentoTotal, 2, ',', '.') }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-blue-500">
                <p class="text-xs font-bold text-gray-400 uppercase">Produtos no Catálogo</p>
                <p class="font-black text-3xl text-blue-600 mt-2">{{ $totalProdutos }}</p>
            </div>
        </div>

        <!-- TABELA DE ULTIMOS PEDIDOS -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-brand-dark text-white flex justify-between items-center">
                <h2 class="font-black text-lg">📦 Últimos Pedidos Recebidos</h2>
                <span class="text-xs text-brand-gold font-bold">Atualização em Tempo Real</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs">
                            <th class="py-3 px-6">ID</th>
                            <th class="py-3 px-6">Cliente</th>
                            <th class="py-3 px-6">WhatsApp</th>
                            <th class="py-3 px-6">Valor Total</th>
                            <th class="py-3 px-6">Status</th>
                            <th class="py-3 px-6">Data/Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($ultimosPedidos as $pedido)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-6 font-bold">#{{ $pedido->id }}</td>
                                <td class="py-3 px-6 font-semibold text-brand-dark">{{ $pedido->nome_cliente }}</td>
                                <td class="py-3 px-6 text-gray-600">{{ $pedido->telefone_cliente ?? '—' }}</td>
                                <td class="py-3 px-6 font-bold text-brand-red">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</td>
                                <td class="py-3 px-6">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                        @if($pedido->status == 'Aguardando Confirmação') bg-yellow-100 text-yellow-800
                                        @elseif($pedido->status == 'Entregue') bg-green-100 text-green-800
                                        @elseif($pedido->status == 'Expirado' || $pedido->status == 'Cancelado') bg-red-100 text-red-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ $pedido->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-xs text-gray-400">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400 font-semibold">Nenhum pedido registrado até o momento.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="bg-brand-dark text-gray-400 text-xs text-center py-4">
        &copy; {{ date('Y') }} Salgados Dona Sogra – Painel Administrativo Laravel.
    </footer>

</body>
</html>
