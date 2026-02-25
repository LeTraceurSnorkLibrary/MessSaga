<script setup>
import {computed, useAttrs} from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'tertiary', 'danger'].includes(v),
    },
    type: {
        type: String,
        default: 'button',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    isShadow: {
        type: Boolean,
        default: false,
    },
    isSlightHover: {
        type: Boolean,
        default: false,
    },
    isStroke: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'l',
        validator: (v) => ['l', 'm', 's'].includes(v),
    },
    tag: {
        type: String,
        default: 'button',
        validator: (v) => ['button', 'a'].includes(v),
    },
    w100: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['click']);

const attrs = useAttrs();
const mergedAttrs = computed(() => ({
    ...attrs,
    ...(props.tag === 'button'
        ? { type: props.type, disabled: props.disabled }
        : {}),
}));
</script>
<template>
    <Component
        :is="tag"
        :class="[
            `button--${variant}`,
            `button--${size}`,
            { 'button--w100': w100 },
            {'button--with-shadow': isShadow},
            {'button--stroke': isStroke},
            {'button--slight-hover': isSlightHover},
        ]"
        class="button"
        v-bind="mergedAttrs"
        @click="$emit('click', $event)"
    >
        <span class="button__content">
            <slot />
        </span>
    </Component>
</template>
<style lang="scss" scoped>
@use '../../scss/typography' as typography;

.button {
    --button-font-size: 1rem;
    --button-line-height: 1.5;
    --button-color: var(--gray-0);
    --button-color-disabled: var(--gray-500);
    --button-bg-color: var(--rose-500);
    --button-bg-color-interaction: var(--rose-600);
    --button-bg-color-disabled: var(--gray-400);
    --button-bg-image: linear-gradient(90deg, var(--orange-500), var(--rose-500));
    --button-bg-image-interaction: none;
    --button-bg-image-disabled: none;
    --button-padding-top-bottom: 0.75rem;
    --button-padding-left-right: 1.5rem;
    --button-border-width: 2px;
    --button-border-color: transparent;
    --button-outline-color: var(--gray-600);
    --button-border-color-interaction: transparent;
    --button-box-shadow-interaction: none;

    @include typography.text(var(--button-font-size), var(--button-line-height), typography.$font-weight--underbold);

    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: calc(var(--button-padding-top-bottom) - var(--button-border-width)) calc(var(--button-padding-left-right) - var(--button-border-width));
    color: var(--button-color);
    text-decoration: none;
    white-space: nowrap;
    border: var(--button-border-width) solid var(--button-border-color);
    border-radius: 64px;
    background-color: var(--button-bg-color);
    background-image: var(--button-bg-image);
    background-position: calc(0px - var(--button-border-width));
    background-size: calc(100% + 2 * var(--button-border-width));
    transition: background-color var(--transition-fast),
    border-color var(--transition-fast),
    color var(--transition-fast),
    box-shadow var(--transition-fast);
    user-select: none;

    @media (min-width: 768px) {
        --button-padding-top-bottom: 1rem;
        --button-padding-left-right: 2rem;
    }

    &:hover:not(:disabled) {
        border-color: var(--button-border-color-interaction);
        box-shadow: var(--button-box-shadow-interaction);
    }

    &:hover:not(:disabled):not(&--slight-hover) {
        background-color: var(--button-bg-color-interaction);
        background-image: var(--button-bg-image-interaction);
    }

    &:focus-visible {
        outline: var(--button-border-width) solid var(--button-outline-color);
        outline-offset: 2px;
    }

    &:disabled {
        --button-color: var(--button-color-disabled);
        --button-bg-color: var(--button-bg-color-disabled);
        --button-bg-image: var(--button-bg-image-disabled);

        cursor: not-allowed;
        opacity: 0.8;
    }

    &--l {
        --button-font-size: 1rem;
        --button-padding-top-bottom: 0.75rem;
        --button-padding-left-right: 1.5rem;
        --button-border-width: 2px;

        @media (min-width: 768px) {
            --button-padding-top-bottom: 1rem;
            --button-padding-left-right: 2rem;
        }
    }

    &--m {
        --button-font-size: 0.875rem;
        --button-padding-top-bottom: 0.5rem;
        --button-padding-left-right: 1rem;
        --button-border-width: 2px;

        @media (min-width: 768px) {
            --button-padding-top-bottom: 0.75rem;
            --button-padding-left-right: 1.5rem;
        }
    }

    &--s {
        --button-font-size: 0.75rem;
        --button-padding-top-bottom: 0.375rem;
        --button-padding-left-right: 0.75rem;
        --button-border-width: 1px;

        @media (min-width: 768px) {
            --button-padding-top-bottom: 0.5rem;
            --button-padding-left-right: 1rem;
        }
    }

    &--w100 {
        width: 100%;
    }

    &--stroke {
        --button-border-color: var(--button-bg-color);
        --button-bg-color-interaction: transparent;

        color: var(--button-bg-color);
        background-color: transparent;
        background-image: none;

        &:hover:not(:disabled):not(.button--slight-hover) {
            background-color: var(--button-bg-color);
            background-image: var(--button-bg-image);
        }
    }

    &--secondary {
        --button-color: var(--gray-700);
        --button-color-disabled: var(--gray-500);
        --button-bg-color: var(--gray-0);
        --button-bg-color-interaction: var(--gray-0);
        --button-bg-color-disabled: var(--gray-200);
        --button-bg-image: none;
        --button-bg-image-interaction: none;
        --button-bg-image-disabled: none;
        --button-border-color: var(--orange-200);
        --button-border-color-interaction: var(--orange-400);
    }

    &--tertiary {
        --button-color: var(--gray-700);
        --button-color-disabled: var(--gray-500);
        --button-bg-color: var(--gray-0);
        --button-bg-color-interaction: var(--gray-200);
        --button-bg-color-disabled: var(--gray-200);
        --button-bg-image: none;
        --button-bg-image-interaction: none;
        --button-bg-image-disabled: none;
        --button-border-color: var(--gray-0);
        --button-border-color-interaction: var(--gray-200);
    }

    &--danger {
        --button-color: var(--gray-0);
        --button-color-disabled: var(--gray-500);
        --button-bg-color: var(--rose-600);
        --button-bg-color-interaction: var(--rose-700);
        --button-bg-color-disabled: var(--gray-400);
        --button-bg-image: none;
        --button-bg-image-interaction: none;
        --button-bg-image-disabled: none;
        --button-border-color: transparent;
        --button-border-color-interaction: transparent;
    }

    &--with-shadow {
        --button-box-shadow-interaction: 0 10px 15px -3px var(--gray-200), 0 4px 6px -4px var(--gray-200);
    }
}

.button__content {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    height: calc(var(--button-font-size) * var(--button-line-height));
}
</style>
