<script setup>
import { Link } from '@inertiajs/vue3';
import WelcomeLogo from './WelcomeLogo.vue';

defineProps({
  canLogin: { type: Boolean, default: false },
  canRegister: { type: Boolean, default: false },
});
</script>

<template>
  <header class="welcome-header">
    <nav class="welcome-header__nav">
      <div class="welcome-header__inner">
        <WelcomeLogo :to="route('dashboard')" />
        <div v-if="canLogin" class="welcome-header__links">
          <Link v-if="canLogin" :href="route('login')" class="welcome-header__link">
            Войти
          </Link>
          <Link
            v-if="canRegister"
            :href="route('register')"
            class="welcome-header__cta"
          >
            Начать бесплатно
          </Link>
        </div>
      </div>
    </nav>
  </header>
</template>

<style scoped>
.welcome-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--welcome-orange-100);
}

.welcome-header__nav {
  max-width: 72rem;
  margin: 0 auto;
  padding: 1rem 1.5rem;
}

.welcome-header__inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.welcome-header__links {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.welcome-header__link {
  color: var(--welcome-gray-700);
  font-weight: 500;
  text-decoration: none;
  transition: color var(--welcome-transition-fast);
}

.welcome-header__link:hover {
  color: var(--welcome-orange-600);
}

.welcome-header__cta {
  padding: 0.625rem 1.5rem;
  background: linear-gradient(90deg, var(--welcome-orange-500), var(--welcome-rose-500));
  color: var(--welcome-white);
  border-radius: var(--welcome-radius-full);
  font-weight: 500;
  text-decoration: none;
  transition: box-shadow var(--welcome-transition-normal), transform var(--welcome-transition-normal);
}

.welcome-header__cta:hover {
  box-shadow: var(--welcome-shadow-lg);
  transform: scale(1.05);
}
</style>
