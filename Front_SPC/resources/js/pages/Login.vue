<template>
  <div class="login-page">
    <section class="login-brand" aria-label="Sistema SGPC do Senac DF">
      <div class="login-brand-content">
        <div class="login-logo-lockup">
          <img :src="logoSenac" alt="Senac" class="login-logo" />
          <span class="login-logo-signature">Fecomércio e Sesc</span>
        </div>
        <div class="login-brand-copy">
          <span class="login-brand-accent" aria-hidden="true"></span>
          <h1 class="login-title">Sistema SGPC</h1>
          <p class="login-subtitle">Sistema de Gerenciamento de Planos de Cursos</p>
          <div class="login-brand-message">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M12 7v14"/>
              <path d="M3 18V5a1 1 0 0 1 1-1h5a3 3 0 0 1 3 3 3 3 0 0 1 3-3h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-5a3 3 0 0 0-3 2 3 3 0 0 0-3-2H4a1 1 0 0 1-1-1Z"/>
            </svg>
            <p>Mais organização e eficiência na gestão dos planos de cursos do Senac.</p>
          </div>
        </div>
        <p class="login-brand-caption">Senac DF · Educação profissional</p>
      </div>
    </section>

    <main class="login-access">
      <svg class="login-corner-decoration" viewBox="0 0 220 140" aria-hidden="true">
        <path d="M82 0h138v106C173 89 119 52 82 0Z" fill="#07366b" />
        <path d="M55-4c35 49 91 86 165 96" fill="none" stroke="#ff7900" stroke-width="3" stroke-linecap="round" />
      </svg>
      <div class="login-card">
        <header class="login-card-header">
          <p class="login-card-welcome">Bem-vindo(a) ao</p>
          <h2 class="login-card-title">Sistema SGPC</h2>
          <p class="login-card-intro">Faça login para acessar o sistema.</p>
        </header>

        <form class="login-form" @submit.prevent="handleLogin">
          <div v-if="errorMessage" class="login-error" role="alert">
            {{ errorMessage }}
          </div>

          <div class="form-group form-group-email">
            <label for="email"><FormLabel label="E-mail" required /></label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              </span>
              <input
                id="email"
                v-model="email"
                type="email"
                autocomplete="email"
                placeholder="seu@email.senac.br"
                required
              />
            </div>
          </div>

          <div class="form-group form-group-password">
            <label for="senha"><FormLabel label="Senha" required /></label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <input
                id="senha"
                v-model="senha"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                placeholder="Digite sua senha"
                required
              />
              <button
                type="button"
                class="toggle-password"
                :aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'"
                @click="showPassword = !showPassword"
              >
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
              </button>
            </div>
          </div>

          <label class="login-lembrar">
            <input v-model="lembrar" type="checkbox" />
            <span>Lembrar e-mail neste computador</span>
          </label>

          <button type="submit" class="btn-entrar" :disabled="loading">
            <span>{{ loading ? 'Entrando...' : 'Entrar' }}</span>
            <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            <span v-else class="login-spinner" aria-hidden="true"></span>
          </button>
        </form>

        <div class="login-card-divider" aria-hidden="true">
          <span>Senac DF</span>
        </div>
        <p class="login-card-footer">
          Acesso restrito a colaboradores autorizados do Senac DF.
        </p>
      </div>

      <footer class="login-page-footer">
        {{ rodapeVersao }}
      </footer>
    </main>
  </div>
</template>

<script src="../scripts/Login.js"></script>
<style scoped src="../../css/Login.css"></style>
