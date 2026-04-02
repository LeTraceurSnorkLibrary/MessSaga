<script setup>
import {computed} from 'vue';
import {Link} from '@inertiajs/vue3';

const props = defineProps({
    theme: {
        type: String,
        default: '',
        validator: (v) => ['primary', 'muted'].includes(v),
    },
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'dashed', 'underlined'].includes(v),
    },
});

const classes = computed(() => [
    'ui-link',
    `ui-link--${props.theme}`,
    `ui-link--${props.variant}`,
]);
</script>

<template>
    <Link :class="classes" v-bind="$attrs">
        <slot/>
    </Link>
</template>

<style lang="scss" scoped>
@use '../../scss/typography' as typography;

.ui-link {
    @include typography.text--150(0.875rem);

    color: var(--blue-600);
    text-underline-offset: 0.2em;
    text-decoration-thickness: 1px;
    transition: color var(--transition-fast), text-decoration-color var(--transition-fast);

    &:hover {
        color: var(--blue-900);
    }

    &:focus-visible {
        outline: 2px solid var(--gray-600);
        outline-offset: 2px;
        border-radius: 4px;
    }

    &--underlined {
        text-decoration: underline;
    }

    &--primary {
        color: var(--rose-600);

        &:hover {
            color: var(--rose-700);
        }
    }

    &--muted {
        color: var(--gray-600);

        &:hover {
            color: var(--gray-900);
        }
    }
}
</style>
