<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Produtos – Admin Dona Sogra</title>
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
                <span class="font-black text-xl text-brand-gold">Dona Sogra - Gestão de Salgados</span>
            </div>
            <nav class="flex gap-4 text-sm font-semibold">
                <a href="{{ url('/admin') }}" class="hover:text-brand-gold transition">📊 Dashboard</a>
                <a href="{{ url('/admin/produtos') }}" class="text-brand-gold font-bold">🥐 Produtos & Estoque</a>
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

        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-brand-dark text-white flex justify-between items-center">
                <h1 class="font-black text-xl">🥐 Gerenciamento de Produtos & Estoque</h1>
                <span class="text-xs text-brand-gold font-bold">{{ $produtos->count() }} Salgados Cadastrados</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs">
                            <th class="py-3 px-6">Salgado</th>
                            <th class="py-3 px-6">Preço Unitário</th>
                            <th class="py-3 px-6">Estoque Atual</th>
                            <th class="py-3 px-6">Status</th>
                            <th class="py-3 px-6 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($produtos as $produto)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-6 font-bold flex items-center gap-3">
                                    <img src="{{ asset($produto->imagem) }}" alt="" class="w-10 h-10 object-cover rounded-lg">
                                    <div>
                                        <p class="text-brand-dark font-black">{{ $produto->nome }}</p>
                                        <p class="text-gray-400 text-xs font-normal">{{ $produto->slug }}</p>
                                    </div>
                                </td>
                                <form action="{{ url('/admin/produtos/' . $produto->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td class="py-3 px-6">
                                        <div class="flex items-center gap-1">
                                            <span class="text-gray-400">R$</span>
                                            <input type="number" step="0.01" name="preco_unitario" value="{{ $produto->preco_unitario }}" class="w-20 border rounded px-2 py-1 font-bold text-brand-red">
                                        </div>
                                    </td>
                                    <td class="py-3 px-6">
                                        <input type="number" name="estoque_atual" value="{{ $produto->estoque_atual }}" class="w-24 border rounded px-2 py-1 font-bold text-center">
                                        <span class="text-xs text-gray-400">un.</span>
                                    </td>
                                    <td class="py-3 px-6">
                                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold">
                                            <input type="checkbox" name="ativo" {{ $produto->ativo ? 'checked' : '' }} class="rounded text-brand-red">
                                            <span class="{{ $produto->ativo ? 'text-green-600' : 'text-red-500' }}">{{ $produto->ativo ? 'Ativo' : 'Inativo' }}</span>
                                        </label>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <button type="submit" class="bg-brand-red text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-red-700 transition shadow">
                                            Salvar
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach
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
