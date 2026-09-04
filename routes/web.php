<?php

use App\Http\Controllers\CardapioController;
use App\Http\Controllers\CarrinhoController;
use App\Http\Controllers\EventoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CardapioController::class, 'index']);
Route::get('/cardapio', [CardapioController::class, 'index']);

// Rotas amigáveis do Laravel
Route::get('/carrinho', [CarrinhoController::class, 'index']);
Route::get('/evento', [EventoController::class, 'index']);

// Redirecionamentos de compatibilidade para arquivos legados (.html)
Route::redirect('/cart.html', '/carrinho');
Route::redirect('/evento.html', '/evento');
Route::redirect('/index.html', '/');
