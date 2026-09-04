<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Usuários – Admin Dona Sogra</title>
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
                <span class="font-black text-xl text-brand-gold">Dona Sogra - Gestão de Usuários</span>
            </div>
            <nav class="flex gap-4 text-sm font-semibold">
                <a href="{{ url('/admin') }}" class="hover:text-brand-gold transition">📊 Dashboard</a>
                <a href="{{ url('/admin/vendas') }}" class="hover:text-brand-gold transition">💰 Vendas</a>
                <a href="{{ url('/admin/produtos') }}" class="hover:text-brand-gold transition">🥐 Estoque</a>
                <a href="{{ url('/admin/usuarios') }}" class="text-brand-gold font-bold">👤 Usuários</a>
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

        <div class="grid lg:grid-cols-[340px_1fr] gap-8">
            
            <!-- FORMULARIO DE CADASTRO -->
            <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                <h2 class="font-black text-lg text-brand-dark mb-4 border-b pb-2">➕ Cadastrar Novo Usuário</h2>
                <form action="{{ url('/admin/usuarios') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Nome Completo</label>
                        <input type="text" name="name" required placeholder="Ex: Maria Oliveira" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2 text-sm font-semibold focus:border-brand-red focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">E-mail</label>
                        <input type="email" name="email" required placeholder="admin@donasogra.com" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2 text-sm font-semibold focus:border-brand-red focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Senha</label>
                        <input type="password" name="password" required minlength="6" placeholder="******" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2 text-sm font-semibold focus:border-brand-red focus:outline-none">
                    </div>
                    <button type="submit" class="w-full bg-brand-red text-white font-bold py-3 rounded-xl hover:bg-red-700 transition shadow">
                        Cadastrar Usuário
                    </button>
                </form>
            </div>

            <!-- LISTA DE USUARIOS -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
                <div class="px-6 py-4 bg-brand-dark text-white flex justify-between items-center">
                    <h2 class="font-black text-lg">👥 Usuários Cadastrados no Sistema</h2>
                    <span class="text-xs text-brand-gold font-bold">{{ $users->count() }} Usuários</span>
                </div>
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs">
                            <th class="py-3 px-6">Nome</th>
                            <th class="py-3 px-6">E-mail</th>
                            <th class="py-3 px-6">Data de Cadastro</th>
                            <th class="py-3 px-6 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $u)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-6 font-bold text-brand-dark">{{ $u->name }}</td>
                                <td class="py-3 px-6 text-gray-600">{{ $u->email }}</td>
                                <td class="py-3 px-6 text-xs text-gray-400">{{ $u->created_at->format('d/m/Y') }}</td>
                                <td class="py-3 px-6 text-center">
                                    <form action="{{ url('/admin/usuarios/' . $u->id) }}" method="POST" onsubmit="return confirm('Deseja realmente remover este usuário?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-bold border border-red-200 px-3 py-1 rounded-lg hover:bg-red-50 transition">
                                            Remover
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
        &copy; {{ date('Y') }} Salgados Dona Sogra – Gestão de Usuários.
    </footer>

</body>
</html>
