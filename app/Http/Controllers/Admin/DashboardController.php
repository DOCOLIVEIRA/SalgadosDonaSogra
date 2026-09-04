<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalPedidos = Pedido::count();
        $pedidosPendentes = Pedido::where('status', 'Aguardando Confirmação')->orWhere('status', 'Pendente')->count();
        $faturamentoTotal = Pedido::where('status', '!=', 'Cancelado')->where('status', '!=', 'Expirado')->sum('valor_total');
        $totalProdutos = Produto::where('ativo', true)->count();
        
        $ultimosPedidos = Pedido::with('itens.produto')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalPedidos',
            'pedidosPendentes',
            'faturamentoTotal',
            'totalProdutos',
            'ultimosPedidos'
        ));
    }
}
