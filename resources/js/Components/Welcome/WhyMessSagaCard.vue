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
  border-radius: var(--radius-2xl);
  padding: 2rem;
  border: 1px solid var(--orange-100);
  background: linear-gradient(135deg, var(--orange-50), var(--amber-50));
  transition: box-shadow var(--transition-slow), transform var(--transition-slow);
}

.why-card:hover {
  box-shadow: var(--shadow-xl);
  transform: scale(1.05);
}

.why-card__icon-wrap {
  width: 4rem;
  height: 4rem;
  border-radius: var(--radius-2xl);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
  box-shadow: var(--shadow-lg);
  transition: transform var(--transition-normal);
  background: linear-gradient(135deg, var(--orange-400), var(--rose-400));
}

.why-card:hover .why-card__icon-wrap {
  transform: scale(1.1);
}

.why-card__icon-wrap :deep(svg) {
  width: 2rem;
  height: 2rem;
  color: var(--gray-0);
}

.why-card__title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--gray-800);
  margin: 0 0 1rem;
}

.why-card__description {
  color: var(--gray-600);
  line-height: 1.6;
  margin: 0;
}

.why-card--rose {
  background: linear-gradient(135deg, var(--rose-50), var(--pink-50));
  border-color: var(--rose-100);
}

.why-card--rose .why-card__icon-wrap {
  background: linear-gradient(135deg, var(--rose-400), var(--pink-400));
}

.why-card--amber {
  background: linear-gradient(135deg, var(--amber-50), var(--yellow-50));
  border-color: var(--amber-100);
}

.why-card--amber .why-card__icon-wrap {
  background: linear-gradient(135deg, var(--amber-400), var(--yellow-400));
}

.why-card--blue {
  background: linear-gradient(135deg, var(--blue-50), var(--indigo-100));
  border-color: var(--blue-100);
}

.why-card--blue .why-card__icon-wrap {
  background: linear-gradient(135deg, var(--blue-400), var(--indigo-400));
}

.why-card--purple {
  background: linear-gradient(135deg, var(--purple-50), var(--purple-100));
  border-color: var(--purple-100);
}

.why-card--purple .why-card__icon-wrap {
  background: linear-gradient(135deg, var(--purple-400), var(--violet-400));
}

.why-card--green {
  background: linear-gradient(135deg, var(--green-50), var(--green-100));
  border-color: var(--green-100);
}

.why-card--green .why-card__icon-wrap {
  background: linear-gradient(135deg, var(--green-400), var(--emerald-400));
}
</style>
