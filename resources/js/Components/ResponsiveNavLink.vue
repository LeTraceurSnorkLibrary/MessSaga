<script setup>
import {computed} from 'vue';
import {Link} from '@inertiajs/vue3';

const props = defineProps({
    href: {type: String, required: true},
    active: {type: Boolean},
});

const linkClass = computed(() =>
    props.active ? 'resp-nav-link resp-nav-link--active' : 'resp-nav-link'
);
</script>

<template>
    <Link :class="linkClass" :href="href">
        <slot/>
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
    color: var(--gray-600);
    text-decoration: none;
    transition: color 150ms ease, background 150ms ease, border-color 150ms ease;
}

.resp-nav-link:hover {
    color: var(--gray-800);
    background: var(--gray-50);
    border-color: var(--gray-300);
}

.resp-nav-link--active {
    border-color: var(--orange-500);
    color: var(--orange-600);
    background: var(--orange-50);
}

.resp-nav-link--active:hover {
    color: var(--orange-600);
    background: var(--orange-50);
}
</style>
