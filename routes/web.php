<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProdutoController as AdminProdutoController;
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

// Gestão de Produtos & Estoque no Admin
Route::get('/admin/produtos', [AdminProdutoController::class, 'index']);
Route::put('/admin/produtos/{produto}', [AdminProdutoController::class, 'updateEstoque']);

// Redirecionamentos de compatibilidade para links legados
Route::redirect('/cart.html', '/carrinho');
Route::redirect('/evento.html', '/evento');
Route::redirect('/index.html', '/');
Route::redirect('/admin/index.php', '/admin');
Route::redirect('/admin/produtos.php', '/admin/produtos');
