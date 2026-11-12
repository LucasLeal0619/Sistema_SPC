<template>
  <div class="dashboard-page">
    <header class="dashboard-header">
      <div>
        <h1>Portfólio de Cursos</h1>
        <p class="dashboard-description">
          Indicadores do ciclo <strong>{{ cicloNome || 'selecionado' }}</strong>
        </p>
      </div>

      <div class="dashboard-toolbar">
        <SearchableSelect
          v-model="filtros.ano"
          aria-label="Ano"
          empty-option="Todos os anos"
          :options="anosDisponiveis"
        />
        <SearchableSelect
          v-model="filtros.unidade"
          aria-label="Unidade"
          empty-option="Todas as unidades"
          :options="unidadesDisponiveis"
        />
        <SearchableSelect
          v-model="filtros.eixo"
          aria-label="Eixo"
          empty-option="Todos os eixos"
          :options="meta.eixos"
        />
        <SearchableSelect
          v-model="filtros.status"
          aria-label="Status"
          empty-option="Todos os status"
          :options="meta.status"
        />
        <button v-if="temFiltro" type="button" class="btn-limpar" @click="limparFiltros">Limpar</button>
      </div>
    </header>

    <div v-if="erro" class="alert alert-error">{{ erro }}</div>

    <section class="dashboard-kpi-strip" aria-label="Indicadores de cursos">
      <article class="dashboard-kpi-tile dashboard-kpi-tile--cursos">
        <p class="dashboard-metric-value">{{ totalCursos }}</p>
        <p class="dashboard-metric-title">Cursos no filtro</p>
      </article>
      <article class="dashboard-kpi-tile">
        <p class="dashboard-metric-value">{{ cursosAtivos }}</p>
        <p class="dashboard-metric-title">Ativos</p>
      </article>
      <article class="dashboard-kpi-tile">
        <p class="dashboard-metric-value">{{ cursosEmRevisao }}</p>
        <p class="dashboard-metric-title">Em revisão</p>
      </article>
      <article class="dashboard-kpi-tile">
        <p class="dashboard-metric-value">{{ cursosInativos }}</p>
        <p class="dashboard-metric-title">Inativos</p>
      </article>
    </section>

    <section class="dashboard-analytics" aria-label="Análises dos cursos">
      <article class="dashboard-chart-card">
        <div class="dashboard-chart-head">
          <div>
            <h3>Cursos por eixo</h3>
            <p class="dashboard-chart-subtitle">{{ totalEixos }} eixos no filtro</p>
          </div>
        </div>
        <div v-if="carregando" class="dashboard-chart-empty">Carregando...</div>
        <div v-else-if="chartEixos.length === 0" class="dashboard-chart-empty">Sem dados para os filtros selecionados</div>
        <div v-else class="dashboard-rank-list">
          <div v-for="(item, index) in chartEixos" :key="item.label" class="dashboard-rank-item">
            <span class="dashboard-rank-pos">{{ index + 1 }}</span>
            <div class="dashboard-rank-body">
              <div class="dashboard-rank-head">
                <span class="dashboard-rank-label">{{ item.label }}</span>
                <span class="dashboard-rank-meta"><strong>{{ item.value }}</strong><small>{{ item.share }}%</small></span>
              </div>
              <div class="dashboard-rank-track">
                <div class="dashboard-rank-fill" :style="{ width: `${Math.max(item.bar, 4)}%`, background: item.color }"></div>
              </div>
            </div>
          </div>
        </div>
      </article>

      <article class="dashboard-chart-card">
        <div class="dashboard-chart-head"><h3>Tipos de curso</h3></div>
        <div v-if="carregando" class="dashboard-chart-empty">Carregando...</div>
        <div v-else-if="chartTipos.length === 0" class="dashboard-chart-empty">Sem dados para os filtros selecionados</div>
        <div v-else class="dashboard-rank-list">
          <div v-for="item in chartTipos" :key="item.label" class="dashboard-rank-item">
            <div class="dashboard-rank-body">
              <div class="dashboard-rank-head">
                <span class="dashboard-rank-label">{{ item.label }}</span>
                <span class="dashboard-rank-meta"><strong>{{ item.value }}</strong><small>{{ item.share }}%</small></span>
              </div>
              <div class="dashboard-rank-track">
                <div class="dashboard-rank-fill" :style="{ width: `${Math.max(item.bar, 4)}%` }"></div>
              </div>
            </div>
          </div>
        </div>
      </article>

      <article class="dashboard-chart-card">
        <div class="dashboard-chart-head"><h3>Carga horária</h3></div>
        <div v-if="carregando" class="dashboard-chart-empty">Carregando...</div>
        <div v-else class="dashboard-rank-list">
          <div v-for="item in chartCargaHoraria" :key="item.label" class="dashboard-rank-item">
            <div class="dashboard-rank-body">
              <div class="dashboard-rank-head">
                <span class="dashboard-rank-label">{{ item.label }}</span>
                <span class="dashboard-rank-meta"><strong>{{ item.value }}</strong><small>{{ item.share }}%</small></span>
              </div>
              <div class="dashboard-rank-track">
                <div class="dashboard-rank-fill" :style="{ width: `${item.value ? Math.max(item.bar, 4) : 0}%` }"></div>
              </div>
            </div>
          </div>
        </div>
      </article>
    </section>
  </div>
</template>

<script src="../scripts/Dashboard.js"></script>
<style scoped src="../../css/Dashboard.css"></style>
