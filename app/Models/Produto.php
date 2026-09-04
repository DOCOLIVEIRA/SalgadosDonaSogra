<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';

    protected $fillable = [
        'slug',
        'nome',
        'descricao',
        'preco_unitario',
        'estoque_atual',
        'imagem',
        'ativo',
    ];

    protected $casts = [
        'preco_unitario' => 'float',
        'estoque_atual' => 'integer',
        'ativo' => 'boolean',
    ];
}
