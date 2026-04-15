<script lang="ts" setup>
import SpeedometerGauge from '@/Components/layout/SpeedometerGauge.vue';
import {computed} from 'vue';

const props = defineProps({
    quota: {
        type: Object,
    },
});

const storageUsed = computed(() => Number(props.quota.storage?.used ?? 0));
const storageLimit = computed(() => Number(props.quota.storage?.limit ?? 0));
const filesUsed = computed(() => Number(props.quota.files?.used ?? 0));
const filesLimit = computed(() => Number(props.quota.files?.limit ?? 0));

const storagePercent = computed(() => {
    if (storageLimit.value <= 0) return 0;

    return Math.min(100, Math.round((storageUsed.value / storageLimit.value) * 100));
});
const filesPercent = computed(() => {
    if (filesLimit.value <= 0) return 0;

    return Math.min(100, Math.round((filesUsed.value / filesLimit.value) * 100));
});

const storageRemaining = computed(() => Math.max(0, storageLimit.value - storageUsed.value));
const filesRemaining = computed(() => Math.max(0, filesLimit.value - filesUsed.value));
const maxUsagePercent = computed(() => Math.max(storagePercent.value, filesPercent.value));

const canUploadMedia = computed(() => storageRemaining.value > 0 && filesRemaining.value > 0);
const descriptionText = computed(() => {
    if (!canUploadMedia.value) {
        return 'Загрузка медиа недоступна';
    }

    if (maxUsagePercent.value >= 90) {
        return `Загрузка медиа доступна, но место почти кончилось!`;
    }

    if (maxUsagePercent.value >= 0) {
        return `Загрузка медиа доступна, но место кончается`;
    }

    return `Загрузка медиа доступна`;
});

const descriptionClass = computed(() => {
    if (!canUploadMedia.value) {
        return 'tariff-quota-progress__description--unavailable';
    }

    if (maxUsagePercent.value >= 90) {
        return 'tariff-quota-progress__description--critical';
    }

    if (maxUsagePercent.value >= 70) {
        return 'tariff-quota-progress__description--risk';
    }

    return 'tariff-quota-progress__description--available';
});

const formatBytes = (value) => {
    const bytes = Number(value ?? 0);
    if (bytes <= 0) return '0 B';

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const amount = bytes / (1024 ** exponent);

    return `${amount.toFixed(amount >= 10 || exponent === 0 ? 0 : 1)} ${units[exponent]}`;
};
</script>
<template>
    <div class="tariff-quota-progress">
        <div class="tariff-quota-progress__header">
            <h4 class="tariff-quota-progress__title">{{ quota.tariff?.label ?? 'Tariff' }}</h4>
            <span
                :class="[
                    'tariff-quota-progress__description',
                    descriptionClass,
                ]"
            >
                {{ descriptionText }}
            </span>
        </div>

        <div class="tariff-quota-progress__metrics">
            <div class="tariff-quota-progress__metric">
                <SpeedometerGauge
                    :percent="storagePercent"
                    color="var(--orange-500)"
                    text="Память"
                />
                <div class="tariff-quota-progress__metric-meta">
                    {{ formatBytes(storageUsed) }} / {{ formatBytes(storageLimit) }}
                </div>
            </div>

            <div class="tariff-quota-progress__metric">
                <SpeedometerGauge
                    :percent="filesPercent"
                    color="var(--blue-500)"
                    text="Файлов"
                />
                <div class="tariff-quota-progress__metric-meta">
                    {{ filesUsed }} / {{ filesLimit }}
                </div>
            </div>
        </div>
    </div>
</template>
<style lang="scss" scoped>
@use '../../../scss/typography' as typography;

.tariff-quota-progress {
    min-width: 14rem;
    max-width: 16rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.tariff-quota-progress__header {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    color: var(--gray-500);
}

.tariff-quota-progress__metrics {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.5rem;
}

.tariff-quota-progress__metric {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    align-items: center;
}

.tariff-quota-progress__metric-meta {
    @include typography.text--120(0.75rem);
}

.tariff-quota-progress__metric-meta {
    color: var(--gray-500);
}

.tariff-quota-progress__title {
    @include typography.text--120(0.75rem, 600);

    color: var(--gray-700);
}

.tariff-quota-progress__description {
    @include typography.text--120(0.75rem);

    &--available {
        color: var(--success-600);
    }

    &--risk {
        color: var(--warning-600);
    }

    &--critical {
        color: var(--orange-700);
    }

    &--unavailable {
        color: var(--red-600);
    }
}
</style>
