<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'nome_cliente',
        'telefone_cliente',
        'valor_total',
        'status',
        'cancelado_por_id',
        'cancelado_em',
    ];

    protected $casts = [
        'valor_total' => 'float',
        'cancelado_em' => 'datetime',
    ];

    public function itens()
    {
        return $this->hasMany(ItemPedido::class, 'pedido_id');
    }
}
