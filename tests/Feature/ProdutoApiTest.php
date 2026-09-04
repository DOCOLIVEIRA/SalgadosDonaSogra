<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_listar_produtos_ativos_via_api(): void
    {
        Produto::create([
            'slug' => 'coxinha-teste',
            'nome' => 'Coxinha Teste',
            'descricao' => 'Descrição Teste',
            'preco_unitario' => 1.50,
            'estoque_atual' => 100,
            'ativo' => true,
        ]);

        $response = $this->getJson('/api/produtos');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'nome' => 'Coxinha Teste',
            ]);
    }
}
