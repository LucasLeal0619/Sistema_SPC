<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncarDadosOperacionaisSeeder extends Seeder
{
    public function run(): void
    {
        $tabelas = [
            'cadastros',
            'curso_por_eixos',
            'cursos',
            'plano_de_metas',
            'pcas',
            'unidades_oferta',
            'sincronizacao_historicos',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($tabelas as $tabela) {
            if (Schema::hasTable($tabela)) {
                DB::table($tabela)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();
    }
}
