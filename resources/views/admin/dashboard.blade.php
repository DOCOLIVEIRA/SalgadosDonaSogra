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
                <span class="font-black text-xl text-brand-gold tracking-wide">Dona Sogra - Painel Admin</span>
            </div>
            <nav class="flex items-center gap-4 text-sm font-semibold">
                <a href="{{ url('/admin') }}" class="text-brand-gold font-bold">📊 Dashboard</a>
                <a href="{{ url('/admin/vendas') }}" class="hover:text-brand-gold transition">💰 Vendas</a>
                <a href="{{ url('/admin/produtos') }}" class="hover:text-brand-gold transition">🥐 Estoque</a>
                <a href="{{ url('/admin/usuarios') }}" class="hover:text-brand-gold transition">👤 Usuários</a>
                <a href="{{ url('/') }}" target="_blank" class="text-xs bg-brand-gold text-brand-dark font-bold px-3 py-1.5 rounded-full hover:bg-yellow-300 transition">🌐 Loja Pública</a>
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto w-full px-6 py-8 space-y-8">
        
        <!-- CARDS DE METRICAS PRINCIPAIS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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

        <!-- 📊 GRAFICOS DE VENDAS E ESTOQUE -->
        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Grafico 1: Vendas por Dia -->
            <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                <h3 class="font-black text-lg text-brand-dark mb-4 border-b pb-2">📈 Evolução de Vendas (Diária)</h3>
                <div class="h-64">
                    <canvas id="vendasChart"></canvas>
                </div>
            </div>

            <!-- Grafico 2: Estoque por Produto -->
            <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                <h3 class="font-black text-lg text-brand-dark mb-4 border-b pb-2">🥐 Nível do Estoque Atual</h3>
                <div class="h-64">
                    <canvas id="estoqueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 🏆 ANÁLISE DE CURVA ABC (CLASSIFICAÇÃO DE PRODUTOS) -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-brand-dark text-white flex justify-between items-center">
                <div>
                    <h3 class="font-black text-lg">🏆 Análise Curva ABC (Ranking de Vendas)</h3>
                    <p class="text-xs text-brand-gold">Classificação por impacto no faturamento geral</p>
                </div>
                <span class="text-xs bg-white/10 px-3 py-1 rounded-full font-bold">Método ABC</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs">
                            <th class="py-3 px-6">Classe</th>
                            <th class="py-3 px-6">Produto</th>
                            <th class="py-3 px-6 text-center">Unidades Vendidas</th>
                            <th class="py-3 px-6">Faturamento Acumulado</th>
                            <th class="py-3 px-6">% do Faturamento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($curvaABC as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-6">
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs text-white
                                        @if($item['classe'] == 'A') bg-green-600
                                        @elseif($item['classe'] == 'B') bg-yellow-500
                                        @else bg-gray-400 @endif">
                                        {{ $item['classe'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 font-bold text-brand-dark">{{ $item['produto'] }}</td>
                                <td class="py-3 px-6 text-center font-semibold">{{ $item['unidades'] }} un.</td>
                                <td class="py-3 px-6 font-bold text-brand-red">R$ {{ number_format($item['faturamento'], 2, ',', '.') }}</td>
                                <td class="py-3 px-6 font-bold text-gray-600">{{ $item['percentual'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400 font-semibold">Nenhum dado acumulado para Curva ABC ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="bg-brand-dark text-gray-400 text-xs text-center py-4 mt-auto">
        &copy; {{ date('Y') }} Salgados Dona Sogra – Painel Administrativo Laravel.
    </footer>

    <!-- SCRIPT DE GRAFICOS CHART.JS -->
    <script>
        // Gráfico de Vendas
        const ctxVendas = document.getElementById('vendasChart').getContext('2d');
        new Chart(ctxVendas, {
            type: 'line',
            data: {
                labels: {!! json_encode($diasLabels) !!},
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: {!! json_encode($vendasValores) !!},
                    borderColor: '#C0392B',
                    backgroundColor: 'rgba(192, 57, 43, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // Gráfico de Estoque
        const ctxEstoque = document.getElementById('estoqueChart').getContext('2d');
        new Chart(ctxEstoque, {
            type: 'bar',
            data: {
                labels: {!! json_encode($estoqueLabels) !!},
                datasets: [{
                    label: 'Estoque Atual (un.)',
                    data: {!! json_encode($estoqueValores) !!},
                    backgroundColor: '#F0A500',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>
