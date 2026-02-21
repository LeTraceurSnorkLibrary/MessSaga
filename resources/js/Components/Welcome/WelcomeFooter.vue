<script setup>
import WelcomeLogo from './WelcomeLogo.vue';

defineProps({
  copyrightYear: { type: String, default: '' },
});
</script>

<template>
  <footer class="welcome-footer">
    <div class="welcome-footer__container">
      <div class="welcome-footer__inner">
        <WelcomeLogo size="sm" />
        <div class="welcome-footer__copy">
          © {{ copyrightYear }} MessSaga. Ваш личный архив воспоминаний.
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.welcome-footer {
  background: var(--gray-900);
  color: var(--gray-400);
  padding: 3rem 1.5rem;
}

.welcome-footer__container {
  max-width: 72rem;
  margin: 0 auto;
}

.welcome-footer__inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

@media (min-width: 768px) {
  .welcome-footer__inner {
    flex-direction: row;
  }
}

.welcome-footer :deep(.welcome-logo__text) {
  color: var(--gray-0);
  background: none;
}

.welcome-footer__copy {
  font-size: 0.875rem;
  text-align: center;
}

@media (min-width: 768px) {
  .welcome-footer__copy {
    text-align: left;
  }
}
</style>
