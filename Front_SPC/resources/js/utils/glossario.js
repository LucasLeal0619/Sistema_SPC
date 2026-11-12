function normalizar(texto) {
  return String(texto || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();
}

export const INDICADORES = {
  'total de cursos': 'Quantidade de cursos do portfólio no ciclo e filtros atuais.',
  'cursos por eixo': 'Distribuição do portfólio entre os cinco eixos oficiais.',
  'tipos de curso': 'Distribuição dos cursos por tipo de oferta.',
  'carga horária': 'Carga horária total dos cursos, em horas.',
};

export const STATUS = {
  ativo: 'Curso disponível para oferta no portfólio.',
  inativo: 'Curso fora de oferta no momento.',
  suspenso: 'Curso temporariamente suspenso.',
  'em revisão': 'Curso em atualização ou revisão de conteúdo.',
  'em análise': 'Registro em avaliação pela gestão.',
  previsto: 'Incluído no planejamento, ainda não vigente.',
  publicado: 'Já publicado para acompanhamento.',
  aprovado: 'Aprovado pela área responsável.',
  planejado: 'Planejado para execução futura.',
  concluído: 'Entrega ou planejamento finalizado.',
  entregue: 'Entrega registrada.',
};

export const TERMOS = {
  sei: 'Sistema Eletrônico de Informações — número do processo oficial.',
  'processo sei': 'Número do processo no SEI associado ao curso.',
  sig: 'Código interno SIG do curso no portfólio.',
  'código sig': 'Código interno SIG do curso no portfólio.',
  dn: 'Código DN (Departamento Nacional) do curso.',
  'código dn': 'Código DN (Departamento Nacional) do curso.',
  pca: 'Planejamento de Cursos Abertos — oferta e precificação do período.',
  ch: 'Carga horária total do curso, em horas.',
  eixo: 'Eixo tecnológico que agrupa cursos da mesma área.',
  segmento: 'Área de formação vinculada a um eixo tecnológico.',
  unidade: 'Local de oferta de um curso.',
  ciclo: 'Período do portfólio que delimita os registros exibidos.',
  'plano de metas': 'Mapeamento de cursos, metas e entregas planejadas por ano.',
  portfólio: 'Conjunto de cursos e planejamentos do período.',
};

const FONTES = [INDICADORES, STATUS, TERMOS];

export function explicar(chave) {
  if (!chave) return null;
  const bruto = String(chave).trim();
  if (!bruto) return null;

  for (const mapa of FONTES) {
    if (mapa[bruto]) return mapa[bruto];
  }

  const chaveNorm = normalizar(bruto);
  for (const mapa of FONTES) {
    for (const [k, v] of Object.entries(mapa)) {
      if (normalizar(k) === chaveNorm) return v;
    }
  }

  return null;
}

export function explicarOuVazio(chave) {
  return explicar(chave) || '';
}
