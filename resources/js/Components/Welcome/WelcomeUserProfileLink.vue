<script setup>
import {Link} from '@inertiajs/vue3';
import LoginIcon from '@/Components/particles/icons/LoginIcon.vue';
import UserCircleIcon from '@/Components/particles/icons/UserCircleIcon.vue';

defineProps({
    name: {
        type: String,
        required: true
    },
});
</script>

<template>
    <Link
        :href="route('dashboard')"
        aria-label="В личный кабинет"
        class="welcome-user-profile-link"
    >
        <UserCircleIcon class="welcome-user-profile-link__avatar"/>
        <span class="welcome-user-profile-link__name">{{ name }}</span>
        <span class="welcome-user-profile-link__hint">
            <span>В личный кабинет</span>
            <LoginIcon class="welcome-user-profile-link__hint-icon"/>
        </span>
    </Link>
</template>

<style lang="scss" scoped>
@use '../../../scss/typography' as typography;

.welcome-user-profile-link {
    display: grid;
    grid-template:
    'AVATAR NAME'
    'LINK   LINK';
    align-items: center;
    gap: 0.2rem;
    text-decoration: none;
    color: var(--gray-700);
    transition: color var(--transition-fast), transform var(--transition-fast);

    &:hover {
        color: var(--orange-600);
        transform: translateY(-1px);
    }
}

.welcome-user-profile-link__avatar {
    grid-area: AVATAR;
    width: 28px;
    height: 28px;
}

.welcome-user-profile-link__name {
    @include typography.text--150(0.875rem, typography.$font-weight--bold);

    grid-area: NAME;
    display: flex;
    max-width: 9rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.welcome-user-profile-link__hint {
    @include typography.text--150(0.75rem);

    grid-area: LINK;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--gray-500);
}

.welcome-user-profile-link__hint-icon {
    width: 14px;
    height: 14px;
}
</style>
