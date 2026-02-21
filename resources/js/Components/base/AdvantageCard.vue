<script setup>
defineProps({
    title: {type: String, required: true},
    description: {type: String, required: true},
    variant: {type: String, default: 'orange'},
    inProgress: {type: Boolean, default: false},
    ribbonText: {type: String, default: 'В разработке'},
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
            <span class="advantage-card__ribbon-text">{{ ribbonText }}</span>
        </div>
        <div class="advantage-card__icon-wrap">
            <slot name="icon"/>
        </div>
        <h3 class="advantage-card__title">{{ title }}</h3>
        <p class="advantage-card__description">{{ description }}</p>
    </div>
</template>
<style lang="scss" scoped>
.advantage-card {
    --advantage-card-bg-image: linear-gradient(135deg, var(--orange-50), var(--amber-50));
    --advantage-card-border-color: var(--orange-100);
    --advantage-card-icon-bg-gradient: linear-gradient(135deg, var(--orange-400), var(--rose-400));
    --advantage-card-ribbon-bg: linear-gradient(135deg, var(--orange-500), var(--rose-500)); // ← Новое

    // Для состояния "в разработке" (более тусклые цвета)
    --advantage-card-bg-image-wip: linear-gradient(135deg, var(--gray-100), var(--gray-150));
    --advantage-card-border-color-wip: var(--gray-200);
    --advantage-card-icon-bg-gradient-wip: linear-gradient(135deg, var(--gray-300), var(--gray-400));

    --advantage-card-icon-wrap-transform: none;

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
        background-image: var(--advantage-card-bg-image-wip);
        border-color: var(--advantage-card-border-color-wip);
        opacity: 0.75;
        cursor: not-allowed;

        .advantage-card__icon-wrap {
            background: var(--advantage-card-icon-bg-gradient-wip);
            opacity: 0.6;
        }

        .advantage-card__title {
            color: var(--gray-500);
        }

        .advantage-card__description {
            color: var(--gray-400);
        }
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
    top: 1.5rem;
    right: -2.5rem;
    z-index: 10;
    width: 10rem;
    padding: 0.375rem 0;
    background: var(--advantage-card-ribbon-bg);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transform: rotate(45deg);
    text-align: center;
}

.advantage-card__ribbon-text {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.025em;
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
}

.advantage-card__icon-wrap :deep(svg) {
    width: 2rem;
    height: 2rem;
    color: var(--gray-0);
}

.advantage-card__title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-800);
    margin: 0 0 1rem;
    transition: color var(--transition-normal);
}

.advantage-card__description {
    color: var(--gray-600);
    line-height: 1.6;
    margin: 0;
    transition: color var(--transition-normal);
}
</style>
