<?php

namespace Tests\Feature;

use App\Models\Cadastro;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditoriaCadastroTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $perfil, string $sufixo): Usuario
    {
        return Usuario::create([
            'nome' => 'Usuário Auditoria',
            'email' => "auditoria-{$sufixo}@teste.com",
            'senha' => Hash::make('senha123'),
            'cpf' => $sufixo === 'admin' ? '12345678921' : '12345678922',
            'perfil' => $perfil,
            'status' => true,
            'unidade' => 'DF',
            'area' => 'Portfólio',
            'telefone' => '11999999921',
        ]);
    }

    public function test_apenas_admin_lista_auditoria(): void
    {
        $admin = $this->usuario(Usuario::PERFIL_ADMINISTRADOR, 'admin');
        $editor = $this->usuario(Usuario::PERFIL_EDITOR, 'editor');

        Cadastro::query()->create([
            'usuario_id' => $editor->id,
            'acao' => 'criar',
            'modulo' => 'cursos',
            'resumo' => 'Cadastrou curso teste',
        ]);

        $this->actingAs($editor, 'sanctum');
        $this->getJson('/api/cadastros')->assertForbidden();

        $this->actingAs($admin, 'sanctum');
        $response = $this->getJson('/api/cadastros');
        $response->assertOk();
        $response->assertJsonPath('meta.total', 1);
        $response->assertJsonPath('data.0.resumo', 'Cadastrou curso teste');
    }

    public function test_admin_pode_ver_detalhe_da_auditoria(): void
    {
        $admin = $this->usuario(Usuario::PERFIL_ADMINISTRADOR, 'admin');
        $editor = $this->usuario(Usuario::PERFIL_EDITOR, 'editor');

        $evento = Cadastro::query()->create([
            'usuario_id' => $editor->id,
            'acao' => 'editar',
            'modulo' => 'cursos',
            'resumo' => 'Atualizou curso X',
            'dados' => ['id' => 99],
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/cadastros/{$evento->id}")
            ->assertOk()
            ->assertJsonPath('cadastro.acao', 'editar')
            ->assertJsonPath('cadastro.resumo', 'Atualizou curso X');
    }
}
