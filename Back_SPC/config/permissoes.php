<?php

/**
 * Níveis de acesso do Sistema_SPC.
 *
 * Administrador — gerencia usuários e tem acesso total.
 * Editor — cria e altera dados do portfólio (sem gerenciar usuários).
 * Consultor — apenas consulta (leitura).
 */
return [

    'perfis' => [
        'Administrador',
        'Editor',
        'Consultor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissões por ação
    |--------------------------------------------------------------------------
    |
    | Cada ação lista os perfis autorizados. Funções não se sobrepõem:
    | - só o Administrador gerencia usuários
    | - só Administrador e Editor alteram dados
    | - Consultor apenas consulta
    |
    */

    'acoes' => [
        'gerenciar_usuarios' => ['Administrador'],
        'editar_dados' => ['Administrador', 'Editor'],
        'consultar_dados' => ['Administrador', 'Editor', 'Consultor'],
        'importar_dados' => ['Administrador', 'Editor'],
        'ver_relatorios' => ['Administrador', 'Editor', 'Consultor'],
        'consultar_auditoria' => ['Administrador'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rotas do menu (front) por perfil
    |--------------------------------------------------------------------------
    */

    'menu' => [
        'inicio' => ['Administrador', 'Editor', 'Consultor'],
        'dashboard' => ['Administrador', 'Editor', 'Consultor'],
        'relatorios' => ['Administrador', 'Editor', 'Consultor'],
        'importacoes' => ['Administrador', 'Editor'],
        'auditoria' => ['Administrador'],
        'cursos' => ['Administrador', 'Editor', 'Consultor'],
        'ciclos' => ['Administrador', 'Editor', 'Consultor'],
        'ciclos-portfolio' => ['Administrador', 'Editor', 'Consultor'],
        'plano-de-metas' => ['Administrador', 'Editor', 'Consultor'],
        'pca' => ['Administrador', 'Editor', 'Consultor'],
        'eixos' => ['Administrador', 'Editor', 'Consultor'],
        'unidades' => ['Administrador', 'Editor', 'Consultor'],
        'estruturas-institucionais' => ['Administrador', 'Editor', 'Consultor'],
        'usuarios' => ['Administrador'],
    ],
];
