<?php

namespace Tests\Feature;

use App\Models\Ciclo;
use App\Models\Usuario;
use App\Models\Curso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CicloContextoApiTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): Usuario
    {
        return Usuario::create([
            'nome' => 'Editor Ciclo Contexto',
            'email' => 'editor-ciclo-contexto@teste.com',
            'senha' => Hash::make('senha123'),
            'cpf' => '12345678977',
            'perfil' => Usuario::PERFIL_EDITOR,
            'status' => true,
            'unidade' => 'DF',
            'area' => 'Portfolio',
            'telefone' => '61999990000',
        ]);
    }

    public function test_api_ciclos_lista_e_alias_legado_coincidem(): void
    {
        $this->actingAs($this->editor(), 'sanctum');

        $novo = $this->getJson('/api/ciclos');
        $legado = $this->getJson('/api/portfolio-ciclos');

        $novo->assertOk();
        $legado->assertOk();
        $this->assertSame($novo->json('data'), $legado->json('data'));
        $this->assertNotNull($novo->json('meta.ciclo_atual_id'));
    }

    public function test_header_de_ciclo_inexistente_retorna_422(): void
    {
        $this->actingAs($this->editor(), 'sanctum');

        $this->getJson('/api/dashboard', [
            'X-Sistema-SPC-Ciclo-Id' => '99999',
        ])->assertStatus(422);
    }

    public function test_dashboard_respeita_header_de_ciclo(): void
    {
        $this->actingAs($this->editor(), 'sanctum');

        $atual = Ciclo::atual();
        $outro = Ciclo::create([
            'nome' => '2030-2031',
            'atual' => false,
        ]);

        Curso::create([
            'titulo' => 'Curso ciclo atual',
            'status' => 'ATIVO',
            'ciclo_id' => $atual->id,
        ]);
        Curso::create([
            'titulo' => 'Curso outro ciclo',
            'status' => 'ATIVO',
            'ciclo_id' => $outro->id,
        ]);

        $response = $this->getJson('/api/dashboard', [
            'X-Sistema-SPC-Ciclo-Id' => (string) $outro->id,
        ]);
        $response->assertOk();
        $this->assertSame($outro->id, $response->json('data.meta.ciclo.id'));
        $this->assertSame(['Curso outro ciclo'], collect($response->json('data.cursos'))->pluck('titulo')->all());
    }

    public function test_ciclo_id_explicito_prevalece_sobre_header(): void
    {
        $this->actingAs($this->editor(), 'sanctum');

        $atual = Ciclo::atual();
        $outro = Ciclo::create([
            'nome' => '2032-2033',
            'atual' => false,
        ]);

        Curso::create([
            'titulo' => 'Curso ciclo atual',
            'status' => 'ATIVO',
            'ciclo_id' => $atual->id,
        ]);
        Curso::create([
            'titulo' => 'Curso outro ciclo',
            'status' => 'ATIVO',
            'ciclo_id' => $outro->id,
        ]);

        $response = $this->getJson('/api/dashboard?ciclo_id='.$outro->id, [
            'X-Sistema-SPC-Ciclo-Id' => (string) $atual->id,
        ]);

        $response->assertOk();
        $this->assertSame($outro->id, $response->json('data.meta.ciclo.id'));
        $this->assertSame(['Curso outro ciclo'], collect($response->json('data.cursos'))->pluck('titulo')->all());
    }
}
