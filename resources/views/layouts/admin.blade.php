<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel Admin – Salgados Dona Sogra')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&display=swap" rel="stylesheet" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <span class="font-black text-xl text-brand-white tracking-wide">Painel Admin</span>
            </div>
            <nav class="flex items-center gap-4 text-sm font-semibold">
                <a href="{{ url('/admin') }}" class="hover:text-brand-gold transition {{ request()->is('admin') || request()->is('admin/dashboard') ? 'text-brand-gold font-bold' : '' }}">📊 Dashboard</a>
                <a href="{{ url('/admin/vendas') }}" class="hover:text-brand-gold transition {{ request()->is('admin/vendas') ? 'text-brand-gold font-bold' : '' }}">💰 Vendas</a>
                <a href="{{ url('/admin/produtos') }}" class="hover:text-brand-gold transition {{ request()->is('admin/produtos') ? 'text-brand-gold font-bold' : '' }}">🥐 Estoque</a>
                <a href="{{ url('/admin/usuarios') }}" class="hover:text-brand-gold transition {{ request()->is('admin/usuarios') ? 'text-brand-gold font-bold' : '' }}">👤 Usuários</a>
                <a href="{{ url('/') }}" target="_blank" class="text-xs bg-brand-gold text-brand-dark font-bold px-3 py-1.5 rounded-full hover:bg-yellow-300 transition">🌐 Loja Pública</a>
                
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs bg-red-600 text-white font-bold px-3 py-1.5 rounded-full hover:bg-red-700 transition">
                        🚪 Sair
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto w-full px-6 py-8 space-y-8">
        @yield('content')
    </main>

    <footer class="bg-brand-dark text-gray-400 text-xs text-center py-4 mt-auto">
        &copy; {{ date('Y') }} Salgados Dona Sogra – Painel Administrativo Laravel.
    </footer>

    @stack('scripts')
</body>
</html>
