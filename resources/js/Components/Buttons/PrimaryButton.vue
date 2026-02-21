<script setup>
defineProps({
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
});
</script>

<template>
  <button
    :type="type"
    class="primary-btn"
    :disabled="disabled"
    @click="$emit('click')"
  >
    <slot />
  </button>
</template>

<style scoped>
.primary-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--welcome-white);
  background: var(--welcome-gray-800);
  border: 1px solid transparent;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: background 150ms ease, box-shadow 150ms ease;
}

.primary-btn:hover:not(:disabled) {
  background: var(--welcome-gray-700);
}

.primary-btn:focus {
  outline: none;
  box-shadow: 0 0 0 2px var(--welcome-white), 0 0 0 4px var(--welcome-indigo-500);
}

.primary-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
