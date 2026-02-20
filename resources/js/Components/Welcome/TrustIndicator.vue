<script setup>
defineProps({
  text: { type: String, required: true },
});
</script>

<template>
  <div class="trust-indicator">
    <svg class="trust-indicator__icon" fill="currentColor" viewBox="0 0 20 20">
      <path
        clip-rule="evenodd"
        fill-rule="evenodd"
        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
      />
    </svg>
    <span class="trust-indicator__text">{{ text }}</span>
  </div>
</template>

<style scoped>
.trust-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.trust-indicator__icon {
  width: 1.25rem;
  height: 1.25rem;
  color: var(--welcome-green-500);
  flex-shrink: 0;
}

.trust-indicator__text {
  font-size: 0.875rem;
  color: var(--welcome-gray-600);
}
</style>
