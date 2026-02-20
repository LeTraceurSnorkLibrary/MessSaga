<script setup>
defineProps({
  step: { type: Number, required: true },
  title: { type: String, required: true },
  description: { type: String, required: true },
  variant: { type: String, default: 'orange' },
});
</script>

<template>
  <div class="step">
    <div class="step__card">
      <div class="step__badge" :class="`step__badge--${variant}`">{{ step }}</div>
      <div class="step__body">
        <div class="step__icon-wrap" :class="`step__icon-wrap--${variant}`">
          <slot name="icon" />
        </div>
        <h3 class="step__title">{{ title }}</h3>
        <p class="step__description">{{ description }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.step {
  position: relative;
}

.step__card {
  position: relative;
  background: var(--welcome-white);
  border-radius: var(--welcome-radius-2xl);
  padding: 2rem;
  padding-top: 2.5rem;
  border: 2px solid var(--welcome-orange-200);
  box-shadow: var(--welcome-shadow-lg);
  transition: box-shadow var(--welcome-transition-normal);
}

.step__card:hover {
  box-shadow: var(--welcome-shadow-xl);
}

.step__badge {
  position: absolute;
  top: -1.5rem;
  left: 50%;
  transform: translateX(-50%);
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--welcome-white);
  font-weight: 700;
  font-size: 1.25rem;
  box-shadow: var(--welcome-shadow-lg);
}

.step__badge--orange {
  background: linear-gradient(135deg, var(--welcome-orange-500), var(--welcome-rose-500));
}

.step__badge--rose {
  background: linear-gradient(135deg, var(--welcome-rose-500), var(--welcome-pink-400));
}

.step__badge--amber {
  background: linear-gradient(135deg, var(--welcome-amber-500), var(--welcome-yellow-400));
}

.step__icon-wrap {
  width: 4rem;
  height: 4rem;
  border-radius: var(--welcome-radius-2xl);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
}

.step__icon-wrap :deep(svg) {
  width: 2rem;
  height: 2rem;
}

.step__icon-wrap--orange {
  background: linear-gradient(135deg, var(--welcome-orange-100), var(--welcome-rose-100));
}

.step__icon-wrap--orange :deep(svg) {
  color: var(--welcome-orange-600);
}

.step__icon-wrap--rose {
  background: linear-gradient(135deg, var(--welcome-rose-100), var(--welcome-pink-100));
}

.step__icon-wrap--rose :deep(svg) {
  color: var(--welcome-rose-600);
}

.step__icon-wrap--amber {
  background: linear-gradient(135deg, var(--welcome-amber-100), var(--welcome-yellow-100));
}

.step__icon-wrap--amber :deep(svg) {
  color: var(--welcome-amber-600);
}

.step__title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--welcome-gray-800);
  margin: 0 0 0.75rem;
  text-align: center;
}

.step__description {
  font-size: 1rem;
  color: var(--welcome-gray-600);
  text-align: center;
  margin: 0;
  line-height: 1.5;
}
</style>
