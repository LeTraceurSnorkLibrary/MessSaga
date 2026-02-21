<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  canLogin: { type: Boolean, default: false },
  canRegister: { type: Boolean, default: false },
});
</script>

<template>
  <section class="cta-section">
    <div class="cta-section__container">
      <h2 class="cta-section__title">Готовы сохранить свои воспоминания?</h2>
      <p class="cta-section__subtitle">
        Присоединяйтесь к MessSaga и никогда не теряйте важные моменты
      </p>
      <div class="cta-section__actions">
        <Link
          v-if="canRegister"
          :href="route('register')"
          class="cta-section__btn cta-section__btn--primary"
        >
          Начать бесплатно
        </Link>
        <Link
          v-if="canLogin"
          :href="route('login')"
          class="cta-section__btn cta-section__btn--outline"
        >
          Уже есть аккаунт
        </Link>
      </div>
    </div>
  </section>
</template>

<style scoped>
.cta-section {
  padding: 6rem 1.5rem;
  background: linear-gradient(90deg, var(--welcome-orange-500), var(--welcome-rose-500), var(--welcome-amber-500));
}

.cta-section__container {
  max-width: 56rem;
  margin: 0 auto;
  text-align: center;
  color: var(--welcome-white);
}

.cta-section__title {
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 700;
  margin: 0 0 1.5rem;
}

.cta-section__subtitle {
  font-size: clamp(1.125rem, 2vw, 1.5rem);
  margin: 0 0 2.5rem;
  opacity: 0.9;
}

.cta-section__actions {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

@media (min-width: 640px) {
  .cta-section__actions {
    flex-direction: row;
  }
}

.cta-section__btn {
  display: inline-block;
  padding: 1.25rem 2.5rem;
  font-size: 1.125rem;
  font-weight: 700;
  border-radius: var(--welcome-radius-full);
  text-decoration: none;
  transition: background var(--welcome-transition-normal), box-shadow var(--welcome-transition-normal), transform var(--welcome-transition-normal);
}

.cta-section__btn--primary {
  background: var(--welcome-white);
  color: var(--welcome-orange-600);
}

.cta-section__btn--primary:hover {
  background: #f9fafb;
  box-shadow: var(--welcome-shadow-2xl);
  transform: scale(1.05);
}

.cta-section__btn--outline {
  background: transparent;
  border: 2px solid var(--welcome-white);
  color: var(--welcome-white);
}

.cta-section__btn--outline:hover {
  background: rgba(255, 255, 255, 0.1);
}
</style>
