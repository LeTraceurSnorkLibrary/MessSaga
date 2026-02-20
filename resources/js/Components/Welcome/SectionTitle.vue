<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: null },
  accent: { type: String, default: null },
});
</script>

<template>
  <div class="section-title">
    <h2 class="section-title__heading">
      <template v-if="accent">
        {{ title }}
        <span class="section-title__accent">{{ accent }}</span>
      </template>
      <template v-else>{{ title }}</template>
    </h2>
    <p v-if="subtitle" class="section-title__subtitle">{{ subtitle }}</p>
  </div>
</template>

<style scoped>
.section-title {
  text-align: center;
  margin-bottom: 4rem;
}

.section-title__heading {
  font-size: clamp(2.25rem, 4vw, 3rem);
  font-weight: 700;
  color: var(--welcome-gray-800);
  margin: 0 0 1rem;
}

.section-title__accent {
  background: linear-gradient(90deg, var(--welcome-orange-600), var(--welcome-rose-600));
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.section-title__subtitle {
  font-size: 1.25rem;
  color: var(--welcome-gray-600);
  max-width: 42rem;
  margin: 0 auto;
  line-height: 1.5;
}
</style>
