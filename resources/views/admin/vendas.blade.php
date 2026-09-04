<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Vendas – Admin Dona Sogra</title>
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
                <span class="font-black text-xl text-brand-gold">Dona Sogra - Gerenciamento de Vendas</span>
            </div>
            <nav class="flex gap-4 text-sm font-semibold">
                <a href="{{ url('/admin') }}" class="hover:text-brand-gold transition">📊 Dashboard</a>
                <a href="{{ url('/admin/vendas') }}" class="text-brand-gold font-bold">💰 Vendas</a>
                <a href="{{ url('/admin/produtos') }}" class="hover:text-brand-gold transition">🥐 Estoque</a>
                <a href="{{ url('/admin/usuarios') }}" class="hover:text-brand-gold transition">👤 Usuários</a>
                <a href="{{ url('/') }}" target="_blank" class="bg-brand-gold text-brand-dark font-bold px-3 py-1.5 rounded-full text-xs hover:bg-yellow-300 transition">🌐 Loja Pública</a>
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto w-full px-6 py-8">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-brand-dark text-white flex justify-between items-center">
                <h1 class="font-black text-xl">💰 Gerenciamento de Pedidos e Vendas</h1>
                <span class="text-xs text-brand-gold font-bold">{{ $pedidos->count() }} Pedidos Registrados</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs">
                            <th class="py-3 px-6">ID</th>
                            <th class="py-3 px-6">Cliente & Contato</th>
                            <th class="py-3 px-6">Itens do Pedido</th>
                            <th class="py-3 px-6">Valor Total</th>
                            <th class="py-3 px-6">Status Atual</th>
                            <th class="py-3 px-6 text-center">Atualizar Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pedidos as $pedido)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-6 font-bold">#{{ $pedido->id }}</td>
                                <td class="py-3 px-6">
                                    <p class="font-bold text-brand-dark">{{ $pedido->nome_cliente }}</p>
                                    <p class="text-xs text-gray-500">📱 {{ $pedido->telefone_cliente ?? 'Não informado' }}</p>
                                    <p class="text-[10px] text-gray-400 mt-1">📅 {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                                </td>
                                <td class="py-3 px-6 text-xs">
                                    <ul class="space-y-1">
                                        @foreach($pedido->itens as $item)
                                            <li>• <strong>{{ $item->quantidade }}x</strong> {{ $item->produto->nome ?? 'Produto' }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="py-3 px-6 font-black text-brand-red">
                                    R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-6">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                        @if($pedido->status == 'Aguardando Confirmação') bg-yellow-100 text-yellow-800
                                        @elseif($pedido->status == 'Entregue') bg-green-100 text-green-800
                                        @elseif($pedido->status == 'Expirado' || $pedido->status == 'Cancelado') bg-red-100 text-red-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ $pedido->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <form action="{{ url('/admin/vendas/' . $pedido->id . '/status') }}" method="POST" class="flex items-center justify-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="border rounded px-2 py-1 text-xs font-bold focus:outline-none">
                                            <option value="Aguardando Confirmação" {{ $pedido->status == 'Aguardando Confirmação' ? 'selected' : '' }}>Aguardando</option>
                                            <option value="Pendente" {{ $pedido->status == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                                            <option value="Em preparo" {{ $pedido->status == 'Em preparo' ? 'selected' : '' }}>Em preparo</option>
                                            <option value="Pronto" {{ $pedido->status == 'Pronto' ? 'selected' : '' }}>Pronto</option>
                                            <option value="Entregue" {{ $pedido->status == 'Entregue' ? 'selected' : '' }}>Entregue</option>
                                            <option value="Cancelado" {{ $pedido->status == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                                        </select>
                                        <button type="submit" class="bg-brand-dark text-white text-xs font-bold px-3 py-1 rounded hover:bg-black transition">
                                            Alterar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="bg-brand-dark text-gray-400 text-xs text-center py-4">
        &copy; {{ date('Y') }} Salgados Dona Sogra – Gerenciamento de Vendas.
    </footer>

</body>
</html>
