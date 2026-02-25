<script lang="ts">
import {defineComponent} from 'vue';

export default defineComponent({
    name: 'Loader',
});
</script>
<template>
    <div class="loader">
        <div class="loader__dot" />
    </div>
</template>
<style lang="scss" scoped>
.loader {
    height: 100%;
    position: relative;
}

.loader__dot {
    background-color: var(--gray-600);
    width: 4px;
    height: 4px;
    border-radius: 50%;
    position: absolute;
    animation: flash 0.8s infinite linear alternate;
    animation-delay: 0.5s;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    &::after,
    &::before {
        content: '';
        position: absolute;
        top: 0;
        width: 4px;
        height: 4px;
        display: inline-block;
        border-radius: 4px;
        background-color: var(--gray-1000);
        animation: flash 0.8s infinite alternate;
    }

    &::after {
        left: 16px;
        animation-delay: 1s;
    }

    &::before {
        left: -16px;
        animation-delay: 0s;
    }
}

@keyframes flash {
    0% {
        background-color: var(--gray-100);
    }
    50%, 100% {
        background-color: var(--gray-1000);
    }
}
</style>
