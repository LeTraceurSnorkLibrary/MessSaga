<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="guest-layout">
        <div class="guest-layout__logo">
            <Link href="/">
                <ApplicationLogo class="guest-layout__logo-img" />
            </Link>
        </div>

        <div class="guest-layout__card">
            <slot />
        </div>
    </div>
</template>

<style lang="scss" scoped>
.guest-layout {
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    align-items: center;
    padding-top: 1.5rem;
    background: var(--gray-100);

    @media (min-width: 640px) {
        justify-content: center;
        padding-top: 0;
    }

    &__logo {
        flex-shrink: 0;
    }

    &__logo-img {
        height: 5rem;
        width: 5rem;
        fill: currentColor;
        color: var(--gray-500);
    }

    &__card {
        margin-top: 1.5rem;
        width: 100%;
        overflow: hidden;
        padding: 1.5rem 1.5rem;
        background: var(--gray-0);
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);

        @media (min-width: 640px) {
            max-width: 28rem;
            border-radius: 0.5rem;
        }
    }
}

/* Отступ между блоками внутри карточки (статус и форма) */
.guest-layout__card > * + * {
    margin-top: 1rem;
}

/* Текстовые блоки и форма на гостевых страницах */
.guest-layout__card :deep(.auth-intro),
.guest-layout__card :deep(.auth-status) {
    margin-bottom: 0;
    font-size: 0.875rem;
}

.guest-layout__card :deep(.auth-intro) {
    color: var(--gray-600);
}

.guest-layout__card :deep(.auth-status) {
    font-weight: 500;
    color: var(--green-600);
}

.guest-layout__card :deep(form) > * + * {
    margin-top: 1rem;
}

.guest-layout__card :deep(.form-actions a) {
    font-size: 0.875rem;
    color: var(--gray-600);
    text-decoration: underline;
}

.guest-layout__card :deep(.form-actions a:hover) {
    color: var(--gray-900);
}

.guest-layout__card :deep(.form-remember__label) {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.guest-layout__card :deep(.form-remember__text) {
    margin-left: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.guest-layout__card :deep(.form-actions) {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
}

.guest-layout__card :deep(.form-actions--between) {
    justify-content: space-between;
}
</style>
