<script setup>
import {Link} from '@inertiajs/vue3';
import LogoutIcon from '@/Components/particles/icons/LogoutIcon.vue';

defineProps({
    user: {
        type: Object,
        required: true,
    },
    close: {
        type: Function,
        required: true,
    },
});
</script>
<template>
    <div class="hamburger-panel-user">
        <Link :href="route('profile.edit')" class="hamburger-panel-user__profile" @click="close">
            <div class="hamburger-panel-user__label">Перейти в профиль:</div>
            <div class="hamburger-panel-user__name">{{ user.name }}</div>
            <div class="hamburger-panel-user__email">{{ user.email }}</div>
        </Link>
        <Link :href="route('logout')" as="button" class="hamburger-panel-user__logout" method="post" @click="close">
            <span class="hamburger-panel-user__logout-text">Выйти</span>
            <LogoutIcon class="hamburger-panel-user__logout-icon"/>
        </Link>
    </div>
</template>
<style lang="scss" scoped>
@use '../../../scss/components/hamburger/hamburger-panel-user';
</style>
