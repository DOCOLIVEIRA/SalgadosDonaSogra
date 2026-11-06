<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-sincronização de colunas legadas no banco (executa sem precisar de SSH)
        try {
            if (Schema::hasTable('pedidos') && !Schema::hasColumn('pedidos', 'created_at')) {
                Schema::table('pedidos', function ($table) {
                    $table->timestamps();
                });
                if (Schema::hasColumn('pedidos', 'criado_em')) {
                    DB::statement('UPDATE pedidos SET created_at = criado_em WHERE criado_em IS NOT NULL');
                }
            }

            if (Schema::hasTable('produtos') && !Schema::hasColumn('produtos', 'created_at')) {
                Schema::table('produtos', function ($table) {
                    $table->timestamps();
                });
            }

            if (Schema::hasTable('itens_pedido') && !Schema::hasColumn('itens_pedido', 'created_at')) {
                Schema::table('itens_pedido', function ($table) {
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Silencioso se o banco não estiver configurado ou em modo console
        }
    }
}
