<script setup>
defineProps({
    title: {type: String, required: true},
    description: {type: String, required: true},
    variant: {type: String, default: 'orange'},
});
</script>
<template>
    <div class="advantage-card" :class="`advantage-card--${variant}`">
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
    --icon-bg-gradient: linear-gradient(135deg, var(--orange-400), var(--rose-400));

    border-radius: var(--radius-2xl);
    padding: 2rem;
    border: 1px solid var(--advantage-card-border-color);
    background-image: var(--advantage-card-bg-image);
    transition: box-shadow var(--transition-slow), transform var(--transition-slow);

    &:hover {
        box-shadow: var(--shadow-xl);
        transform: scale(1.05);
    }

    &--rose {
        --advantage-card-bg-image: linear-gradient(135deg, var(--rose-50), var(--pink-50));
        --advantage-card-border-color: var(--rose-100);
        --icon-bg-gradient: linear-gradient(135deg, var(--rose-400), var(--pink-400));
    }

    &--amber {
        --advantage-card-bg-image: linear-gradient(135deg, var(--amber-50), var(--yellow-50));
        --advantage-card-border-color: var(--amber-100);
        --icon-bg-gradient: linear-gradient(135deg, var(--amber-400), var(--yellow-400));
    }

    &--blue {
        --advantage-card-bg-image: linear-gradient(135deg, var(--blue-50), var(--indigo-100));
        --advantage-card-border-color: var(--blue-100);
        --icon-bg-gradient: linear-gradient(135deg, var(--blue-400), var(--indigo-400));
    }

    &--purple {
        --advantage-card-bg-image: linear-gradient(135deg, var(--purple-50), var(--purple-100));
        --advantage-card-border-color: var(--purple-100);
        --icon-bg-gradient: linear-gradient(135deg, var(--purple-400), var(--violet-400));
    }

    &--green {
        --advantage-card-bg-image: linear-gradient(135deg, var(--green-50), var(--green-100));
        --advantage-card-border-color: var(--green-100);
        --icon-bg-gradient: linear-gradient(135deg, var(--green-400), var(--emerald-400));
    }
}

.advantage-card__icon-wrap {
    width: 4rem;
    height: 4rem;
    border-radius: var(--radius-2xl);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-lg);
    transition: transform var(--transition-normal);
    background: var(--icon-bg-gradient);
}

.advantage-card:hover .advantage-card__icon-wrap {
    transform: scale(1.1);
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
}

.advantage-card__description {
    color: var(--gray-600);
    line-height: 1.6;
    margin: 0;
}
</style>
