<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\PlanoDeMeta;
use App\Models\PortfolioCiclo;
use App\Models\RegiaoAdministrativa;
use App\Models\UnidadeOferta;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortfolioCicloEJornadaApiTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): Usuario
    {
        return Usuario::create([
            'nome' => 'Editor Ciclos',
            'email' => 'editor-ciclos@teste.com',
            'senha' => Hash::make('senha123'),
            'cpf' => '12345678021',
            'perfil' => Usuario::PERFIL_EDITOR,
            'status' => true,
            'unidade' => 'Asa Norte',
            'area' => 'Portfólio',
            'telefone' => '61999990021',
        ]);
    }

    private function consultor(): Usuario
    {
        return Usuario::create([
            'nome' => 'Consultor Ciclos',
            'email' => 'consultor-ciclos@teste.com',
            'senha' => Hash::make('senha123'),
            'cpf' => '12345678022',
            'perfil' => Usuario::PERFIL_CONSULTOR,
            'status' => true,
            'unidade' => 'Asa Norte',
            'area' => 'Portfólio',
            'telefone' => '61999990022',
        ]);
    }

    public function test_cria_curso_semelhante_somente_com_justificativa(): void
    {
        $this->actingAs($this->editor(), 'sanctum');

        $regiao = RegiaoAdministrativa::query()->firstOrCreate(['nome' => 'Asa Norte'], ['ativo' => true]);
        UnidadeOferta::query()->firstOrCreate(
            ['nome' => 'Asa Norte'],
            ['tipo' => UnidadeOferta::TIPO_UNIDADE, 'ativo' => true, 'regiao_administrativa_id' => $regiao->id],
        );

        $payload = [
            'ciclo_id' => PortfolioCiclo::atual()?->id,
            'titulo' => 'Curso duplicado',
            'eixo' => 'Saúde',
            'segmento' => 'Enfermagem',
            'programa' => '60+',
            'modalidade' => 'Qualificação Profissional',
            'status' => 'ATIVO',
            'codigo_sig' => 'SIG-DUP-001',
            'codigo_dn' => 'DN-DUP-001',
            'processo_sei' => '2026.SEI.001',
            'carga_horaria' => '40',
            'turmas' => '1',
            'codigo_processo' => 'PROC-DUP-001',
            'alunos' => '10',
            'instrutor' => 'Instrutor Teste',
            'descricao' => 'Descrição de teste.',
            'identificacao' => '2026',
            'ultima_revisao' => '2026',
            'data_inicio' => '2026-01-01',
            'data_fim' => '2026-12-31',
            'unidade' => 'Asa Norte',
            'unidades_oferta' => ['Asa Norte'],
            'observacoes' => 'Observações de teste.',
            'valores' => '0',
            'compativel_bolsa' => 'NÃO',
            'comercial' => 'NÃO',
            'pcn' => 'PCN de teste.',
            'pcr' => 'PCR de teste.',
        ];

        $this->postJson('/api/cursos', $payload)->assertCreated();

        $bloqueado = $this->postJson('/api/cursos', $payload);
        $bloqueado->assertStatus(409);
        $bloqueado->assertJsonPath('duplicidade', true);
        $bloqueado->assertJsonPath('exige_justificativa', true);

        $liberado = $this->postJson('/api/cursos', [
            ...$payload,
            'justificativa_duplicidade' => 'Turma extra autorizada pela coordenação.',
        ]);
        $liberado->assertCreated();
        $liberado->assertJsonPath('curso.justificativa_duplicidade', 'Turma extra autorizada pela coordenação.');
    }

    public function test_gerar_proximo_portfolio_copia_cursos_do_ciclo_origem(): void
    {
        $this->actingAs($this->editor(), 'sanctum');

        $origem = PortfolioCiclo::atual();
        $this->assertNotNull($origem);

        Curso::create([
            'titulo' => 'Curso origem',
            'eixo' => 'Saúde',
            'status' => 'ATIVO',
            'codigo_sig' => 'SIG-CICLO-1',
            'ciclo_id' => $origem->id,
        ]);

        $response = $this->postJson('/api/portfolio-ciclos/gerar-proximo', [
            'origem_id' => $origem->id,
            'nome' => '2028',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('ciclo.nome', '2028');
        $response->assertJsonPath('cursos_copiados', 1);
        $this->assertSame(1, (int) $response->json('ciclo.cursos_count'));

        $novoId = $response->json('ciclo.id');
        $this->assertDatabaseHas('cursos', [
            'titulo' => 'Curso origem',
            'codigo_sig' => 'SIG-CICLO-1',
            'ciclo_id' => $novoId,
        ]);
        $this->assertDatabaseHas('cursos', [
            'titulo' => 'Curso origem',
            'ciclo_id' => $origem->id,
        ]);

        $filtrado = $this->getJson('/api/cursos?ciclo_id='.$novoId);
        $filtrado->assertOk();
        $filtrado->assertJsonPath('meta.total', 1);
        $filtrado->assertJsonPath('data.0.titulo', 'Curso origem');
    }

    public function test_gerar_proximo_sem_copiar_cursos(): void
    {
        $this->actingAs($this->editor(), 'sanctum');

        $origem = PortfolioCiclo::atual();
        $this->assertNotNull($origem);

        Curso::create([
            'titulo' => 'Curso origem sem copia',
            'eixo' => 'Saúde',
            'status' => 'ATIVO',
            'codigo_sig' => 'SIG-CICLO-SEM',
            'ciclo_id' => $origem->id,
        ]);

        $response = $this->postJson('/api/portfolio-ciclos/gerar-proximo', [
            'origem_id' => $origem->id,
            'nome' => '2031',
            'copiar_cursos' => false,
            'marcar_atual' => false,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('ciclo.nome', '2031');
        $response->assertJsonPath('cursos_copiados', 0);
        $this->assertSame(0, (int) $response->json('ciclo.cursos_count'));

        $novoId = $response->json('ciclo.id');
        $this->assertDatabaseMissing('cursos', [
            'titulo' => 'Curso origem sem copia',
            'ciclo_id' => $novoId,
        ]);
    }

    public function test_excluir_ciclo_com_registros_e_bloqueado(): void
    {
        $this->actingAs($this->editor(), 'sanctum');

        $origem = PortfolioCiclo::atual();
        $this->assertNotNull($origem);

        $ciclo = PortfolioCiclo::create([
            'nome' => '2032-teste',
            'origem_id' => $origem->id,
            'atual' => false,
            'observacao' => 'Ciclo para exclusão',
        ]);

        Curso::create([
            'titulo' => 'Curso do ciclo teste',
            'eixo' => 'Saúde',
            'status' => 'ATIVO',
            'codigo_sig' => 'SIG-DEL-1',
            'ciclo_id' => $ciclo->id,
        ]);

        $bloqueado = $this->deleteJson("/api/portfolio-ciclos/{$ciclo->id}");
        $bloqueado->assertStatus(422);
        $this->assertDatabaseHas('portfolio_ciclos', ['id' => $ciclo->id]);
        $this->assertDatabaseHas('cursos', ['codigo_sig' => 'SIG-DEL-1', 'ciclo_id' => $ciclo->id]);

        $forcar = $this->deleteJson("/api/portfolio-ciclos/{$ciclo->id}?limpar_registros=1");
        $forcar->assertStatus(422);
        $this->assertDatabaseHas('portfolio_ciclos', ['id' => $ciclo->id]);
        $this->assertDatabaseHas('cursos', ['codigo_sig' => 'SIG-DEL-1']);
    }

    public function test_ciclo_novo_nao_herda_cursos_nem_metas_de_outro_ano(): void
    {
        $this->actingAs($this->editor(), 'sanctum');

        $origem = PortfolioCiclo::atual();
        $this->assertNotNull($origem);

        Curso::create([
            'titulo' => 'Curso do ciclo atual',
            'eixo' => 'Saúde',
            'status' => 'ATIVO',
            'codigo_sig' => 'SIG-CICLO-ATUAL',
            'ciclo_id' => $origem->id,
        ]);

        PlanoDeMeta::create([
            'segmento' => 'Pedagógico',
            'curso' => 'Meta 2026',
            'tipo' => 'QUALIFICAÇÃO',
            'numero_sei' => 'SEI-CICLO-01',
            'codigo_sig' => 'SIG-META-01',
            'mes_entrega' => 'Janeiro',
            'status' => 'PLANEJADO',
            'status_final' => 'PENDENTE',
            'ano' => 2026,
        ]);

        $response = $this->postJson('/api/portfolio-ciclos', [
            'nome' => '2027',
            'atual' => false,
        ]);

        $response->assertCreated();
        $this->assertSame(0, (int) $response->json('ciclo.cursos_count'));
        $this->assertSame(0, (int) $response->json('ciclo.composicao.plano_de_metas'));

        $novoId = $response->json('ciclo.id');

        $this->getJson('/api/cursos?ciclo_id='.$novoId)
            ->assertOk()
            ->assertJsonPath('meta.total', 0);

        $this->getJson('/api/plano-de-metas?ciclo_id='.$novoId)
            ->assertOk()
            ->assertJsonPath('meta.total', 0);

        $this->getJson('/api/plano-de-metas?ciclo_id='.$origem->id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        $this->getJson('/api/portfolio-ciclos')
            ->assertOk();

        $ciclos = collect($this->getJson('/api/portfolio-ciclos')->json('data'));
        $this->assertSame(1, (int) $ciclos->firstWhere('id', $origem->id)['composicao']['plano_de_metas']);
        $this->assertSame(0, (int) $ciclos->firstWhere('id', (int) $novoId)['composicao']['plano_de_metas']);
    }

    public function test_lista_ciclos_inclui_anos_e_composicao_do_portfolio(): void
    {
        $this->actingAs($this->editor(), 'sanctum');

        $response = $this->getJson('/api/portfolio-ciclos');
        $response->assertOk();
        $response->assertJsonPath('data.0.nome', '2025-2026');
        $this->assertSame(['2025', '2026'], $response->json('data.0.anos'));
        $response->assertJsonStructure([
            'data' => [[
                'composicao' => [
                    'cursos',
                    'plano_de_metas',
                    'pca',
                    'eixos',
                ],
            ]],
        ]);
    }

    public function test_consultor_nao_gera_proximo_portfolio(): void
    {
        $this->actingAs($this->consultor(), 'sanctum');

        $this->postJson('/api/portfolio-ciclos/gerar-proximo', [
            'nome' => '2029',
        ])->assertForbidden();
    }

}
