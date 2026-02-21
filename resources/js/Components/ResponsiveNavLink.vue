<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  href: { type: String, required: true },
  active: { type: Boolean },
});

const linkClass = computed(() =>
  props.active ? 'resp-nav-link resp-nav-link--active' : 'resp-nav-link'
);
</script>

<template>
  <Link :href="href" :class="linkClass">
    <slot />
  </Link>
</template>

<style scoped>
.resp-nav-link {
  display: block;
  width: 100%;
  padding: 0.5rem 0.75rem 0.5rem 1rem;
  border-left: 4px solid transparent;
  font-size: 1rem;
  font-weight: 500;
  text-align: start;
  color: var(--welcome-gray-600);
  text-decoration: none;
  transition: color 150ms ease, background 150ms ease, border-color 150ms ease;
}

.resp-nav-link:hover {
  color: var(--welcome-gray-800);
  background: var(--welcome-gray-50);
  border-color: var(--welcome-gray-300);
}

.resp-nav-link--active {
  border-color: var(--welcome-orange-500);
  color: var(--welcome-orange-600);
  background: var(--welcome-orange-50);
}

.resp-nav-link--active:hover {
  color: var(--welcome-orange-600);
  background: var(--welcome-orange-50);
}
</style>
