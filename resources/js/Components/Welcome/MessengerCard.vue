<script setup>
defineProps({
  name: { type: String, required: true },
  subtitle: { type: String, default: null },
  variant: { type: String, default: 'blue' },
  placeholder: { type: Boolean, default: false },
});
</script>

<template>
  <div
    class="messenger-card"
    :class="[
      `messenger-card--${variant}`,
      { 'messenger-card--placeholder': placeholder },
    ]"
  >
    <div class="messenger-card__icon">
      <slot name="icon">
        <span v-if="placeholder" class="messenger-card__placeholder-icon">+</span>
      </slot>
    </div>
    <div class="messenger-card__name">{{ name }}</div>
    <div v-if="subtitle && !placeholder" class="messenger-card__subtitle">{{ subtitle }}</div>
  </div>
</template>

<style scoped>
.messenger-card {
  background: var(--gray-0);
  border-radius: var(--radius-xl);
  padding: 1rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--blue-100);
  transition: transform var(--transition-normal);
}

.messenger-card:hover {
  transform: scale(1.05);
}

.messenger-card__icon {
  width: 3rem;
  height: 3rem;
  border-radius: var(--radius-xl);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.75rem;
}

.messenger-card__icon :deep(svg) {
  width: 1.75rem;
  height: 1.75rem;
  color: var(--gray-0);
}

.messenger-card--blue .messenger-card__icon { background: var(--blue-500); }
.messenger-card--green .messenger-card__icon { background: var(--green-500); }
.messenger-card--indigo .messenger-card__icon { background: var(--indigo-500); }
.messenger-card--purple .messenger-card__icon { background: var(--purple-300); }

.messenger-card--green { border-color: var(--green-100); }
.messenger-card--indigo { border-color: var(--indigo-100); }
.messenger-card--purple { border-color: var(--purple-100); }

.messenger-card__name {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--gray-700);
}

.messenger-card__subtitle {
  font-size: 0.75rem;
  color: var(--gray-500);
  margin-top: 0.25rem;
}

.messenger-card--placeholder {
  opacity: 0.6;
  border-color: var(--purple-100);
}

.messenger-card--placeholder .messenger-card__icon {
  background: var(--purple-300);
}

.messenger-card--placeholder .messenger-card__name {
  color: var(--gray-600);
}

.messenger-card__placeholder-icon {
  font-size: 1.5rem;
  color: var(--gray-0);
  line-height: 1;
}
</style>
