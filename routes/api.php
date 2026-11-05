<?php

use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProdutoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/migrar-banco', function () {
    try {
        Artisan::call('migrate --force');
        $output = Artisan::output();
        Artisan::call('db:seed --force');
        $output .= "\n" . Artisan::output();
        return response('<h2>Banco de dados configurado com sucesso!</h2><pre>' . htmlspecialchars($output) . '</pre><br><a href="/">Ir para o Cardápio</a>');
    } catch (\Throwable $e) {
        return response('<h2>Erro ao conectar/migrar:</h2><pre>' . htmlspecialchars($e->getMessage()) . "\n\nArquivo: " . $e->getFile() . ':' . $e->getLine() . "\n\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>', 500);
    }
});

Route::get('/produtos', [ProdutoController::class, 'index']);
Route::post('/pedidos', [PedidoController::class, 'store']);
