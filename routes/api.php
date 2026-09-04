<?php

use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProdutoController;
use Illuminate\Support\Facades\Route;

Route::get('/produtos', [ProdutoController::class, 'index']);
Route::post('/pedidos', [PedidoController::class, 'store']);
