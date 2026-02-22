<script setup>
import {Link} from '@inertiajs/vue3';

defineProps({
    href: {type: String, required: true},
    method: {type: String, default: 'get'},
    as: {type: String, default: 'a'},
});
</script>

<template>
    <Link
        :as="as"
        :href="href"
        :method="method"
        class="dropdown-link"
    >
        <slot/>
    </Link>
</template>

<style scoped>
.dropdown-link {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    line-height: 1.25;
    text-align: start;
    color: var(--gray-700);
    text-decoration: none;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: background 150ms ease;
}

.dropdown-link:hover {
    background: var(--gray-100);
}
</style>
