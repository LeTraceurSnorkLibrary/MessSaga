<script setup>
import {computed} from 'vue';

const props = defineProps({
    quota: {
        type: Object,
        default: null,
    },
});

const storageUsed = computed(() => Number(props.quota?.storage?.used ?? 0));
const storageLimit = computed(() => Number(props.quota?.storage?.limit ?? 0));
const filesUsed = computed(() => Number(props.quota?.files?.used ?? 0));
const filesLimit = computed(() => Number(props.quota?.files?.limit ?? 0));

const storagePercent = computed(() => {
    if (storageLimit.value <= 0) return 0;

    return Math.min(100, Math.round((storageUsed.value / storageLimit.value) * 100));
});
const filesPercent = computed(() => {
    if (filesLimit.value <= 0) return 0;

    return Math.min(100, Math.round((filesUsed.value / filesLimit.value) * 100));
});

const progressPercent = computed(() => Math.max(storagePercent.value, filesPercent.value));

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
    <div v-if="quota" class="tariff-quota-progress">
        <div class="tariff-quota-progress__head">
            <span class="tariff-quota-progress__tariff">{{ quota.tariff?.label ?? 'Tariff' }}</span>
            <span class="tariff-quota-progress__percent">{{ progressPercent }}%</span>
        </div>
        <div class="tariff-quota-progress__bar">
            <div class="tariff-quota-progress__bar-fill" :style="{ width: `${progressPercent}%` }"></div>
        </div>
        <div class="tariff-quota-progress__meta">
            <span>{{ formatBytes(storageUsed) }} / {{ formatBytes(storageLimit) }}</span>
            <span>{{ filesUsed }} / {{ filesLimit }} файлов</span>
        </div>
    </div>
</template>

<style scoped>
.tariff-quota-progress {
    min-width: 14rem;
    max-width: 16rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.tariff-quota-progress__head,
.tariff-quota-progress__meta {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: var(--gray-500);
}

.tariff-quota-progress__tariff {
    font-weight: 600;
    color: var(--gray-700);
}

.tariff-quota-progress__percent {
    font-weight: 600;
    color: var(--gray-600);
}

.tariff-quota-progress__bar {
    position: relative;
    height: 0.375rem;
    border-radius: 9999px;
    background: var(--gray-200);
    overflow: hidden;
}

.tariff-quota-progress__bar-fill {
    position: absolute;
    inset: 0 auto 0 0;
    width: 0;
    background: linear-gradient(90deg, var(--orange-500), var(--orange-600));
    transition: width 220ms ease;
}
</style>
