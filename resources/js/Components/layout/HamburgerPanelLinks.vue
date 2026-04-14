<script setup>
import {Link} from '@inertiajs/vue3';

defineProps({
    adminPanelUrl: {
        type: String,
        default: null,
    },
    close: {
        type: Function,
        required: true,
    },
});
</script>
<template>
    <div class="hamburger-panel-links">
        <a
            v-if="adminPanelUrl"
            :href="adminPanelUrl"
            class="hamburger-panel-links__item"
            @click="close"
        >
            Админ-панель
        </a>
        <Link
            :class="[
                'hamburger-panel-links__item',
                { 'hamburger-panel-links__item--active': route().current('dashboard') },
            ]"
            :href="route('dashboard')"
            @click="close"
        >
            Переписки
        </Link>
    </div>
</template>
<style lang="scss" scoped>
@use '../../../scss/components/hamburger/hamburger-panel-links';
</style>
