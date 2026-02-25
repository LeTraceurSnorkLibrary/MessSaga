<script setup>
import NavMenu from "@/Components/base/NavMenu.vue";
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import HomeIcon from "@/Components/particles/icons/HomeIcon.vue";
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import {Link} from '@inertiajs/vue3';
import {onMounted, onUnmounted, ref} from 'vue';

const showingNavigationDropdown = ref(false);

const closeOnEscape = (e) => {
    if (e.key === 'Escape') showingNavigationDropdown.value = false;
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));
</script>
<template>
    <div class="auth-layout">
        <div class="auth-layout__screen">
            <nav class="auth-layout__nav">
                <div class="auth-layout__nav-inner">
                    <div class="auth-layout__logo">
                        <Link href="/">
                            <HomeIcon class="auth-layout__logo-svg" />
                        </Link>
                    </div>

                    <div class="auth-layout__menu">
                        <NavMenu :items="[
                            {label: 'Переписки', href: route('dashboard')},
                            {label: 'Профиль', href: route('profile.edit')},
                        ]"></NavMenu>
                    </div>

                    <div class="auth-layout__user">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="auth-layout__trigger-wrap">
                                  <button class="auth-layout__trigger" type="button">
                                    {{ $page.props.auth.user.name }}
                                    <svg class="auth-layout__trigger-icon" fill="currentColor" viewBox="0 0 20 20"
                                         xmlns="http://www.w3.org/2000/svg">
                                      <path clip-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            fill-rule="evenodd" />
                                    </svg>
                                  </button>
                                </span>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Мой профиль</DropdownLink>
                                <DropdownLink :href="route('logout')" as="button" method="post">Выход</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <div class="auth-layout__hamburger">
                        <button
                            aria-label="Меню"
                            class="auth-layout__hamburger-btn"
                            type="button"
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                        >
                            <svg v-show="!showingNavigationDropdown" class="auth-layout__hamburger-icon"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                            <svg v-show="showingNavigationDropdown" class="auth-layout__hamburger-icon"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div
                    :class="{ 'auth-layout__mobile--open': showingNavigationDropdown }"
                    class="auth-layout__mobile"
                >
                    <div class="auth-layout__mobile-links">
                        <ResponsiveNavLink :active="route().current('dashboard')" :href="route('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>
                    <div class="auth-layout__mobile-user">
                        <div class="auth-layout__mobile-name">{{ $page.props.auth.user.name }}</div>
                        <div class="auth-layout__mobile-email">{{ $page.props.auth.user.email }}</div>
                        <div class="auth-layout__mobile-actions">
                            <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" as="button" method="post">Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header v-if="$slots.header" class="auth-layout__page-header">
                <div class="auth-layout__page-header-inner">
                    <slot name="header" />
                </div>
            </header>

            <main class="auth-layout__main">
                <slot />
            </main>
        </div>
    </div>
</template>
<style scoped>
.auth-layout {
    min-height: 100vh;
}

.auth-layout__screen {
    min-height: 100vh;
    background: var(--gray-100);
}

.auth-layout__nav {
    border-bottom: 1px solid var(--gray-100);
    background: var(--gray-0);
}

.auth-layout__nav-inner {
    max-width: 80rem;
    margin: 0 auto;
    padding: 0 1rem;

    @media (min-width: 640px) {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    @media (min-width: 1024px) {
        padding-left: 2rem;
        padding-right: 2rem;
    }
}

.auth-layout__nav-inner {
    display: flex;
    align-items: center;
    column-gap: 2rem;
    height: 4rem;
}

.auth-layout__logo {
    flex-shrink: 0;
}

.auth-layout__logo :deep(a) {
    display: block;
}

.auth-layout__logo-svg {
    height: 2.25rem;
    width: auto;
}

.auth-layout__user {
    display: none;
    margin-left: auto;

    @media (min-width: 640px) {
        display: flex;
        align-items: center;
    }
}

.auth-layout__trigger-wrap {
    display: inline-flex;
    border-radius: 0.375rem;
}

.auth-layout__trigger {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1.25;
    color: var(--gray-500);
    background: transparent;
    border: 1px solid transparent;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: color 150ms ease, border-color 150ms ease;

    &:hover {
        color: var(--gray-700);
    }
}

.auth-layout__trigger-icon {
    margin-left: 0.5rem;
    margin-right: -0.125rem;
    width: 1rem;
    height: 1rem;
}

.auth-layout__hamburger {
    display: flex;
    align-items: center;
    margin-right: -0.5rem;

    @media (min-width: 640px) {
        display: none;
    }
}

.auth-layout__hamburger-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    color: var(--gray-400);
    background: transparent;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: color 150ms ease, background 150ms ease;
}

.auth-layout__hamburger-btn:hover {
    color: var(--gray-500);
    background: var(--gray-100);
}

.auth-layout__hamburger-icon {
    width: 1.5rem;
    height: 1.5rem;
}

.auth-layout__mobile {
    display: block;
    padding-bottom: 0.75rem;
    padding-top: 0.5rem;

    @media (min-width: 640px) {
        display: none;
    }
}

.auth-layout__mobile:not(.auth-layout__mobile--open) {
    display: none;
}

.auth-layout__mobile-links {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.auth-layout__mobile-user {
    border-top: 1px solid var(--gray-200);
    padding: 1rem 1rem 0.25rem;
    margin-top: 1rem;
}

.auth-layout__mobile-name {
    font-size: 1rem;
    font-weight: 500;
    color: var(--gray-800);
}

.auth-layout__mobile-email {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-500);
}

.auth-layout__mobile-actions {
    margin-top: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.auth-layout__page-header {
    background: var(--gray-0);
    box-shadow: var(--shadow-sm);
}

.auth-layout__page-header-inner {
    max-width: 80rem;
    margin: 0 auto;
    padding: 1.5rem 1rem;

    @media (min-width: 640px) {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    @media (min-width: 1024px) {
        padding-left: 2rem;
        padding-right: 2rem;
    }
}

.auth-layout__main {
    flex: 1;
}
</style>
