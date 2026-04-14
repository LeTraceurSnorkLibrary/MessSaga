<script setup>
import HamburgerPanelLinks from '@/Components/layout/HamburgerPanelLinks.vue';
import HamburgerPanelUser from '@/Components/layout/HamburgerPanelUser.vue';

defineProps({
    adminPanelUrl: {
        type: String,
        default: null,
    },
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
    <div class="hamburger-panel">
        <div class="hamburger-panel__links-layout">
            <HamburgerPanelLinks :admin-panel-url="adminPanelUrl" :close="close"/>
        </div>
        <div class="hamburger-panel__user">
            <HamburgerPanelUser :close="close" :user="user"/>
        </div>
    </div>
</template>
<style lang="scss" scoped>
@use '../../../scss/components/hamburger/hamburger-panel';
</style>
