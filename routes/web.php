<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProdutoController as AdminProdutoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendaController;
use App\Http\Controllers\CardapioController;
use App\Http\Controllers\CarrinhoController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CardapioController::class, 'index']);
Route::get('/cardapio', [CardapioController::class, 'index']);

Route::get('/carrinho', [CarrinhoController::class, 'index']);
Route::get('/evento', [EventoController::class, 'index']);

// Autenticação
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas do Painel Administrativo em Laravel (Protegidas)
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Gestão de Vendas / Pedidos
    Route::get('/vendas', [VendaController::class, 'index']);
    Route::patch('/vendas/{pedido}/status', [VendaController::class, 'updateStatus']);

    // Gestão de Produtos & Estoque
    Route::get('/produtos', [AdminProdutoController::class, 'index']);
    Route::put('/produtos/{produto}', [AdminProdutoController::class, 'updateEstoque']);

    // Gestão & Cadastro de Usuários
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy']);
});


