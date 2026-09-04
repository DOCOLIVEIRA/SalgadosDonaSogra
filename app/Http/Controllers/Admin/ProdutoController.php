<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class ProdutoController extends Controller
{
    public function index(): View
    {
        $produtos = Produto::all();
        return view('admin.produtos', compact('produtos'));
    }

    public function updateEstoque(Request $request, Produto $produto): RedirectResponse
    {
        $request->validate([
            'estoque_atual' => 'required|integer|min:0',
            'preco_unitario' => 'required|numeric|min:0',
        ]);

        $produto->update([
            'estoque_atual' => $request->estoque_atual,
            'preco_unitario' => $request->preco_unitario,
            'ativo' => $request->has('ativo'),
        ]);

        return redirect('/admin/produtos')->with('success', 'Produto atualizado com sucesso!');
    }
}
