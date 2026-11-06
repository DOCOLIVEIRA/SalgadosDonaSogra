<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $dia = $request->input('dia');
        $mes = $request->input('mes');
        $ano = $request->input('ano', date('Y'));

        // Identifica coluna de data (created_at ou criado_em)
        $colData = Schema::hasColumn('pedidos', 'created_at') ? 'created_at' : (Schema::hasColumn('pedidos', 'criado_em') ? 'criado_em' : 'id');

        // Query Base de Pedidos Válidos
        $queryPedidos = Pedido::whereNotIn('status', ['Cancelado', 'Expirado']);

        if ($ano) {
            $queryPedidos->whereYear($colData, $ano);
        }
        if ($mes) {
            $queryPedidos->whereMonth($colData, $mes);
        }
        if ($dia) {
            $queryPedidos->whereDay($colData, $dia);
        }

        $totalPedidos = (clone $queryPedidos)->count();
        $faturamentoTotal = (clone $queryPedidos)->sum('valor_total');
        $pedidosPendentes = Pedido::whereIn('status', ['Aguardando Confirmação', 'Pendente'])->count();
        $totalProdutos = Produto::where('ativo', true)->count();

        // 📉 Cálculo de Crescimento do Faturamento (Comparação com período anterior)
        $faturamentoAnterior = 0;
        if ($mes && $ano) {
            $mesAnterior = $mes - 1;
            $anoAnterior = $ano;
            if ($mesAnterior == 0) {
                $mesAnterior = 12;
                $anoAnterior--;
            }
            $faturamentoAnterior = Pedido::whereNotIn('status', ['Cancelado', 'Expirado'])
                ->whereMonth($colData, $mesAnterior)
                ->whereYear($colData, $anoAnterior)
                ->sum('valor_total');
        } else {
            $faturamentoAnterior = Pedido::whereNotIn('status', ['Cancelado', 'Expirado'])
                ->whereBetween($colData, [Carbon::now()->subDays(60), Carbon::now()->subDays(30)])
                ->sum('valor_total');
        }

        $crescimentoFaturamento = 0;
        if ($faturamentoAnterior > 0) {
            $crescimentoFaturamento = (($faturamentoTotal - $faturamentoAnterior) / $faturamentoAnterior) * 100;
        } elseif ($faturamentoTotal > 0) {
            $crescimentoFaturamento = 100;
        }

        // 🚨 Produtos com Estoque Crítico (Menos de 20)
        $produtosAlerta = Produto::where('ativo', true)->where('estoque_atual', '<', 20)->orderBy('estoque_atual', 'ASC')->get();

        // 📊 DADOS PARA O GRÁFICO DE VENDAS
        $vendasQuery = Pedido::select(
            DB::raw("DATE($colData) as data"),
            DB::raw('SUM(valor_total) as total')
        )
        ->whereNotIn('status', ['Cancelado', 'Expirado']);

        if ($ano) $vendasQuery->whereYear($colData, $ano);
        if ($mes) $vendasQuery->whereMonth($colData, $mes);
        if ($dia) $vendasQuery->whereDay($colData, $dia);

        $vendasPorDia = $vendasQuery->groupBy('data')->orderBy('data', 'ASC')->get();
        $diasLabels = $vendasPorDia->pluck('data')->map(fn($d) => date('d/m/Y', strtotime($d)))->toArray();
        $vendasValores = $vendasPorDia->pluck('total')->toArray();

        // 🥐 DADOS DE SAÍDA DE PRODUTOS NO PERÍODO FILTRADO
        $saidaProdutosQuery = ItemPedido::select(
            'produto_id',
            DB::raw('SUM(quantidade) as total_saida'),
            DB::raw('SUM(quantidade * preco_unitario) as faturamento_item')
        )
        ->whereHas('pedido', function ($q) use ($colData, $ano, $mes, $dia) {
            $q->whereNotIn('status', ['Cancelado', 'Expirado']);
            if ($ano) $q->whereYear($colData, $ano);
            if ($mes) $q->whereMonth($colData, $mes);
            if ($dia) $q->whereDay($colData, $dia);
        })
        ->groupBy('produto_id')
        ->orderBy('total_saida', 'DESC')
        ->with('produto');

        $saidaProdutos = $saidaProdutosQuery->get();

        // 📈 ANÁLISE DE CURVA ABC NO PERÍODO
        $faturamentoGeral = $saidaProdutos->sum('faturamento_item') ?: 1;
        $acumulado = 0;

        $curvaABC = $saidaProdutos->map(function ($item) use ($faturamentoGeral, &$acumulado) {
            $percentual = ($item->faturamento_item / $faturamentoGeral) * 100;
            $acumulado += $percentual;

            $classe = 'C';
            if ($acumulado <= 80) {
                $classe = 'A';
            } elseif ($acumulado <= 95) {
                $classe = 'B';
            }

            return [
                'produto' => $item->produto->nome ?? 'Produto Removido',
                'unidades' => $item->total_saida,
                'faturamento' => $item->faturamento_item,
                'percentual' => round($percentual, 1),
                'classe' => $classe,
            ];
        });

        // Gráfico de Estoque Geral
        $produtosEstoque = Produto::where('ativo', true)->get();
        $estoqueLabels = $produtosEstoque->pluck('nome')->toArray();
        $estoqueValores = $produtosEstoque->pluck('estoque_atual')->toArray();

        $ultimosPedidos = Pedido::with('itens.produto')->orderBy($colData, 'desc')->take(5)->get();

        return view('admin.dashboard', compact(
            'totalPedidos',
            'pedidosPendentes',
            'faturamentoTotal',
            'totalProdutos',
            'crescimentoFaturamento',
            'produtosAlerta',
            'ultimosPedidos',
            'diasLabels',
            'vendasValores',
            'estoqueLabels',
            'estoqueValores',
            'curvaABC',
            'saidaProdutos',
            'dia',
            'mes',
            'ano'
        ));
    }
}
