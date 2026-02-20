<script setup>
defineProps({
  text: { type: String, required: true },
});
</script>

<template>
  <div class="welcome-badge">
    <span class="welcome-badge__dot" />
    <span class="welcome-badge__text">{{ text }}</span>
  </div>
</template>

<style scoped>
.welcome-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(4px);
  border-radius: var(--welcome-radius-full);
  border: 1px solid var(--welcome-orange-200);
  box-shadow: var(--welcome-shadow-sm);
}

.welcome-badge__dot {
  width: 0.5rem;
  height: 0.5rem;
  background: var(--welcome-green-500);
  border-radius: 50%;
  animation: pulse 2s ease-in-out infinite;
}

.welcome-badge__text {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--welcome-gray-700);
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>
