<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdutoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('produtos')->insertOrIgnore([
            ['slug' => 'coxinha-de-frango', 'nome' => 'Coxinha de Frango', 'descricao' => 'Massa crocante, recheio de frango desfiado temperado.', 'preco_unitario' => 0.70, 'estoque_atual' => 500, 'imagem' => 'img/coxinha.png', 'ativo' => true],
            ['slug' => 'coxinha-de-carne', 'nome' => 'Coxinha de Carne', 'descricao' => 'Coxinha frita com recheio de carne moída temperada.', 'preco_unitario' => 0.85, 'estoque_atual' => 500, 'imagem' => 'img/coxinha_de_carne.png', 'ativo' => true],
            ['slug' => 'kibe', 'nome' => 'Kibe', 'descricao' => 'Kibe tradicional, crocante por fora e suculento por dentro.', 'preco_unitario' => 0.70, 'estoque_atual' => 500, 'imagem' => 'img/kibe.png', 'ativo' => true],
            ['slug' => 'kibe-com-queijo', 'nome' => 'Kibolinha', 'descricao' => 'Kibe com queijo, crocante por fora com queijo derretido por dentro.', 'preco_unitario' => 0.85, 'estoque_atual' => 500, 'imagem' => 'img/kibolinha.png', 'ativo' => true],
            ['slug' => 'fataya', 'nome' => 'Fataya', 'descricao' => 'Massa com recheio cremoso de carne moída temperada.', 'preco_unitario' => 1.10, 'estoque_atual' => 500, 'imagem' => 'img/fataya.jpeg', 'ativo' => true],
            ['slug' => 'croquete-de-salsicha', 'nome' => 'Croquete de Salsicha', 'descricao' => 'Crocante por fora com recheio cremoso de salsicha por dentro.', 'preco_unitario' => 0.70, 'estoque_atual' => 500, 'imagem' => 'img/croquete_de_salsicha.png', 'ativo' => true],
            ['slug' => 'bolinha-de-queijo', 'nome' => 'Bolinha de Queijo', 'descricao' => 'Bolinhas crocantes com mozzarella derretida por dentro.', 'preco_unitario' => 0.80, 'estoque_atual' => 500, 'imagem' => 'img/bolinha_queijo.jpg', 'ativo' => true],
            ['slug' => 'bolinho-de-bacalhau', 'nome' => 'Bolinho de Bacalhau', 'descricao' => 'Crocante por fora com recheio cremoso de bacalhau por dentro.', 'preco_unitario' => 1.00, 'estoque_atual' => 500, 'imagem' => 'img/bolinho_de_bacalhau.jpg', 'ativo' => true],
            ['slug' => 'almofadinha-calabresa-queijo', 'nome' => 'Almofadinha de Calabresa e Queijo', 'descricao' => 'Crocante por fora com recheio cremoso de calabresa e queijo por dentro.', 'preco_unitario' => 0.80, 'estoque_atual' => 500, 'imagem' => 'img/almofadinha_calabresa_e_queijo.jpg', 'ativo' => true],
        ]);

        DB::table('configuracoes')->insertOrIgnore([
            ['chave' => 'min_qty', 'valor' => '50', 'descricao' => 'Quantidade Mínima de salgados no pedido'],
            ['chave' => 'step_qty_index', 'valor' => '50', 'descricao' => 'Intervalo dos botões +/- na página inicial'],
            ['chave' => 'step_qty_cart', 'valor' => '5', 'descricao' => 'Intervalo dos botões +/- no carrinho'],
        ]);
    }
}
