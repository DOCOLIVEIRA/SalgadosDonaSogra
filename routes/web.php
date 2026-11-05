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
use Illuminate\Support\Facades\Artisan;

// Rota para rodar as migrações e seed inicial sem precisar de SSH
Route::withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
])->get('/migrar-banco', function () {
    try {
        Artisan::call('migrate --force');
        $output = Artisan::output();
        Artisan::call('db:seed --force');
        $output .= "\n" . Artisan::output();
        return '<h2>Banco de dados configurado com sucesso!</h2><pre>' . htmlspecialchars($output) . '</pre><br><a href="/">Ir para o Cardápio</a>';
    } catch (\Throwable $e) {
        return '<h2>Erro ao conectar/migrar:</h2><pre>' . htmlspecialchars($e->getMessage()) . "\n\nArquivo: " . $e->getFile() . ':' . $e->getLine() . "\n\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
});

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


