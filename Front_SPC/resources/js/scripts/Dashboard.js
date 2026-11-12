import { lerCicloContexto, CICLO_CONTEXTO_EVENTO } from './cicloContexto';
import { carregarUnidadesNomes } from './unidadesApi';
import { EIXOS_OFICIAIS, eixosIguais } from '../utils/catalogoOficial';

const CORES_EIXO = ['#003F7D', '#F57C00', '#0d9488', '#7c3aed', '#db2777', '#2563eb', '#ca8a04', '#64748b'];

export default {
  name: 'Dashboard',
  data() {
    return {
      carregando: true,
      erro: '',
      cursos: [],
      filtros: { ano: '', unidade: '', eixo: '', status: '' },
      meta: { eixos: [...EIXOS_OFICIAIS], status: ['ATIVO', 'INATIVO', 'EM REVISÃO'] },
      cicloNome: lerCicloContexto()?.nome || '',
      unidadesBase: [],
    };
  },
  computed: {
    temFiltro() {
      return Object.values(this.filtros).some(Boolean);
    },
    anosDisponiveis() {
      return [...new Set(this.cursos
        .map((curso) => String(curso.ultima_revisao || '').slice(0, 4))
        .filter((ano) => /^\d{4}$/.test(ano)))]
        .sort((a, b) => b.localeCompare(a));
    },
    unidadesDisponiveis() {
      return [...new Set([
        ...this.unidadesBase,
        ...this.cursos.flatMap((curso) => [
          curso.unidade,
          ...(Array.isArray(curso.unidades_oferta) ? curso.unidades_oferta : []),
        ]),
      ].filter(Boolean))].sort();
    },
    cursosFiltrados() {
      return this.cursos.filter((curso) => {
        const ano = String(curso.ultima_revisao || '').slice(0, 4);
        const unidades = [curso.unidade, ...(curso.unidades_oferta || [])].filter(Boolean);
        return (!this.filtros.ano || ano === String(this.filtros.ano))
          && (!this.filtros.unidade || unidades.includes(this.filtros.unidade))
          && (!this.filtros.eixo || eixosIguais(curso.eixo, this.filtros.eixo))
          && (!this.filtros.status || this.statusCurso(curso) === this.statusCurso({ status: this.filtros.status }));
      });
    },
    totalCursos() {
      return this.cursosFiltrados.length;
    },
    cursosAtivos() {
      return this.cursosFiltrados.filter((curso) => this.statusCurso(curso) === 'ATIVO').length;
    },
    cursosInativos() {
      return this.cursosFiltrados.filter((curso) => this.statusCurso(curso) === 'INATIVO').length;
    },
    cursosEmRevisao() {
      return this.cursosFiltrados.filter((curso) => this.statusCurso(curso).includes('REVISAO')).length;
    },
    totalEixos() {
      return this.meta.eixos.length;
    },
    chartEixos() {
      return this.enriquecerBarras(this.meta.eixos.map((label, index) => ({
        label,
        value: this.cursosFiltrados.filter((curso) => eixosIguais(curso.eixo, label)).length,
        color: CORES_EIXO[index % CORES_EIXO.length],
      })));
    },
    chartTipos() {
      const contagem = this.contarPor(this.cursosFiltrados, 'tipo');
      return this.enriquecerBarras(Object.entries(contagem)
        .map(([label, value]) => ({ label, value }))
        .sort((a, b) => b.value - a.value));
    },
    chartCargaHoraria() {
      const faixas = [
        { label: 'Até 100h', min: 0, max: 100, value: 0 },
        { label: '101 a 300h', min: 101, max: 300, value: 0 },
        { label: '301 a 800h', min: 301, max: 800, value: 0 },
        { label: 'Acima de 800h', min: 801, max: Infinity, value: 0 },
      ];
      this.cursosFiltrados.forEach((curso) => {
        const carga = Number(String(curso.carga_horaria || '').replace(/\D/g, '')) || 0;
        const faixa = faixas.find((item) => carga >= item.min && carga <= item.max);
        if (faixa) faixa.value++;
      });
      return this.enriquecerBarras(faixas);
    },
  },
  created() {
    this.carregarDashboard();
    window.addEventListener(CICLO_CONTEXTO_EVENTO, this.carregarDashboard);
  },
  beforeUnmount() {
    window.removeEventListener(CICLO_CONTEXTO_EVENTO, this.carregarDashboard);
  },
  methods: {
    async carregarDashboard() {
      this.carregando = true;
      this.erro = '';
      try {
        const { data } = await window.axios.get('/api/dashboard', {
          params: { ciclo_id: lerCicloContexto()?.id },
        });
        const payload = data.data || {};
        this.cursos = payload.cursos || [];
        this.cicloNome = payload.meta?.ciclo?.nome || lerCicloContexto()?.nome || '';
        const eixos = payload.meta?.eixos || [];
        const status = [...new Set([
          ...(payload.meta?.status || []),
          ...this.cursos.map((curso) => curso.status).filter(Boolean),
        ])].sort();
        this.meta = {
          eixos: eixos.length ? eixos : [...EIXOS_OFICIAIS],
          status: status.length ? status : ['ATIVO', 'INATIVO', 'EM REVISÃO'],
        };
        this.unidadesBase = await carregarUnidadesNomes({ forcar: true });
      } catch (error) {
        this.erro = error.response?.data?.message || 'Não foi possível carregar os dados do portfólio.';
      } finally {
        this.carregando = false;
      }
    },
    limparFiltros() {
      this.filtros = { ano: '', unidade: '', eixo: '', status: '' };
    },
    statusCurso(curso) {
      return String(curso?.status || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toUpperCase();
    },
    contarPor(lista, campo) {
      return lista.reduce((acc, item) => {
        const chave = item[campo];
        if (chave) acc[chave] = (acc[chave] || 0) + 1;
        return acc;
      }, {});
    },
    enriquecerBarras(items) {
      const total = items.reduce((sum, item) => sum + item.value, 0);
      const max = Math.max(...items.map((item) => item.value), 1);
      return items.map((item) => ({
        ...item,
        share: total ? Math.round((item.value / total) * 100) : 0,
        bar: Math.round((item.value / max) * 100),
      }));
    },
  },
};
