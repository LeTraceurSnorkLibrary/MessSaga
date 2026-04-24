<script lang="ts" setup>
import {computed} from 'vue';

const props = withDefaults(defineProps<{
    text: string;
    percent: number;
    color?: string;
}>(), {
    color: 'var(--orange-500)',
});

const gaugeRadius = 50;
const gaugeCircumference = Math.PI * gaugeRadius;

const normalizedPercent = computed(() => Math.max(0, Math.min(100, Math.round(props.percent))));
const gaugeOffset = computed(() => gaugeCircumference * (1 - normalizedPercent.value / 100));
</script>
<template>
    <div :style="{ '--speedometer-gauge-color': color }" class="speedometer-gauge">
        <svg class="speedometer-gauge__svg" viewBox="0 0 120 70">
            <path
                class="speedometer-gauge__track"
                d="M 10 60 A 50 50 0 0 1 110 60"
            />
            <path
                :stroke-dasharray="gaugeCircumference"
                :stroke-dashoffset="gaugeOffset"
                class="speedometer-gauge__value"
                d="M 10 60 A 50 50 0 0 1 110 60"
            />
        </svg>
        <span class="speedometer-gauge__percent">{{ normalizedPercent }}%</span>
        <span class="speedometer-gauge__text">{{ text }}</span>
    </div>
</template>
<style lang="scss" scoped>
@use '../../../scss/typography' as typography;
@use '../../../scss/typography/variables' as typo_variables;

.speedometer-gauge {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    aspect-ratio: 12 / 8;
}

.speedometer-gauge__svg {
    height: auto;
    width: 100%;
}

.speedometer-gauge__track,
.speedometer-gauge__value {
    fill: none;
    stroke-width: 5;
    stroke-linecap: round;
}

.speedometer-gauge__track {
    stroke: var(--gray-200);
}

.speedometer-gauge__value {
    stroke: var(--speedometer-gauge-color);
    transition: stroke-dashoffset 220ms ease;
    transform-origin: center;
}

.speedometer-gauge__percent {
    @include typography.text--120(0.875rem, typo_variables.$font-weight--underbold);

    position: absolute;
    left: 50%;
    top: 52%;
    transform: translate(-50%, -50%);
    color: var(--gray-700);
}

.speedometer-gauge__text {
    @include typography.text--120(0.75rem);

    margin-top: -0.25rem;
    color: var(--gray-600);
}
</style>
