<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('nome_cliente', 150);
            $table->string('telefone_cliente', 30)->nullable();
            $table->decimal('valor_total', 10, 2)->default(0.00);
            $table->enum('status', ['Aguardando Confirmação', 'Pendente', 'Em preparo', 'Pronto', 'Entregue', 'Cancelado', 'Expirado'])->default('Aguardando Confirmação');
            $table->foreignId('cancelado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelado_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
