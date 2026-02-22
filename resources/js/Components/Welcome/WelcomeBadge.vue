<script setup>
defineProps({
    text: {type: String, required: true},
});
</script>
<template>
    <div class="welcome-badge">
        <span class="welcome-badge__dot"/>
        <span class="welcome-badge__text">{{ text }}</span>
    </div>
</template>
<style lang="scss" scoped>
@use '../../../scss/typography' as typography;

.welcome-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 1rem;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid var(--orange-200);
    border-radius: var(--radius-full);
    box-shadow: var(--shadow-sm);
    backdrop-filter: blur(4px);
}

.welcome-badge__dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    background: var(--green-500);
    animation: pulse 2s ease-in-out infinite;
}

.welcome-badge__text {
    @include typography.text--150(0.875rem, 500);

    color: var(--gray-700);
    white-space: nowrap;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>
