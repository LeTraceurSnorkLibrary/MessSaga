<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  href: { type: String, required: true },
  active: { type: Boolean },
});

const linkClass = computed(() =>
  props.active ? 'nav-link nav-link--active' : 'nav-link'
);
</script>

<template>
  <Link :href="href" :class="linkClass">
    <slot />
  </Link>
</template>

<style scoped>
.nav-link {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.25rem 0.25rem 0;
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
  border-bottom: 2px solid transparent;
  font-size: 0.875rem;
  font-weight: 500;
  line-height: 1.25;
  color: var(--welcome-gray-500);
  text-decoration: none;
  transition: color 150ms ease, border-color 150ms ease;
}

.nav-link:hover {
  color: var(--welcome-gray-700);
  border-color: var(--welcome-gray-300);
}

.nav-link--active {
  border-color: var(--welcome-orange-500);
  color: var(--welcome-gray-800);
}
</style>
