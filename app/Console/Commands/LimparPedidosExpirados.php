<?php

namespace App\Console\Commands;

use App\Models\Pedido;
use App\Models\Produto;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimparPedidosExpirados extends Command
{
    protected $signature = 'pedidos:limpar-expirados {--horas=24 : Horas limite de inatividade}';
    protected $description = 'Cancela pedidos inativos/não confirmados e devolve os itens ao estoque';

    public function handle(): int
    {
        $horas = (int) $this->option('horas');
        $dataLimite = Carbon::now()->subHours($horas);

        $pedidosExpirados = Pedido::with('itens')
            ->where('status', 'Aguardando Confirmação')
            ->where('created_at', '<=', $dataLimite)
            ->get();

        if ($pedidosExpirados->isEmpty()) {
            $this->info("Nenhum pedido expirado encontrado (limite de {$horas}h).");
            return Command::SUCCESS;
        }

        $totalCancelados = 0;

        foreach ($pedidosExpirados as $pedido) {
            DB::transaction(function () use ($pedido) {
                // Devolve itens ao estoque
                foreach ($pedido->itens as $item) {
                    Produto::where('id', $item->produto_id)->increment('estoque_atual', $item->quantidade);
                }

                // Atualiza status do pedido
                $pedido->update([
                    'status' => 'Expirado',
                    'cancelado_em' => Carbon::now(),
                ]);
            });

            $totalCancelados++;
        }

        $this->info("{$totalCancelados} pedido(s) expirado(s) cancelado(s) e estoque devolvido com sucesso!");
        return Command::SUCCESS;
    }
}
