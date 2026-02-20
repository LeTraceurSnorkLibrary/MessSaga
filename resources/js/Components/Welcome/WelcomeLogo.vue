<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  to: { type: String, default: null },
  size: { type: String, default: 'md' },
});
</script>

<template>
  <component
    :is="to ? Link : 'div'"
    :href="to"
    class="welcome-logo"
    :class="[size === 'sm' && 'welcome-logo--sm']"
  >
    <div class="welcome-logo__icon">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
        />
      </svg>
    </div>
    <span class="welcome-logo__text">MessSaga</span>
  </component>
</template>

<style scoped>
.welcome-logo {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: inherit;
}

.welcome-logo__icon {
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(135deg, var(--welcome-orange-400), var(--welcome-rose-400));
  border-radius: var(--welcome-radius-xl);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--welcome-shadow-lg);
}

.welcome-logo__icon svg {
  width: 1.5rem;
  height: 1.5rem;
  color: var(--welcome-white);
}

.welcome-logo__text {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(90deg, var(--welcome-orange-600), var(--welcome-rose-600));
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.welcome-logo--sm .welcome-logo__icon {
  width: 2rem;
  height: 2rem;
}

.welcome-logo--sm .welcome-logo__icon svg {
  width: 1.25rem;
  height: 1.25rem;
}

.welcome-logo--sm .welcome-logo__text {
  font-size: 1.25rem;
}
</style>
