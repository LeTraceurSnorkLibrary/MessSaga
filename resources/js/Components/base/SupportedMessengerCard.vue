<script setup>
defineProps({
    name: {type: String, required: true},
    variant: {type: String},
    placeholder: {type: Boolean, default: false},
});
</script>
<template>
    <div :class="[
          `supported-messenger-card--${variant}`,
          { 'supported-messenger-card--placeholder': placeholder },
        ]"
         class="supported-messenger-card"
    >
        <div class="supported-messenger-card__icon">
            <slot name="icon">
                <span v-if="placeholder" class="supported-messenger-card__placeholder-icon">+</span>
            </slot>
        </div>
        <div class="supported-messenger-card__name">{{ name }}</div>
    </div>
</template>
<style lang="scss" scoped>
@use '../../../scss/typography' as typography;

.supported-messenger-card {
    --supported-messenger-card-name-color: var(--gray-700);
    --supported-messenger-card-border-color: var(--purple-100);
    --supported-messenger-card-icon-bg-color: var(--purple-300);
    --supported-messenger-card-icon-opacity: 1;

    display: flex;
    column-gap: 0.5rem;
    align-items: center;
    padding: 1rem;
    border: 1px solid var(--blue-100);
    border-color: var(--supported-messenger-card-border-color);
    border-radius: var(--radius-xl);
    background: var(--gray-0);
    box-shadow: var(--shadow-md);
    transition: transform var(--transition-normal);

    &:hover {
        transform: scale(1.05);
    }

    &--placeholder {
        --supported-messenger-card-name-color: var(--gray-500);
        --supported-messenger-card-icon-opacity: 0.6;
    }

    &--telegram {
        --supported-messenger-card-icon-bg-color: #00a7e7;
    }

    &--vk {
        --supported-messenger-card-icon-bg-color: #0074fa;
    }

    &--whatsapp {
        --supported-messenger-card-icon-bg-color: #00d46c;
    }
}

.supported-messenger-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-xl);
    background: var(--supported-messenger-card-icon-bg-color);
    opacity: var(--supported-messenger-card-icon-opacity);
}

.supported-messenger-card__icon :deep(svg) {
    width: 1.75rem;
    height: 1.75rem;
    color: var(--gray-0);
}

.supported-messenger-card__name {
    @include typography.text--150(1rem, typography.$font-weight--underbold);

    color: var(--supported-messenger-card-name-color);
}

.supported-messenger-card__placeholder-icon {
    font-size: 1.5rem;
    color: var(--gray-0);
    line-height: 1;
}
</style>
