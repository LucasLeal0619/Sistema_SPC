import { createRouter, createWebHistory } from 'vue-router';
import { garantirSessao, podeAcessarMenu } from '../scripts/auth';

const appChildren = [
  { path: 'inicio', name: 'inicio', component: () => import('../pages/Inicio.vue'), meta: { menu: 'inicio' } },
  { path: 'dashboard', name: 'dashboard', component: () => import('../pages/Dashboard.vue'), meta: { menu: 'dashboard' } },
  { path: 'relatorios', name: 'relatorios', component: () => import('../pages/Relatorios.vue'), meta: { menu: 'relatorios' } },
  { path: 'importacoes', name: 'importacoes', component: () => import('../pages/Importacoes.vue'), meta: { menu: 'importacoes' } },
  { path: 'importacoes/revisao-dados', name: 'revisao-dados', component: () => import('../pages/RevisaoDados.vue'), meta: { menu: 'importacoes' } },
  { path: 'cursos', name: 'cursos', component: () => import('../pages/Cursos.vue'), meta: { menu: 'cursos' } },
  { path: 'ciclos', name: 'ciclos', component: () => import('../pages/CiclosPortfolio.vue'), meta: { menu: 'ciclos' } },
  { path: 'ciclos-portfolio', redirect: { name: 'ciclos' } },
  { path: 'plano-de-metas', name: 'plano-de-metas', component: () => import('../pages/PlanoDeMetas.vue'), meta: { menu: 'plano-de-metas' } },
  { path: 'pca', name: 'pca', component: () => import('../pages/Pca.vue'), meta: { menu: 'pca' } },
  { path: 'eixos', name: 'eixos', component: () => import('../pages/Eixos.vue'), meta: { menu: 'eixos' } },
  { path: 'eixos/:id', name: 'eixos-detalhe', component: () => import('../pages/EixoDetalhe.vue'), meta: { menu: 'eixos' } },
  { path: 'estruturas-institucionais', name: 'estruturas-institucionais', component: () => import('../pages/Unidades.vue'), meta: { menu: 'estruturas-institucionais' } },
  { path: 'unidades', redirect: { name: 'estruturas-institucionais' } },
  { path: 'auditoria', name: 'auditoria', component: () => import('../pages/Auditoria.vue'), meta: { menu: 'auditoria' } },
  { path: 'usuarios', name: 'usuarios', component: () => import('../pages/Usuarios.vue'), meta: { menu: 'usuarios' } },
];

const redirects = appChildren.map((route) => ({
  path: `/${route.path}`,
  redirect: `/app/${route.path}`,
}));

const routes = [
  {
    path: '/',
    redirect: '/login',
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../pages/Login.vue'),
  },
  {
    path: '/app',
    component: () => import('../layouts/AppLayout.vue'),
    redirect: '/app/inicio',
    children: appChildren,
  },
  ...redirects,
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to) => {
  const normalizedPath = to.path.toLowerCase();

  if (to.path !== normalizedPath) {
    return {
      path: normalizedPath,
      query: to.query,
      hash: to.hash,
    };
  }

  const requiresAuth = to.path.startsWith('/app');
  const sessaoOk = await garantirSessao();

  if (requiresAuth && !sessaoOk) {
    return {
      name: 'login',
      query: to.fullPath !== '/app/inicio' && to.fullPath !== '/app'
        ? { redirect: to.fullPath }
        : {},
    };
  }

  if (to.name === 'login' && sessaoOk) {
    return { path: '/app/inicio' };
  }

  if (requiresAuth && sessaoOk) {
    const menuKey = to.meta?.menu;

    if (menuKey && !podeAcessarMenu(menuKey)) {
      return { path: '/app/inicio' };
    }
  }
});

const TITULOS_PAGINA = {
  login: 'Login',
  inicio: 'Início',
  dashboard: 'Dashboard',
  relatorios: 'Relatórios',
  importacoes: 'Importações',
  'revisao-dados': 'Revisão de Dados',
  eixos: 'Eixos',
  'eixos-detalhe': 'Eixo',
  cursos: 'Cursos',
  ciclos: 'Ciclos de Gestão',
  'ciclos-portfolio': 'Ciclos de Gestão',
  'plano-de-metas': 'Plano de Metas',
  pca: 'PCA',
  unidades: 'Estruturas Institucionais',
  'estruturas-institucionais': 'Estruturas Institucionais',
  auditoria: 'Auditoria',
  usuarios: 'Usuários',
};

router.afterEach((to) => {
  const titulo = TITULOS_PAGINA[to.name] || 'Sistema_SPC';
  document.title = `${titulo} · Sistema_SPC`;
});

export default router;
