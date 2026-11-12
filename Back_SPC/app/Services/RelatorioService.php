<?php

namespace App\Services;

use App\Models\Ciclo;
use App\Models\Pca;
use App\Models\PlanoDeMeta;
use App\Support\CatalogoOficial;
use App\Support\ConsultaCatalogoCursos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class RelatorioService
{
    public const LIMITE_PDF = 10000;

    public const LIMITE_PREVIEW = 10000;

    public function catalogo(): array
    {
        return array_values(config('relatorios.catalogo', []));
    }

    public function tipoExiste(string $tipo): bool
    {
        return array_key_exists($tipo, config('relatorios.catalogo', []));
    }

    public function obterDefinicao(string $tipo): array
    {
        if (! $this->tipoExiste($tipo)) {
            throw new InvalidArgumentException("Tipo de relatório inválido: {$tipo}");
        }

        return config("relatorios.catalogo.{$tipo}");
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{definicao: array, filtros: array, registros: Collection, total: int, total_exibido: int, truncado: bool, limite: int}
     */
    public function montar(string $tipo, array $filtros = [], ?int $limite = null): array
    {
        $definicao = $this->obterDefinicao($tipo);
        $filtrosAplicados = $this->normalizarFiltros($filtros, $definicao['filtros'] ?? []);
        $limiteEfetivo = $limite ?? self::LIMITE_PDF;
        $total = $this->contar($tipo, $filtrosAplicados);
        $registros = $this->consultar($tipo, $filtrosAplicados, $limiteEfetivo);

        return [
            'definicao' => $definicao,
            'filtros' => $filtrosAplicados,
            'registros' => $registros,
            'total' => $total,
            'total_exibido' => $registros->count(),
            'truncado' => $total > $limiteEfetivo,
            'limite' => $limiteEfetivo,
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function contar(string $tipo, array $filtros = []): int
    {
        return $this->queryPorTipo($tipo, $filtros)->count();
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function consultar(string $tipo, array $filtros = [], ?int $limite = null): Collection
    {
        $query = $this->queryPorTipo($tipo, $filtros);
        if ($limite !== null) {
            $query->limit($limite);
        }

        return $query->get()->map(fn ($item) => $item->toArray());
    }

    /** @return list<string> */
    public function eixosDisponiveis(?string $tipo = null): array
    {
        return CatalogoOficial::eixos();
    }

    public function contagens(array $filtros = []): array
    {
        return [
            'cursos' => $this->queryCursos($filtros)->count(),
            'plano-de-metas' => $this->queryPlanoDeMetas($filtros)->count(),
            'pcas' => $this->queryPcas($filtros)->count(),
            'eixos' => $this->queryEixos($filtros)->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array<string, string>
     */
    private function normalizarFiltros(array $filtros, array $permitidos): array
    {
        $saida = [];
        foreach (array_values(array_unique([...$permitidos, 'busca', 'ciclo_id'])) as $chave) {
            $valor = $filtros[$chave] ?? null;
            if ($valor !== null && $valor !== '') {
                $saida[$chave] = (string) $valor;
            }
        }

        return $saida;
    }

    private function queryPorTipo(string $tipo, array $filtros): Builder
    {
        return match ($tipo) {
            'cursos' => $this->queryCursos($filtros),
            'plano-de-metas' => $this->queryPlanoDeMetas($filtros),
            'pcas' => $this->queryPcas($filtros),
            'eixos' => $this->queryEixos($filtros),
            default => throw new InvalidArgumentException("Tipo de relatório inválido: {$tipo}"),
        };
    }

    private function aplicarFiltroEixoOficial(Builder $query, array $filtros): void
    {
        if (! empty($filtros['eixo'])) {
            CatalogoOficial::aplicarFiltroEixo($query, $filtros['eixo']);
        }
    }

    private function aplicarFiltroCiclo(Builder $query, array $filtros): void
    {
        Ciclo::aplicarFiltroNaConsulta($query, $filtros['ciclo_id'] ?? null);
    }

    private function aplicarBusca(Builder $query, array $filtros, array $campos): void
    {
        if (empty($filtros['busca']) || $campos === []) {
            return;
        }

        $busca = $filtros['busca'];
        $query->where(function (Builder $q) use ($busca, $campos) {
            foreach ($campos as $indice => $campo) {
                $indice === 0
                    ? $q->where($campo, 'like', "%{$busca}%")
                    : $q->orWhere($campo, 'like', "%{$busca}%");
            }
        });
    }

    private function queryCursos(array $filtros): Builder
    {
        return ConsultaCatalogoCursos::query($filtros)->orderBy('titulo');
    }

    private function queryPlanoDeMetas(array $filtros): Builder
    {
        $query = PlanoDeMeta::query()
            ->orderByRaw('CASE WHEN ano IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('ano')
            ->orderBy('curso');

        $this->aplicarFiltroCiclo($query, $filtros);
        $this->aplicarBusca($query, $filtros, [
            'segmento', 'curso', 'tipo', 'numero_sei', 'codigo_sig',
            'mes_entrega', 'status', 'status_final', 'observacao',
        ]);
        if (! empty($filtros['ano'])) {
            $query->where('ano', $filtros['ano']);
        }
        if (! empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        return $query;
    }

    private function queryPcas(array $filtros): Builder
    {
        $query = Pca::query()
            ->orderByRaw("CASE WHEN COALESCE(TRIM(eixo), '') = '' AND COALESCE(TRIM(unidade), '') = '' THEN 1 ELSE 0 END")
            ->orderByDesc('ano')
            ->orderBy('titulo');

        $this->aplicarFiltroCiclo($query, $filtros);
        $this->aplicarBusca($query, $filtros, [
            'titulo', 'numero_sei', 'codigo_sig', 'eixo', 'unidade', 'semestre', 'status', 'observacao',
        ]);
        if (! empty($filtros['ano'])) {
            $query->where('ano', $filtros['ano']);
        }
        if (! empty($filtros['unidade'])) {
            $query->where('unidade', $filtros['unidade']);
        }
        $this->aplicarFiltroEixoOficial($query, $filtros);
        if (! empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        return $query;
    }

    private function queryEixos(array $filtros): Builder
    {
        return $this->queryCursos($filtros);
    }
}
