<script setup>
import {Link, usePage} from '@inertiajs/vue3';

const props = defineProps({
    /**
     * Элементы меню
     * [{ label: 'Главная', href: '/' }]
     */
    items: {
        type: Array,
        required: true,
        validator: (items) => items.every(item => item.label && item.href),
    },
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'pills', 'underline', 'gradient'].includes(v),
    },
    size: {
        type: String,
        default: 'm',
        validator: (v) => ['s', 'm', 'l'].includes(v),
    },
    fullWidth: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

const isActive = (item) => {
    if (item.active !== undefined) {
        return item.active;
    }

    const itemPath = new URL(item.href, window.location.origin).pathname;
    const currentPath = page.url;

    return currentPath === itemPath ||
        (item.matchPrefix && currentPath.startsWith(itemPath + '/'));
};
</script>
<template>
    <nav
        :class="[
            `nav-menu--${variant}`,
            `nav-menu--${size}`,
            { 'nav-menu--full-width': fullWidth },
        ]"
        class="nav-menu"
    >
        <Link
            v-for="(item, index) in items"
            :key="index"
            :aria-current="isActive(item) ? 'page' : undefined"
            :class="{ 'nav-menu__item--active': isActive(item) }"
            :href="item.href"
            class="nav-menu__item"
        >
            <span class="nav-menu__label">{{ item.label }}</span>
        </Link>
    </nav>
</template>
<style lang="scss" scoped>
@use '../../../scss/typography' as typography;

.nav-menu {
    --nav-item-font-size: 0.875rem;
    --nav-item-color: var(--gray-700);
    --nav-item-bg-color: transparent;
    --nav-item-bg-color-hover: transparent;
    --nav-item-border-color: transparent;
    --nav-item-border-color-active: var(--orange-500);
    --nav-item-padding-y: 0.5rem;
    --nav-item-padding-x: 1rem;
    --nav-item-border-radius: 0;
    --nav-item-gap: 0.5rem;

    display: flex;
    align-items: center;
    gap: 0.25rem;

    @media (min-width: 768px) {
        gap: 0.5rem;
    }

    &--full-width {
        width: 100%;

        .nav-menu__item {
            flex: 1;
            justify-content: center;
        }
    }

    &--l {
        --nav-item-font-size: 1rem;
        --nav-item-padding-y: 0.75rem;
        --nav-item-padding-x: 1.5rem;
        --nav-item-gap: 0.625rem;
    }

    &--m {
        --nav-item-font-size: 0.875rem;
        --nav-item-padding-y: 0.5rem;
        --nav-item-padding-x: 1rem;
        --nav-item-gap: 0.5rem;
    }

    &--s {
        --nav-item-font-size: 0.75rem;
        --nav-item-padding-y: 0.375rem;
        --nav-item-padding-x: 0.75rem;
        --nav-item-gap: 0.375rem;
    }

    &--pills {
        gap: 0.5rem;

        .nav-menu__item {
            border-radius: 9999px;

            &--active {
                background: linear-gradient(90deg, var(--orange-500), var(--rose-500));
                color: var(--gray-0);
                box-shadow: 0 4px 6px -1px rgba(251, 146, 60, 0.3);
            }
        }
    }

    &--gradient {
        padding: 0.25rem;
        background: linear-gradient(90deg, var(--orange-50), var(--rose-50), var(--amber-50));
        border-radius: 9999px;

        .nav-menu__item {
            &--active {
                background: var(--gray-0);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
        }
    }
}

.nav-menu__item {
    @include typography.text--150(var(--nav-item-font-size), typography.$font-weight--semibold);

    display: inline-flex;
    align-items: center;
    gap: var(--nav-item-gap);
    padding: var(--nav-item-padding-y) var(--nav-item-padding-x);
    padding-bottom: calc(var(--nav-item-padding-y) + 2px);
    text-decoration: none;
    white-space: nowrap;
    color: var(--nav-item-color);
    border-bottom: 2px solid var(--nav-item-border-color);
    border-radius: var(--nav-item-border-radius);
    background-color: var(--nav-item-bg-color);
    transition: all var(--transition-fast);
    cursor: pointer;
    user-select: none;

    &:hover {
        color: var(--orange-600);
        background-color: var(--nav-item-bg-color-hover);
        border-bottom-color: var(--orange-300);
    }

    &:focus-visible {
        outline: 2px solid var(--orange-500);
        outline-offset: 2px;
    }

    &--active {
        font-weight: typography.$font-weight--underbold;
        border-bottom-color: var(--nav-item-border-color-active);

        .nav-menu__icon {
            transform: scale(1.1);
        }
    }
}

.nav-menu__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.25em;
    height: 1.25em;
    flex-shrink: 0;
    transition: transform var(--transition-fast);
}

.nav-menu__icon :deep(svg) {
    width: 100%;
    height: 100%;
}

.nav-menu__label {
    display: inline-block;
}
</style>
