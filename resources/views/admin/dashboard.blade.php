@extends('layouts.admin')
@section('title', 'Dashboard – Salgados Dona Sogra')

@section('content')
        <!-- 🚀 AÇÕES RÁPIDAS -->
        <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <h1 class="font-black text-2xl text-brand-dark">Dashboard Geral</h1>
            <div class="flex items-center gap-3">
                <a href="{{ url('/admin/vendas') }}" class="bg-brand-red text-white font-bold text-sm px-5 py-2.5 rounded-lg hover:bg-red-700 transition shadow flex items-center gap-2">
                    <span>➕ Nova Venda</span>
                </a>
                <a href="{{ url('/admin/produtos') }}" class="bg-brand-gold text-brand-dark font-bold text-sm px-5 py-2.5 rounded-lg hover:bg-yellow-500 transition shadow flex items-center gap-2">
                    <span>➕ Novo Produto</span>
                </a>
                <button onclick="window.print()" class="bg-gray-800 text-white font-bold text-sm px-5 py-2.5 rounded-lg hover:bg-gray-900 transition shadow flex items-center gap-2">
                    <span>🖨️ Imprimir Resumo</span>
                </button>
            </div>
        </div>
        <!-- 🔍 FILTRO POR DIA, MÊS E ANO -->
        <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
            <form action="{{ url('/admin') }}" method="GET" class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📅</span>
                    <h2 class="font-black text-lg text-brand-dark">Consulta de Vendas e Saída de Produtos</h2>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase">Dia</label>
                        <select name="dia" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-bold bg-gray-50 focus:outline-none focus:border-brand-red">
                            <option value="">Todos os Dias</option>
                            @for($d = 1; $d <= 31; $d++)
                                <option value="{{ sprintf('%02d', $d) }}" {{ $dia == sprintf('%02d', $d) ? 'selected' : '' }}>Dia {{ sprintf('%02d', $d) }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase">Mês</label>
                        <select name="mes" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-bold bg-gray-50 focus:outline-none focus:border-brand-red">
                            <option value="">Todos os Meses</option>
                            @foreach([1=>'Janeiro', 2=>'Fevereiro', 3=>'Março', 4=>'Abril', 5=>'Maio', 6=>'Junho', 7=>'Julho', 8=>'Agosto', 9=>'Setembro', 10=>'Outubro', 11=>'Novembro', 12=>'Dezembro'] as $mNum => $mNome)
                                <option value="{{ sprintf('%02d', $mNum) }}" {{ $mes == sprintf('%02d', $mNum) ? 'selected' : '' }}>{{ $mNome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase">Ano</label>
                        <select name="ano" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-bold bg-gray-50 focus:outline-none focus:border-brand-red">
                            @for($y = date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ $ano == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="flex items-end gap-2 pt-4">
                        <button type="submit" class="bg-brand-red text-white font-bold text-xs px-5 py-2 rounded-lg hover:bg-red-700 transition shadow">
                            🔍 Filtrar
                        </button>
                        <a href="{{ url('/admin') }}" class="bg-gray-100 text-gray-600 font-bold text-xs px-4 py-2 rounded-lg hover:bg-gray-200 transition">
                            Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- CARDS DE METRICAS PRINCIPAIS (COM FILTRO) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-brand-red relative overflow-hidden group hover:shadow-lg transition">
                <div class="absolute right-[-20px] top-[-20px] opacity-5 text-8xl group-hover:scale-110 transition duration-500">🛒</div>
                <p class="text-xs font-bold text-gray-400 uppercase">Pedidos no Período</p>
                <p class="font-black text-3xl text-brand-dark mt-2">{{ $totalPedidos }}</p>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-yellow-500 relative overflow-hidden group hover:shadow-lg transition">
                <div class="absolute right-[-20px] top-[-20px] opacity-5 text-8xl group-hover:scale-110 transition duration-500">⏳</div>
                <p class="text-xs font-bold text-gray-400 uppercase">Pedidos Pendentes</p>
                <p class="font-black text-3xl text-yellow-600 mt-2">{{ $pedidosPendentes }}</p>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-green-500 relative overflow-hidden group hover:shadow-lg transition flex flex-col justify-between">
                <div class="absolute right-[-20px] top-[-20px] opacity-5 text-8xl group-hover:scale-110 transition duration-500">💰</div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Faturamento no Período</p>
                    <p class="font-black text-3xl text-green-600 mt-1">R$ {{ number_format($faturamentoTotal, 2, ',', '.') }}</p>
                </div>
                @if($crescimentoFaturamento != 0)
                    <div class="mt-3 text-xs font-bold flex items-center gap-1 {{ $crescimentoFaturamento > 0 ? 'text-green-500 bg-green-50' : 'text-red-500 bg-red-50' }} px-2 py-1 rounded-md self-start">
                        <span>{{ $crescimentoFaturamento > 0 ? '↗' : '↘' }}</span>
                        <span>{{ number_format(abs($crescimentoFaturamento), 1, ',', '.') }}% vs período anterior</span>
                    </div>
                @else
                    <div class="mt-3 text-[10px] font-bold text-gray-400 uppercase bg-gray-50 px-2 py-1 rounded-md self-start">Sem variação</div>
                @endif
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-blue-500 relative overflow-hidden group hover:shadow-lg transition">
                <div class="absolute right-[-20px] top-[-20px] opacity-5 text-8xl group-hover:scale-110 transition duration-500">🥐</div>
                <p class="text-xs font-bold text-gray-400 uppercase">Produtos Ativos</p>
                <p class="font-black text-3xl text-blue-600 mt-2">{{ $totalProdutos }}</p>
            </div>
        </div>

        <!-- 🚨 ALERTAS E 🛒 ÚLTIMAS VENDAS -->
        <div class="grid lg:grid-cols-3 gap-6">
            
            <!-- ALERTAS DE ESTOQUE (1 Coluna) -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 flex flex-col">
                <div class="px-6 py-4 bg-red-600 text-white flex justify-between items-center">
                    <h3 class="font-black text-base flex items-center gap-2"><span>🚨</span> Estoque Crítico (< 20)</h3>
                    <span class="text-xs bg-white/20 px-2 py-1 rounded-full font-bold">{{ $produtosAlerta->count() }}</span>
                </div>
                <div class="p-4 flex-1 overflow-y-auto max-h-[300px]">
                    @if($produtosAlerta->count() > 0)
                        <ul class="space-y-3">
                            @foreach($produtosAlerta as $alerta)
                            <li class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                    <span class="font-bold text-brand-dark text-sm">{{ $alerta->nome }}</span>
                                </div>
                                <span class="font-black text-red-600 text-sm bg-red-200 px-2 py-1 rounded-md">{{ $alerta->estoque_atual }} un.</span>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="flex flex-col items-center justify-center h-full text-gray-400 py-8">
                            <span class="text-4xl mb-2">✅</span>
                            <p class="font-bold text-sm">Estoque Saudável!</p>
                            <p class="text-xs text-center mt-1">Nenhum produto com menos de 20 unidades.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ÚLTIMOS PEDIDOS (2 Colunas) -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 flex flex-col">
                <div class="px-6 py-4 bg-brand-dark text-white flex justify-between items-center">
                    <h3 class="font-black text-base flex items-center gap-2"><span>🛒</span> Últimas Vendas Realizadas</h3>
                    <a href="{{ url('/admin/vendas') }}" class="text-xs text-brand-gold hover:underline font-bold">Ver Todas &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs">
                                <th class="py-3 px-6">ID</th>
                                <th class="py-3 px-6">Cliente</th>
                                <th class="py-3 px-6">Valor</th>
                                <th class="py-3 px-6 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ultimosPedidos as $pedido)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-6 font-bold text-gray-400">#{{ str_pad($pedido->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td class="py-3 px-6 font-bold text-brand-dark">{{ $pedido->cliente_nome }}</td>
                                    <td class="py-3 px-6 font-black text-brand-red">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</td>
                                    <td class="py-3 px-6 text-center">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                            @if($pedido->status == 'Entregue' || $pedido->status == 'Concluído') bg-green-100 text-green-700
                                            @elseif($pedido->status == 'Cancelado') bg-red-100 text-red-700
                                            @elseif($pedido->status == 'Pronto para Retirada' || $pedido->status == 'Saiu para Entrega') bg-blue-100 text-blue-700
                                            @else bg-yellow-100 text-yellow-700 @endif">
                                            {{ $pedido->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-400 font-semibold">Nenhuma venda registrada recentemente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 📦 RELATÓRIO DE SAÍDA DE PRODUTOS NO PERÍODO -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-brand-dark text-white flex justify-between items-center">
                <h3 class="font-black text-lg">🥐 Relatório de Saída de Produtos (Período Selecionado)</h3>
                <span class="text-xs text-brand-gold font-bold">{{ $saidaProdutos->count() }} Produtos Vendidos</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs">
                            <th class="py-3 px-6">Produto</th>
                            <th class="py-3 px-6 text-center">Quantidade Saída (Unidades)</th>
                            <th class="py-3 px-6">Faturamento Gerado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($saidaProdutos as $saida)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-6 font-bold text-brand-dark">{{ $saida->produto->nome ?? 'Produto Removido' }}</td>
                                <td class="py-3 px-6 text-center font-black text-brand-red text-base">{{ $saida->total_saida }} un.</td>
                                <td class="py-3 px-6 font-bold text-green-600">R$ {{ number_format($saida->faturamento_item, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-400 font-semibold">Nenhuma saída de produto registrada neste período.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 📊 GRAFICOS DE VENDAS E ESTOQUE -->
        <div class="grid lg:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                <h3 class="font-black text-lg text-brand-dark mb-4 border-b pb-2">📈 Evolução de Vendas no Período</h3>
                <div class="h-64">
                    <canvas id="vendasChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                <h3 class="font-black text-lg text-brand-dark mb-4 border-b pb-2">🥐 Nível do Estoque Atual</h3>
                <div class="h-64">
                    <canvas id="estoqueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 🏆 ANÁLISE DE CURVA ABC NO PERÍODO -->
        <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-brand-dark text-white flex justify-between items-center">
                <div>
                    <h3 class="font-black text-lg">🏆 Análise Curva ABC (Ranking no Período)</h3>
                    <p class="text-xs text-brand-gold">Classificação de relevância de faturamento</p>
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
                                <td colspan="5" class="py-6 text-center text-gray-400 font-semibold">Nenhum dado acumulado para Curva ABC neste período.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

@endsection

@push('scripts')
    <script>
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
@endpush
