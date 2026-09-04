<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nome_cliente' => 'required|string|max:150',
            'telefone_cliente' => 'nullable|string|max:30',
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $valorTotal = 0;
            $itensProcessados = [];

            foreach ($request->itens as $item) {
                $produto = Produto::findOrFail($item['produto_id']);

                if ($produto->estoque_atual < $item['quantidade']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Estoque insuficiente para o produto: {$produto->nome}. Estoque disponível: {$produto->estoque_atual}",
                    ], 422);
                }

                $subtotal = $produto->preco_unitario * $item['quantidade'];
                $valorTotal += $subtotal;

                // Baixa temporária do estoque
                $produto->decrement('estoque_atual', $item['quantidade']);

                $itensProcessados[] = [
                    'produto_id' => $produto->id,
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $produto->preco_unitario,
                ];
            }

            $pedido = Pedido::create([
                'nome_cliente' => $request->nome_cliente,
                'telefone_cliente' => $request->telefone_cliente,
                'valor_total' => $valorTotal,
                'status' => 'Aguardando Confirmação',
            ]);

            foreach ($itensProcessados as $itemData) {
                $itemData['pedido_id'] = $pedido->id;
                ItemPedido::create($itemData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido criado com sucesso! Reservamos seu estoque.',
                'data' => [
                    'pedido_id' => $pedido->id,
                    'valor_total' => $pedido->valor_total,
                    'status' => $pedido->status,
                    'created_at' => $pedido->created_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar o pedido: ' . $e->getMessage(),
            ], 500);
        }
    }
}
