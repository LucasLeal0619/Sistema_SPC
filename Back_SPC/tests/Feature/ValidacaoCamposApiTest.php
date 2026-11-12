<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ValidacaoCamposApiTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $perfil, string $sufixo): Usuario
    {
        return Usuario::create([
            'nome' => 'Usuário Validação',
            'email' => "validacao-{$sufixo}@teste.com",
            'senha' => Hash::make('senha123'),
            'cpf' => $sufixo === 'admin' ? '52998224725' : '11144477735',
            'perfil' => $perfil,
            'status' => true,
            'unidade' => 'Asa Norte',
            'area' => 'Portfólio',
            'telefone' => '61999990099',
        ]);
    }

    public function test_usuario_rejeita_cpf_e_telefone_invalidos(): void
    {
        $this->actingAs($this->usuario(Usuario::PERFIL_ADMINISTRADOR, 'admin'), 'sanctum');

        $this->postJson('/api/usuarios', [
            'nome' => 'Teste CPF',
            'email' => 'cpf-invalido@teste.com',
            'senha' => 'senha123',
            'cpf' => '111.111.111-11',
            'perfil' => Usuario::PERFIL_EDITOR,
            'telefone' => '123',
        ])->assertStatus(422)->assertJsonValidationErrors(['cpf', 'telefone']);
    }

    public function test_curso_rejeita_carga_horaria_e_processo_sei_invalidos(): void
    {
        $this->actingAs($this->usuario(Usuario::PERFIL_EDITOR, 'editor'), 'sanctum');

        $this->postJson('/api/cursos', [
            'titulo' => 'Curso teste validação',
            'eixo' => 'Gastronomia e Turismo',
            'modalidade' => 'Qualificação Profissional',
            'status' => 'ATIVO',
            'codigo_sig' => 'SIG-VAL-001',
            'carga_horaria' => '800h',
            'processo_sei' => '@@@',
            'unidades_oferta' => ['Asa Norte'],
        ])->assertStatus(422)->assertJsonValidationErrors(['carga_horaria', 'processo_sei']);
    }

    public function test_plano_de_meta_rejeita_numero_sei_invalido(): void
    {
        $this->actingAs($this->usuario(Usuario::PERFIL_EDITOR, 'editor'), 'sanctum');

        $this->postJson('/api/plano-de-metas', [
            'segmento' => 'Infraestrutura',
            'curso' => 'Curso teste',
            'tipo' => 'QUALIFICAÇÃO',
            'numero_sei' => '@@@',
            'codigo_sig' => 'SIG-VAL-002',
            'mes_entrega' => 'Janeiro',
            'status' => 'PLANEJADO',
            'status_final' => 'PENDENTE',
            'ano' => 2026,
        ])->assertStatus(422)->assertJsonValidationErrors(['numero_sei']);
    }

    public function test_curso_por_eixo_rejeita_turmas_com_letras(): void
    {
        $this->actingAs($this->usuario(Usuario::PERFIL_EDITOR, 'editor'), 'sanctum');

        $this->postJson('/api/curso-por-eixos', [
            'curso' => 'Curso Eixo',
            'eixo' => 'Gastronomia',
            'ano' => '2026',
            'status' => 'Ativo',
            'turmas' => '2a',
            'alunos' => '10x',
        ])->assertStatus(422)->assertJsonValidationErrors(['turmas', 'alunos']);
    }

    public function test_pca_rejeita_carga_horaria_e_numero_sei_invalidos(): void
    {
        $this->actingAs($this->usuario(Usuario::PERFIL_EDITOR, 'editor'), 'sanctum');

        $this->postJson('/api/pcas', [
            'titulo' => 'PCA teste',
            'status' => 'Vigente',
            'numero_sei' => '@@@',
            'carga_horaria' => 'abc',
        ])->assertStatus(422)->assertJsonValidationErrors(['numero_sei', 'carga_horaria']);
    }

    public function test_portfolio_ciclo_rejeita_nome_longo_e_observacao_longa(): void
    {
        $this->actingAs($this->usuario(Usuario::PERFIL_EDITOR, 'editor'), 'sanctum');

        $this->postJson('/api/portfolio-ciclos', [
            'nome' => Str::repeat('X', 81),
            'observacao' => Str::repeat('O', 2001),
        ])->assertStatus(422)->assertJsonValidationErrors(['nome', 'observacao']);
    }

    public function test_usuario_rejeita_nome_longo(): void
    {
        $this->actingAs($this->usuario(Usuario::PERFIL_ADMINISTRADOR, 'admin'), 'sanctum');

        $this->postJson('/api/usuarios', [
            'nome' => Str::repeat('U', 101),
            'email' => 'nome-longo@teste.com',
            'senha' => 'senha123',
            'cpf' => '39053344705',
            'perfil' => Usuario::PERFIL_EDITOR,
            'telefone' => '61999990099',
        ])->assertStatus(422)->assertJsonValidationErrors(['nome']);
    }
}
