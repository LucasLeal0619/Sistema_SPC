<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $modulos = [
            'visitas-tecnicas',
            'horas-pedagogicas',
            'acoes-extensivas',
            'eventos',
            'jornadas-pedagogicas',
            'resolucoes',
            'termos-referencia',
            'cped-equipes',
            'kanban',
            'fluxogramas',
            'carometro',
            'organograma',
            'ferramentas',
            'cped',
            'sistemas-apoio',
        ];

        if (Schema::hasTable('cadastros') && Schema::hasColumn('cadastros', 'modulo')) {
            $auditoria = DB::table('cadastros')->whereIn('modulo', $modulos);

            if (Schema::hasColumn('cadastros', 'registro_tipo')) {
                $tipos = [
                    'App\\Models\\VisitaTecnica',
                    'App\\Models\\HoraPedagogica',
                    'App\\Models\\AcaoExtensiva',
                    'App\\Models\\Evento',
                    'App\\Models\\JornadaPedagogica',
                    'App\\Models\\Resolucao',
                    'App\\Models\\ResolucaoHistorico',
                    'App\\Models\\TermoReferencia',
                    'App\\Models\\TermoReferenciaHistorico',
                    'App\\Models\\CpedEquipe',
                    'App\\Models\\KanbanQuadro',
                    'App\\Models\\KanbanColuna',
                    'App\\Models\\KanbanCartao',
                    'App\\Models\\Fluxograma',
                ];
                $auditoria->orWhereIn('registro_tipo', $tipos);
            }

            $auditoria->delete();
        }

        foreach ([
            'notificacao_leituras',
            'termos_referencia_historicos',
            'resolucao_historicos',
            'kanban_cartoes',
            'kanban_colunas',
            'visita_tecnicas',
            'hora_pedagogicas',
            'acao_extensivas',
            'eventos',
            'jornadas_pedagogicas',
            'resolucoes',
            'termos_referencia',
            'kanban_quadros',
            'fluxogramas',
            'cped_equipes',
        ] as $tabela) {
            Schema::dropIfExists($tabela);
        }
    }

    public function down(): void
    {
        throw new \RuntimeException('Esta migration remove dados permanentemente e não pode ser revertida.');
    }
};
