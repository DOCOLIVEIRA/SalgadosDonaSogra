<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class CarrinhoController extends Controller
{
    public function index(): View
    {
        return view('carrinho');
    }
}
