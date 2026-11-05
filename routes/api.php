<?php

use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProdutoController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/produtos', [ProdutoController::class, 'index']);
Route::post('/pedidos', [PedidoController::class, 'store']);

Route::get('/check-db', function () {
    try {
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        return response()->json([
            'success' => true,
            'tables' => $tables,
        ]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/setup-admin', function () {
    try {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        $user = User::updateOrCreate(
            ['email' => 'admin@donasogra.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin@2026'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Tabela users e usuario Administrador configurados com sucesso!',
            'email' => $user->email,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});
