<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;

class ProdutoController extends Controller
{
    public function index(): JsonResponse
    {
        $produtos = Produto::where('ativo', true)->get();

        return response()->json([
            'success' => true,
            'data' => $produtos,
        ]);
    }
}
