<?php

namespace App\Services;

use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\UnidadeOferta;
use App\Support\CatalogoOficial;
use App\Support\ConsultaCatalogoCursos;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function resumo(array $filtros = []): array
    {
        $cicloId = ConsultaCatalogoCursos::ciclo($filtros['ciclo_id'] ?? null);
        $cursos = ConsultaCatalogoCursos::query($filtros)
            ->select([
                'id',
                'titulo',
                'eixo',
                'status',
                'tipo',
                'unidade',
                'unidades_oferta',
                'ultima_revisao',
                'carga_horaria',
            ])
            ->orderBy('id')
            ->get()
            ->map(fn (Curso $curso) => [
                'id' => $curso->id,
                'titulo' => $curso->titulo,
                'eixo' => $curso->eixo,
                'status' => $curso->status,
                'tipo' => $curso->tipo,
                'unidade' => $curso->unidade,
                'unidades_oferta' => $curso->unidades_oferta ?? [],
                'ultima_revisao' => $curso->ultima_revisao,
                'carga_horaria' => $curso->carga_horaria,
            ])
            ->values()
            ->all();

        $status = Curso::query()
            ->when($cicloId, fn ($query) => $query->where('ciclo_id', $cicloId))
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->all();

        return [
            'cursos' => $cursos,
            'meta' => [
                'eixos' => CatalogoOficial::eixos(),
                'ciclo_id' => $cicloId,
                'ciclo' => $cicloId ? Ciclo::query()->find($cicloId)?->paraMeta() : null,
                'status' => $status,
                'unidades' => UnidadeOferta::nomesAtivos(),
            ],
        ];
    }
}
