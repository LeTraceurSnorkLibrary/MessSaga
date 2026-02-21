<script setup>
import { Link } from '@inertiajs/vue3';
import WelcomeBadge from './WelcomeBadge.vue';
import TrustIndicator from './TrustIndicator.vue';

defineProps({
  canLogin: { type: Boolean, default: false },
  canRegister: { type: Boolean, default: false },
});
</script>

<template>
  <section class="welcome-hero">
    <div class="welcome-hero__container">
      <div class="welcome-hero__content">
        <WelcomeBadge text="Сохраните свои воспоминания навсегда" />
        <h1 class="welcome-hero__title">
          <span class="welcome-hero__title-accent">Ваш личный архив</span>
          <br />
          <span class="welcome-hero__title-dark">всех переписок</span>
        </h1>
        <p class="welcome-hero__subtitle">
          Сохраните переписки из Telegram, WhatsApp, VK и других мессенджеров
          в одном безопасном месте. Ищите, вспоминайте, храните навсегда.
        </p>
        <div class="welcome-hero__actions">
          <Link
            v-if="canRegister"
            :href="route('register')"
            class="welcome-hero__btn welcome-hero__btn--primary"
          >
            <span>Начать прямо сейчас</span>
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
            </svg>
          </Link>
          <a href="#how-it-works" class="welcome-hero__btn welcome-hero__btn--secondary">
            Как это работает?
          </a>
        </div>
        <div class="welcome-hero__trust">
          <TrustIndicator text="Бесплатно" />
          <TrustIndicator text="Зашифровано" />
          <TrustIndicator text="Без рекламы" />
        </div>
      </div>
      <div class="welcome-hero__mockup">
        <slot name="mockup" />
      </div>
    </div>
  </section>
</template>

<style scoped>
.welcome-hero {
  padding-top: 8rem;
  padding-bottom: 5rem;
  padding-left: 1.5rem;
  padding-right: 1.5rem;
}

.welcome-hero__container {
  max-width: 72rem;
  margin: 0 auto;
}

.welcome-hero__content {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
}

.welcome-hero__title {
  font-size: clamp(2.5rem, 5vw, 4.375rem);
  font-weight: 700;
  line-height: 1.2;
  margin: 0;
}

.welcome-hero__title-accent {
  background: linear-gradient(90deg, var(--welcome-orange-600), var(--welcome-rose-600), var(--welcome-amber-600));
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.welcome-hero__title-dark {
  color: var(--welcome-gray-800);
}

.welcome-hero__subtitle {
  font-size: clamp(1.125rem, 2vw, 1.5rem);
  color: var(--welcome-gray-600);
  max-width: 48rem;
  margin: 0;
  line-height: 1.6;
}

.welcome-hero__actions {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding-top: 1rem;
}

@media (min-width: 640px) {
  .welcome-hero__actions {
    flex-direction: row;
    justify-content: center;
  }
}

.welcome-hero__btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  font-size: 1.125rem;
  font-weight: 600;
  border-radius: var(--welcome-radius-full);
  text-decoration: none;
  transition: box-shadow var(--welcome-transition-normal), transform var(--welcome-transition-normal);
}

.welcome-hero__btn--primary {
  background: linear-gradient(90deg, var(--welcome-orange-500), var(--welcome-rose-500));
  color: var(--welcome-white);
}

.welcome-hero__btn--primary:hover {
  box-shadow: var(--welcome-shadow-2xl);
  transform: scale(1.05);
}

.welcome-hero__btn--secondary {
  background: var(--welcome-white);
  color: var(--welcome-gray-700);
  border: 2px solid var(--welcome-orange-200);
}

.welcome-hero__btn--secondary:hover {
  border-color: var(--welcome-orange-300);
  box-shadow: var(--welcome-shadow-lg);
}

.welcome-hero__trust {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  padding-top: 2rem;
  font-size: 0.875rem;
  color: var(--welcome-gray-600);
}

.welcome-hero__mockup {
  margin-top: 4rem;
  position: relative;
}
</style>
