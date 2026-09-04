<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class VendaController extends Controller
{
    public function index(): View
    {
        $pedidos = Pedido::with('itens.produto')->latest()->get();
        return view('admin.vendas', compact('pedidos'));
    }

    public function updateStatus(Request $request, Pedido $pedido): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $statusAnterior = $pedido->status;
        $novoStatus = $request->status;

        // Se o pedido for cancelado ou expirado manualmente, devolver estoque
        if (in_array($novoStatus, ['Cancelado', 'Expirado']) && !in_array($statusAnterior, ['Cancelado', 'Expirado'])) {
            foreach ($pedido->itens as $item) {
                Produto::where('id', $item->produto_id)->increment('estoque_atual', $item->quantidade);
            }
            $pedido->cancelado_em = now();
        }

        $pedido->update(['status' => $novoStatus]);

        return redirect('/admin/vendas')->with('success', "Status do pedido #{$pedido->id} alterado para {$novoStatus}!");
    }
}
