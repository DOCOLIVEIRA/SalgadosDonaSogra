<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalPedidos = Pedido::count();
        $pedidosPendentes = Pedido::where('status', 'Aguardando Confirmação')->orWhere('status', 'Pendente')->count();
        $faturamentoTotal = Pedido::whereNotIn('status', ['Cancelado', 'Expirado'])->sum('valor_total');
        $totalProdutos = Produto::where('ativo', true)->count();
        
        $ultimosPedidos = Pedido::with('itens.produto')
            ->latest()
            ->take(5)
            ->get();

        // 📊 DADOS PARA O GRÁFICO DE VENDAS (Por dia)
        $vendasPorDia = Pedido::select(
            DB::raw('DATE(created_at) as data'),
            DB::raw('SUM(valor_total) as total')
        )
        ->whereNotIn('status', ['Cancelado', 'Expirado'])
        ->groupBy('data')
        ->orderBy('data', 'ASC')
        ->take(7)
        ->get();

        $diasLabels = $vendasPorDia->pluck('data')->map(fn($d) => date('d/m', strtotime($d)))->toArray();
        $vendasValores = $vendasPorDia->pluck('total')->toArray();

        // 🥐 DADOS PARA O GRÁFICO DE ESTOQUE
        $produtosEstoque = Produto::where('ativo', true)->get();
        $estoqueLabels = $produtosEstoque->pluck('nome')->toArray();
        $estoqueValores = $produtosEstoque->pluck('estoque_atual')->toArray();

        // 📈 ANÁLISE DE CURVA ABC (Ranking de Vendas por Faturamento)
        $rankingProdutos = ItemPedido::select(
            'produto_id',
            DB::raw('SUM(quantidade) as total_unidades'),
            DB::raw('SUM(quantidade * preco_unitario) as faturamento_total')
        )
        ->groupBy('produto_id')
        ->orderBy('faturamento_total', 'DESC')
        ->with('produto')
        ->get();

        $faturamentoGeral = $rankingProdutos->sum('faturamento_total') ?: 1;
        $acumulado = 0;

        $curvaABC = $rankingProdutos->map(function ($item) use ($faturamentoGeral, &$acumulado) {
            $percentual = ($item->faturamento_total / $faturamentoGeral) * 100;
            $acumulado += $percentual;

            $classe = 'C';
            if ($acumulado <= 80) {
                $classe = 'A';
            } elseif ($acumulado <= 95) {
                $classe = 'B';
            }

            return [
                'produto' => $item->produto->nome ?? 'Produto Removido',
                'unidades' => $item->total_unidades,
                'faturamento' => $item->faturamento_total,
                'percentual' => round($percentual, 1),
                'classe' => $classe,
            ];
        });

        return view('admin.dashboard', compact(
            'totalPedidos',
            'pedidosPendentes',
            'faturamentoTotal',
            'totalProdutos',
            'ultimosPedidos',
            'diasLabels',
            'vendasValores',
            'estoqueLabels',
            'estoqueValores',
            'curvaABC'
        ));
    }
}
