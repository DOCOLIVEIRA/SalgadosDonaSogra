<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Salgados Dona Sogra</title>
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
                        }
                    },
                    fontFamily: { sans: ['Outfit', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans text-brand-dark min-h-screen flex items-center justify-center relative overflow-hidden">
    
    <!-- Background Decorativo -->
    <div class="absolute top-[-100px] left-[-100px] w-96 h-96 bg-brand-red rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
    <div class="absolute bottom-[-100px] right-[-100px] w-96 h-96 bg-brand-gold rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>

    <div class="w-full max-w-md bg-white p-8 rounded-3xl shadow-2xl relative z-10 border border-gray-100">
        <div class="flex justify-center mb-6">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="h-20 w-auto">
        </div>
        
        <h2 class="text-3xl font-black text-center text-brand-dark mb-2">Painel Administrativo</h2>
        <p class="text-center text-gray-500 font-semibold mb-8 text-sm">Insira suas credenciais para acessar</p>

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-brand-red p-4 mb-6 rounded-lg">
                <p class="text-brand-red text-sm font-bold">{{ $errors->first() }}</p>
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST" class="space-y-5">
            @csrf
            
            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-1">E-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-transparent transition text-sm bg-gray-50 font-medium placeholder-gray-400"
                    placeholder="admin@donasogra.com.br">
            </div>

            <div>
                <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Senha</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-transparent transition text-sm bg-gray-50 font-medium placeholder-gray-400"
                    placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-brand-dark text-brand-gold font-black text-lg py-3 rounded-xl hover:bg-gray-900 transition shadow-lg mt-4">
                Entrar no Painel
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="{{ url('/') }}" class="text-xs text-gray-400 hover:text-brand-red font-semibold transition">&larr; Voltar para a loja pública</a>
        </div>
    </div>
</body>
</html>
