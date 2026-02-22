<script setup>
defineProps({
    name: {type: String, required: true},
    subtitle: {type: String, default: null},
    variant: {type: String, default: 'work-in-progress'},
    placeholder: {type: Boolean, default: false},
});
</script>
<template>
    <div
        class="messenger-card"
        :class="[
          `messenger-card--${variant}`,
          { 'messenger-card--placeholder': placeholder },
        ]"
    >
        <div class="messenger-card__icon">
            <slot name="icon">
                <span v-if="placeholder" class="messenger-card__placeholder-icon">+</span>
            </slot>
        </div>
        <div class="messenger-card__name">{{ name }}</div>
        <div v-if="subtitle && !placeholder" class="messenger-card__subtitle">{{ subtitle }}</div>
    </div>
</template>
<style lang="scss" scoped>
.messenger-card {
    --messenger-card-name-color: var(--gray-700);
    --messenger-card-border-color: var(--purple-100);
    --messenger-card-icon-bg-color: var(--purple-300);
    --messenger-card-icon-opacity: 1;

    padding: 1rem;
    border: 1px solid var(--blue-100);
    border-color: var(--messenger-card-border-color);
    border-radius: var(--radius-xl);
    background: var(--gray-0);
    box-shadow: var(--shadow-md);
    transition: transform var(--transition-normal);

    &:hover {
        transform: scale(1.05);
    }

    &--work-in-progress {
        --messenger-card-name-color: var(--gray-500);
        --messenger-card-icon-opacity: 0.6;
    }

    &--telegram {
        --messenger-card-icon-bg-color: #00a7e7;
    }

    &--vk {
        --messenger-card-icon-bg-color: #0074fa;
    }

    &--whatsapp {
        --messenger-card-icon-bg-color: #00d46c;
    }
}

.messenger-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    margin-bottom: 0.75rem;
    border-radius: var(--radius-xl);
    background: var(--messenger-card-icon-bg-color);
    opacity: var(--messenger-card-icon-opacity);
}

.messenger-card__icon :deep(svg) {
    width: 1.75rem;
    height: 1.75rem;
    color: var(--gray-0);
}

.messenger-card__name {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--messenger-card-name-color);
}

.messenger-card__subtitle {
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-top: 0.25rem;
}

.messenger-card__placeholder-icon {
    font-size: 1.5rem;
    color: var(--gray-0);
    line-height: 1;
}
</style>
