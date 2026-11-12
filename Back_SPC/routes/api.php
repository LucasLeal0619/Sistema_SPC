<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CadastroController;
use App\Http\Controllers\Api\CursoController;
use App\Http\Controllers\Api\CursoExecucaoController;
use App\Http\Controllers\Api\CursoPorEixoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EixoController;
use App\Http\Controllers\Api\ImportacaoController;
use App\Http\Controllers\Api\CicloController;
use App\Http\Controllers\Api\PcaController;
use App\Http\Controllers\Api\PlanoDeMetaController;
use App\Http\Controllers\Api\RelatorioController;
use App\Http\Controllers\Api\RevisaoDadosController;
use App\Http\Controllers\Api\UnidadeOfertaController;
use App\Http\Controllers\Api\RegiaoAdministrativaController;
use App\Http\Controllers\Api\UsuarioController;
use App\Models\Usuario;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

Route::middleware(['auth:sanctum', 'usuario.ativo'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // Somente Administrador gerencia usuários (cadastra e define login/senha).
    Route::middleware('perfil:'.Usuario::PERFIL_ADMINISTRADOR)
        ->apiResource('usuarios', UsuarioController::class);

    // Auditoria (tabela cadastros) — somente leitura para Administrador.
    Route::get('cadastros', [CadastroController::class, 'index']);
    Route::get('cadastros/{cadastro}', [CadastroController::class, 'show']);
    Route::post('ciclos/gerar-proximo', [CicloController::class, 'gerarProximo']);
    Route::post('ciclos/{ciclo}/marcar-atual', [CicloController::class, 'marcarAtual']);
    Route::apiResource('ciclos', CicloController::class);
    Route::post('portfolio-ciclos/gerar-proximo', [CicloController::class, 'gerarProximo']);
    Route::post('portfolio-ciclos/{ciclo}/marcar-atual', [CicloController::class, 'marcarAtual']);
    Route::apiResource('portfolio-ciclos', CicloController::class)->parameters(['portfolio-ciclos' => 'ciclo']);
    Route::apiResource('cursos', CursoController::class);
    Route::apiResource('plano-de-metas', PlanoDeMetaController::class);
    Route::apiResource('pcas', PcaController::class);
    Route::get('eixos/resumo', [EixoController::class, 'resumo']);
    Route::get('eixos/pendentes', [EixoController::class, 'pendentes']);
    Route::get('eixos/{eixo}/detalhes', [EixoController::class, 'detalhes']);
    Route::get('eixos/{eixo}/cursos', [EixoController::class, 'cursos']);
    Route::get('revisao-dados', [RevisaoDadosController::class, 'index']);
    Route::post('revisao-dados/cursos/{curso}/classificar', [RevisaoDadosController::class, 'classificar']);
    Route::post('revisao-dados/execucoes/{curso_execucao}/vincular', [RevisaoDadosController::class, 'vincular']);
    Route::post('curso-execucoes/{curso_execucao}/vincular', [CursoExecucaoController::class, 'vincular']);
    Route::apiResource('curso-execucoes', CursoExecucaoController::class)
        ->parameters(['curso-execucoes' => 'curso_execucao']);
    Route::apiResource('curso-por-eixos', CursoPorEixoController::class);
    Route::get('unidades-oferta/nomes', [UnidadeOfertaController::class, 'nomes']);
    Route::get('unidades-oferta/opcoes', [UnidadeOfertaController::class, 'opcoes']);
    Route::apiResource('regioes-administrativas', RegiaoAdministrativaController::class)
        ->parameters(['regioes-administrativas' => 'regiaoAdministrativa'])
        ->except(['destroy']);
    Route::apiResource('unidades-oferta', UnidadeOfertaController::class)
        ->parameters(['unidades-oferta' => 'unidadeOferta'])
        ->except(['destroy']);
    Route::get('dashboard', [DashboardController::class, 'resumo']);

    Route::get('relatorios', [RelatorioController::class, 'index']);
    Route::get('relatorios/{tipo}/preview', [RelatorioController::class, 'preview']);
    Route::get('relatorios/{tipo}/pdf', [RelatorioController::class, 'pdf']);

    Route::get('importacoes', [ImportacaoController::class, 'catalogo']);
    Route::post('importacoes/{modulo}/preview', [ImportacaoController::class, 'preview']);
    Route::post('importacoes/{modulo}/commit', [ImportacaoController::class, 'commit']);

});
