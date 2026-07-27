<script lang="ts" setup>
import SpeedometerGauge from '@/Components/layout/SpeedometerGauge.vue';
import {computed, onBeforeUnmount, onMounted, ref} from 'vue';

const props = defineProps({
    quota: {
        type: Object,
    },
});

const storageUsed = computed(() => Number(props.quota.storage?.used ?? 0));
const storageLimit = computed(() => Math.max(0, Number(props.quota.storage?.limit ?? 0)));
const filesUsed = computed(() => Number(props.quota.files?.used ?? 0));
const filesLimit = computed(() => Math.max(0, Number(props.quota.files?.limit ?? 0)));
const nowTs = ref(Date.now());
let timerId = null;

const storagePercent = computed(() => {
    return Math.round((storageUsed.value / Math.max(1, storageLimit.value)) * 100);
});
const filesPercent = computed(() => {
    return Math.round((filesUsed.value / Math.max(1, filesLimit.value)) * 100);
});

const storageRemaining = computed(() => Math.max(0, storageLimit.value - storageUsed.value));
const filesRemaining = computed(() => Math.max(0, filesLimit.value - filesUsed.value));
const maxUsagePercent = computed(() => Math.max(storagePercent.value, filesPercent.value));

const canUploadMedia = computed(() => storageRemaining.value > 0 && filesRemaining.value > 0);
const graceUntilTs = computed(() => {
    const raw = props.quota?.grace?.until ?? null;
    if (!raw) {
        return null;
    }

    const parsed = Date.parse(String(raw));

    return Number.isNaN(parsed)
        ? null
        : parsed;
});
const graceSecondsLeft = computed(() => {
    if (graceUntilTs.value === null) {
        return 0;
    }

    return Math.max(0, Math.floor((graceUntilTs.value - nowTs.value) / 1000));
});
const hasActiveGrace = computed(() => !canUploadMedia.value && graceSecondsLeft.value > 0);
const graceUntilIso = computed(() => {
    const raw = props.quota?.grace?.until ?? null;

    return raw ? String(raw) : '';
});
const graceUntilLabel = computed(() => {
    if (graceUntilTs.value === null) {
        return '';
    }

    const date = new Date(graceUntilTs.value);

    return new Intl.DateTimeFormat('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
});
const graceUntilTitle = computed(() => (graceUntilLabel.value ? `до ${graceUntilLabel.value}` : ''));
const graceRemainingLabel = computed(() => {
    const seconds = graceSecondsLeft.value;
    if (seconds <= 0) {
        return '';
    }

    const days = Math.floor(seconds / 86400);
    if (days >= 1) {
        return `${days} дн.`;
    }

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    if (hours >= 1) {
        return `${hours} ч. ${minutes} мин.`;
    }

    return `${Math.max(1, minutes)} мин.`;
});
const gracePeriodHelpText =
    ' '
    + ''
    + '';
const descriptionText = computed(() => {
    if (!canUploadMedia.value) {
        return 'Загрузка медиа недоступна. Повысьте тариф, чтобы сохранить файлы.';
    }

    if (maxUsagePercent.value >= 90) {
        return `Загрузка медиа доступна, но место почти кончилось!`;
    }

    if (maxUsagePercent.value >= 70) {
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

onMounted(() => {
    timerId = window.setInterval(() => {
        nowTs.value = Date.now();
    }, 30000);
});

onBeforeUnmount(() => {
    if (timerId !== null) {
        window.clearInterval(timerId);
        timerId = null;
    }
});
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
            <p
                v-if="hasActiveGrace"
                class="tariff-quota-progress__grace"
            >
                Льготный период:
                <time
                    :datetime="graceUntilIso"
                    :title="graceUntilTitle"
                    class="tariff-quota-progress__grace-remaining"
                >ещё {{ graceRemainingLabel }}</time>
                <span class="tariff-quota-progress__grace-help"
                    title="Загруженные медиафайлы ещё хранятся, хотя лимит тарифа превышен. К сожалению, по окончании льготного периода часть Ваших файлов будет удалена, чтобы занятое место попадало в тариф."
                    role="img"
                    aria-label="Что такое льготный период"
                />
            </p>
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

.tariff-quota-progress__grace {
    @include typography.text--120(0.75rem);

    margin: 0;
    color: var(--emerald-600);
}

.tariff-quota-progress__grace-remaining {
  cursor: help;
    text-decoration: underline;
    text-decoration-style: dotted;
    text-underline-offset: 0.15em;
}

.tariff-quota-progress__grace-help {
  cursor: help;
    display: inline-block;
    width: 1lh;
    height: 1lh;
    margin-left: 0.25rem;
    vertical-align: middle;
    background-color: var(--gray-500);
    mask-image: url('@/../assets/help-circle-question.svg');
    mask-repeat: no-repeat;
    mask-position: center;
    mask-size: contain;
    -webkit-mask-image: url('@/../assets/help-circle-question.svg');
    -webkit-mask-repeat: no-repeat;
    -webkit-mask-position: center;
    -webkit-mask-size: contain;

    &:hover {
        background-color: var(--gray-1000);
    }
}
</style>
