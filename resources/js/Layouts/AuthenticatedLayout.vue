<script setup>
import NavMenu from "@/Components/base/NavMenu.vue";
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import HamburgerPanel from '@/Components/layout/HamburgerPanel.vue';
import Hamburger from '@/Components/layout/Hamburger.vue';
import TariffQuotaProgressBar from '@/Components/layout/TariffQuotaProgressBar.vue';
import HomeIcon from "@/Components/particles/icons/HomeIcon.vue";
import {Link, usePage} from '@inertiajs/vue3';
import {computed} from 'vue';
import AdminPanelIcon from '@/Components/particles/icons/AdminPanelIcon.vue';

const page = usePage();
const adminPanelUrl = computed(() => page.props.filament?.adminPanelUrl ?? null);
const quota = computed(() => page.props.auth?.quota ?? null);
</script>
<template>
    <div class="auth-layout">
        <div class="auth-layout__screen">
            <nav class="auth-layout__nav">
                <div class="auth-layout__nav-inner">
                    <div class="auth-layout__logo">
                        <div class="auth-layout__logo-cluster">
                            <Link class="auth-layout__logo-link" href="/" title="На главную">
                                <HomeIcon class="auth-layout__logo-svg"/>
                            </Link>
                        </div>
                    </div>

                    <div class="auth-layout__menu">
                        <NavMenu :items="[
                            {label: 'Переписки', href: route('dashboard')},
                            {label: 'Профиль', href: route('profile.edit')},
                        ]"/>
                    </div>

                    <div class="auth-layout__user">
                        <TariffQuotaProgressBar :quota="quota" />
                        <a
                            v-if="adminPanelUrl"
                            :href="adminPanelUrl"
                            aria-label="Админ-панель"
                            class="auth-layout__admin-link"
                            title="Админ-панель"
                        >
                            <AdminPanelIcon/>
                        </a>
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="auth-layout__trigger-wrap">
                                    <button class="auth-layout__trigger" type="button">
                                        {{ $page.props.auth.user.name }}
                                        <svg class="auth-layout__trigger-icon" fill="currentColor" viewBox="0 0 20 20"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                clip-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                fill-rule="evenodd"
                                            />
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
                        <Hamburger>
                            <template #default="{ close }">
                                <HamburgerPanel
                                    :admin-panel-url="adminPanelUrl"
                                    :close="close"
                                    :user="$page.props.auth.user"
                                />
                            </template>
                        </Hamburger>
                    </div>
                </div>
            </nav>

            <header v-if="$slots.header" class="auth-layout__page-header">
                <div class="auth-layout__page-header-inner">
                    <slot name="header"/>
                </div>
            </header>

            <main class="auth-layout__main">
                <slot/>
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
    display: flex;
    align-items: center;
    column-gap: 2rem;
    min-height: 4rem;
    max-width: 80rem;
    margin: 0 auto;
    padding: 0 1rem;

    @media (min-width: 768px) {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    @media (min-width: 1024px) {
        padding-left: 2rem;
        padding-right: 2rem;
    }
}

.auth-layout__menu {
    display: none;
    align-items: center;

    @media (min-width: 768px) {
        display: flex;
    }
}

.auth-layout__logo {
    flex-shrink: 0;
}

.auth-layout__logo-cluster {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.auth-layout__logo-link {
    display: block;
}

.auth-layout__logo-svg {
    height: 2.25rem;
    width: auto;
}

.auth-layout__admin-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 28px;
    width: 28px;
    color: var(--gray-500);
    transition: color .2s ease;

    &:hover {
        color: var(--orange-600);
    }
}

.auth-layout__user {
    display: none;
    margin-left: auto;
    column-gap: 0.75rem;

    @media (min-width: 768px) {
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
    margin-left: auto;

    @media (min-width: 768px) {
        display: none;
    }
}

.auth-layout__page-header {
    background: var(--gray-0);
    box-shadow: var(--shadow-sm);
}

.auth-layout__page-header-inner {
    max-width: 80rem;
    margin: 0 auto;
    padding: 1.5rem 1rem;

    @media (min-width: 768px) {
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
