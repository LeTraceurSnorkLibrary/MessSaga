<script setup>
defineProps({
    title: {type: String, required: true},
    description: {type: String, required: true},
    variant: {type: String, default: 'orange'},
    inProgress: {type: Boolean, default: false},
});
</script>
<template>
    <div :class="[
            `advantage-card--${variant}`,
            { 'advantage-card--in-progress': inProgress }
        ]"
         class="advantage-card"
    >
        <div v-if="inProgress" class="advantage-card__ribbon">
            <span class="advantage-card__ribbon-text">В разработке</span>
        </div>
        <div class="advantage-card__icon-wrap">
            <slot name="icon"/>
        </div>
        <h3 class="advantage-card__title">{{ title }}</h3>
        <p class="advantage-card__description">{{ description }}</p>
    </div>
</template>
<style lang="scss" scoped>
@use '../../../scss/typography' as typography;

.advantage-card {
    --advantage-card-title-color: var(--gray-800);
    --advantage-card-bg-image: linear-gradient(135deg, var(--orange-50), var(--amber-50));
    --advantage-card-border-color: var(--orange-100);
    --advantage-card-icon-bg-gradient: linear-gradient(135deg, var(--orange-400), var(--rose-400));
    --advantage-card-ribbon-bg: linear-gradient(135deg, var(--orange-500), var(--rose-500));
    --advantage-card-icon-opacity: 1;
    --advantage-card-icon-wrap-transform: none;
    --advantage-card-description-color: var(--gray-600);

    // Для состояния "в разработке" (более тусклые цвета)
    --advantage-card-bg-image-wip: linear-gradient(135deg, var(--gray-100), var(--gray-150));
    --advantage-card-border-color-wip: var(--gray-200);
    --advantage-card-icon-bg-gradient-wip: linear-gradient(135deg, var(--gray-300), var(--gray-400));

    position: relative;
    overflow: hidden;
    border-radius: var(--radius-2xl);
    padding: 2rem;
    border: 1px solid var(--advantage-card-border-color);
    background-image: var(--advantage-card-bg-image);
    transition: box-shadow var(--transition-slow), transform var(--transition-slow);

    &:hover:not(&--in-progress) {
        --advantage-card-icon-wrap-transform: scale(1.1);

        box-shadow: var(--shadow-xl);
        transform: scale(1.05);
    }

    &--in-progress {
        --advantage-card-title-color: var(--gray-500);
        --advantage-card-bg-image: var(--advantage-card-bg-image-wip);
        --advantage-card-border-color: var(--advantage-card-border-color-wip);
        --advantage-card-icon-bg-gradient: var(--advantage-card-icon-bg-gradient-wip);
        --advantage-card-icon-opacity: 0.6;
        --advantage-card-description-color: var(--gray-400);

        cursor: progress;
        opacity: 0.75;
    }

    &--rose {
        --advantage-card-bg-image: linear-gradient(135deg, var(--rose-50), var(--pink-50));
        --advantage-card-border-color: var(--rose-100);
        --advantage-card-icon-bg-gradient: linear-gradient(135deg, var(--rose-400), var(--pink-400));
        --advantage-card-ribbon-bg: linear-gradient(135deg, var(--rose-500), var(--pink-500));

        --advantage-card-bg-image-wip: linear-gradient(135deg, var(--gray-100), var(--gray-150));
        --advantage-card-border-color-wip: var(--gray-200);
        --advantage-card-icon-bg-gradient-wip: linear-gradient(135deg, var(--gray-300), var(--gray-400));
    }

    &--amber {
        --advantage-card-bg-image: linear-gradient(135deg, var(--amber-50), var(--yellow-50));
        --advantage-card-border-color: var(--amber-100);
        --advantage-card-icon-bg-gradient: linear-gradient(135deg, var(--amber-400), var(--yellow-400));
        --advantage-card-ribbon-bg: linear-gradient(135deg, var(--amber-500), var(--yellow-500));

        --advantage-card-bg-image-wip: linear-gradient(135deg, var(--gray-100), var(--gray-150));
        --advantage-card-border-color-wip: var(--gray-200);
        --advantage-card-icon-bg-gradient-wip: linear-gradient(135deg, var(--gray-300), var(--gray-400));
    }

    &--blue {
        --advantage-card-bg-image: linear-gradient(135deg, var(--blue-50), var(--indigo-100));
        --advantage-card-border-color: var(--blue-100);
        --advantage-card-icon-bg-gradient: linear-gradient(135deg, var(--blue-400), var(--indigo-400));
        --advantage-card-ribbon-bg: linear-gradient(135deg, var(--blue-500), var(--indigo-500));

        --advantage-card-bg-image-wip: linear-gradient(135deg, var(--gray-100), var(--gray-150));
        --advantage-card-border-color-wip: var(--gray-200);
        --advantage-card-icon-bg-gradient-wip: linear-gradient(135deg, var(--gray-300), var(--gray-400));
    }

    &--purple {
        --advantage-card-bg-image: linear-gradient(135deg, var(--purple-50), var(--purple-100));
        --advantage-card-border-color: var(--purple-100);
        --advantage-card-icon-bg-gradient: linear-gradient(135deg, var(--purple-400), var(--violet-400));
        --advantage-card-ribbon-bg: linear-gradient(135deg, var(--purple-500), var(--violet-500));

        --advantage-card-bg-image-wip: linear-gradient(135deg, var(--gray-100), var(--gray-150));
        --advantage-card-border-color-wip: var(--gray-200);
        --advantage-card-icon-bg-gradient-wip: linear-gradient(135deg, var(--gray-300), var(--gray-400));
    }

    &--green {
        --advantage-card-bg-image: linear-gradient(135deg, var(--green-50), var(--green-100));
        --advantage-card-border-color: var(--green-100);
        --advantage-card-icon-bg-gradient: linear-gradient(135deg, var(--green-400), var(--emerald-400));
        --advantage-card-ribbon-bg: linear-gradient(135deg, var(--green-500), var(--emerald-500));

        --advantage-card-bg-image-wip: linear-gradient(135deg, var(--gray-100), var(--gray-150));
        --advantage-card-border-color-wip: var(--gray-200);
        --advantage-card-icon-bg-gradient-wip: linear-gradient(135deg, var(--gray-300), var(--gray-400));
    }
}

.advantage-card__ribbon {
    position: absolute;
    top: 30px;
    right: -50px;
    width: 200px;
    padding: 0.375rem 0;
    text-align: center;
    background: var(--advantage-card-ribbon-bg);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transform: rotate(45deg);
    z-index: 10;
}

.advantage-card__ribbon-text {
    @include typography.text--150(0.75rem, typography.$font-weight--bold);

    display: block;
    text-transform: uppercase;
    letter-spacing: 0.025rem;
    color: var(--gray-0);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.advantage-card__icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 4rem;
    height: 4rem;
    margin-bottom: 1.5rem;
    border-radius: var(--radius-2xl);
    background: var(--advantage-card-icon-bg-gradient);
    box-shadow: var(--shadow-lg);
    transition: transform var(--transition-normal), opacity var(--transition-normal);
    transform: var(--advantage-card-icon-wrap-transform);
    opacity: var(--advantage-card-icon-opacity);
}

.advantage-card__icon-wrap :deep(svg) {
    width: 2rem;
    height: 2rem;
    color: var(--gray-0);
}

.advantage-card__title {
    @include typography.text--150(1.5rem, typography.$font-weight--bold);

    color: var(--advantage-card-title-color);
    margin: 0 0 1rem;
    transition: color var(--transition-normal);
}

.advantage-card__description {
    @include typography.text--150(1rem);

    color: var(--advantage-card-description-color);
    margin: 0;
    transition: color var(--transition-normal);
}
</style>
