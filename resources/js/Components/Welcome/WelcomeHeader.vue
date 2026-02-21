<script setup>
import {Link} from '@inertiajs/vue3';
import WelcomeLogo from './WelcomeLogo.vue';
import LoginIcon from "@/Components/particles/icons/LoginIcon.vue";

defineProps({
    canLogin: {type: Boolean, default: false},
    canRegister: {type: Boolean, default: false},
});
</script>
<template>
    <header class="welcome-header">
        <nav class="welcome-header__nav">
            <WelcomeLogo to="/"/>
            <div v-if="canLogin" class="welcome-header__links">
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    aria-label="Войти"
                    class="welcome-header__link"
                >
                    <LoginIcon class="welcome-header__icon welcome-header__icon--mobile"/>
                    <span class="welcome-header__text">Войти</span>
                </Link>
            </div>
        </nav>
    </header>
</template>
<style lang="scss" scoped>
.welcome-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 50;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--orange-100);
}

.welcome-header__nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 72rem;
    margin: 0 auto;
    padding: 1rem 1.5rem;
}

.welcome-header__links {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.welcome-header__link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray-700);
    font-weight: 500;
    text-decoration: none;
    transition: color var(--transition-fast);
    padding: 0.5rem;

    &:hover {
        color: var(--orange-600);
    }

    @media (min-width: 768px) {
        padding: 0;
    }
}

.welcome-header__icon {
    width: 24px;
    height: 24px;
}

.welcome-header__text {
    display: none;

    @media (min-width: 768px) {
        display: inline;
    }
}
</style>
