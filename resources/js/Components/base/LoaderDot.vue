<template>
    <div class="loader">
        <div class="loader-dash" />
    </div>
</template>
<script lang="ts">
import { defineComponent } from 'vue';
export default defineComponent({
    name: 'LoaderDot',
});
</script>
<style lang="scss" scoped>
.loader {
    height: 100%;
    position: relative;
}

.loader-dash {
    background-color: var(--gray-600);
    width: 4px;
    height: 4px;
    border-radius: 50%;
    position: absolute;
    animation: dash-flashing 0.8s infinite linear alternate;
    animation-delay: 0.5s;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.loader-dash::before, .loader-dash::after {
    content: "";
    display: inline-block;
    position: absolute;
    top: 0;
}

.loader-dash::before {
    left: -16px;
    background-color: var(--gray-600);
    width: 4px;
    height: 4px;
    border-radius: 50%;
    animation: dash-flashing 0.8s infinite alternate;
    animation-delay: 0s;
}

.loader-dash::after {
    left: 16px;
    background-color: var(--gray-600);
    width: 4px;
    height: 4px;
    border-radius: 50%;
    animation: dash-flashing 0.8s infinite alternate;
    animation-delay: 1s;
}

@keyframes dash-flashing {
    0% {
        background-color: var(--gray-100);
    }
    50%, 100% {
        background-color: var(--gray-600);
    }
}
</style>
