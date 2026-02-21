<script setup>
defineProps({
  title: { type: String, required: true },
  description: { type: String, required: true },
  variant: { type: String, default: 'orange' },
});
</script>

<template>
  <div class="why-card" :class="`why-card--${variant}`">
    <div class="why-card__icon-wrap">
      <slot name="icon" />
    </div>
    <h3 class="why-card__title">{{ title }}</h3>
    <p class="why-card__description">{{ description }}</p>
  </div>
</template>

<style scoped>
.why-card {
  border-radius: var(--welcome-radius-2xl);
  padding: 2rem;
  border: 1px solid var(--welcome-orange-100);
  background: linear-gradient(135deg, var(--welcome-orange-50), var(--welcome-amber-50));
  transition: box-shadow var(--welcome-transition-slow), transform var(--welcome-transition-slow);
}

.why-card:hover {
  box-shadow: var(--welcome-shadow-xl);
  transform: scale(1.05);
}

.why-card__icon-wrap {
  width: 4rem;
  height: 4rem;
  border-radius: var(--welcome-radius-2xl);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
  box-shadow: var(--welcome-shadow-lg);
  transition: transform var(--welcome-transition-normal);
  background: linear-gradient(135deg, var(--welcome-orange-400), var(--welcome-rose-400));
}

.why-card:hover .why-card__icon-wrap {
  transform: scale(1.1);
}

.why-card__icon-wrap :deep(svg) {
  width: 2rem;
  height: 2rem;
  color: var(--welcome-white);
}

.why-card__title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--welcome-gray-800);
  margin: 0 0 1rem;
}

.why-card__description {
  color: var(--welcome-gray-600);
  line-height: 1.6;
  margin: 0;
}

.why-card--rose {
  background: linear-gradient(135deg, var(--welcome-rose-50), var(--welcome-pink-50));
  border-color: var(--welcome-rose-100);
}

.why-card--rose .why-card__icon-wrap {
  background: linear-gradient(135deg, var(--welcome-rose-400), var(--welcome-pink-400));
}

.why-card--amber {
  background: linear-gradient(135deg, var(--welcome-amber-50), var(--welcome-yellow-50));
  border-color: var(--welcome-amber-100);
}

.why-card--amber .why-card__icon-wrap {
  background: linear-gradient(135deg, var(--welcome-amber-400), var(--welcome-yellow-400));
}

.why-card--blue {
  background: linear-gradient(135deg, var(--welcome-blue-50), var(--welcome-indigo-100));
  border-color: var(--welcome-blue-100);
}

.why-card--blue .why-card__icon-wrap {
  background: linear-gradient(135deg, var(--welcome-blue-400), var(--welcome-indigo-400));
}

.why-card--purple {
  background: linear-gradient(135deg, var(--welcome-purple-50), var(--welcome-purple-100));
  border-color: var(--welcome-purple-100);
}

.why-card--purple .why-card__icon-wrap {
  background: linear-gradient(135deg, var(--welcome-purple-400), var(--welcome-violet-400));
}

.why-card--green {
  background: linear-gradient(135deg, var(--welcome-green-50), var(--welcome-green-100));
  border-color: var(--welcome-green-100);
}

.why-card--green .why-card__icon-wrap {
  background: linear-gradient(135deg, var(--welcome-green-400), var(--welcome-emerald-400));
}
</style>
