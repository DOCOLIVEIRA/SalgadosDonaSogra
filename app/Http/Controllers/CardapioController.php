<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Contracts\View\View;

class CardapioController extends Controller
{
    public function index(): View
    {
        $produtos = Produto::where('ativo', true)->get();

        return view('cardapio', compact('produtos'));
    }
}
