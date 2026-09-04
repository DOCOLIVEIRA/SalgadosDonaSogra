<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProdutoController as AdminProdutoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendaController;
use App\Http\Controllers\CardapioController;
use App\Http\Controllers\CarrinhoController;
use App\Http\Controllers\EventoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CardapioController::class, 'index']);
Route::get('/cardapio', [CardapioController::class, 'index']);

Route::get('/carrinho', [CarrinhoController::class, 'index']);
Route::get('/evento', [EventoController::class, 'index']);

// Rotas do Painel Administrativo em Laravel
Route::get('/admin', [DashboardController::class, 'index']);
Route::get('/admin/dashboard', [DashboardController::class, 'index']);

// Gestão de Vendas / Pedidos
Route::get('/admin/vendas', [VendaController::class, 'index']);
Route::patch('/admin/vendas/{pedido}/status', [VendaController::class, 'updateStatus']);

// Gestão de Produtos & Estoque
Route::get('/admin/produtos', [AdminProdutoController::class, 'index']);
Route::put('/admin/produtos/{produto}', [AdminProdutoController::class, 'updateEstoque']);

// Gestão & Cadastro de Usuários
Route::get('/admin/usuarios', [UserController::class, 'index']);
Route::post('/admin/usuarios', [UserController::class, 'store']);
Route::delete('/admin/usuarios/{user}', [UserController::class, 'destroy']);

// Redirecionamentos de compatibilidade para links legados
Route::redirect('/cart.html', '/carrinho');
Route::redirect('/evento.html', '/evento');
Route::redirect('/index.html', '/');
Route::redirect('/admin/index.php', '/admin');
Route::redirect('/admin/produtos.php', '/admin/produtos');
Route::redirect('/admin/usuarios.php', '/admin/usuarios');
