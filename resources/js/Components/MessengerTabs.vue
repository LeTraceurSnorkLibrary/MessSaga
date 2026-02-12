<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    value: {
        type: String,
        default: 'telegram',
    },
});

const emit = defineEmits(['update:value']);

const tabs = [
    { id: 'telegram', label: 'Telegram' },
    { id: 'whatsapp', label: 'WhatsApp' },
    { id: 'viber', label: 'Viber' },
];

const current = ref(props.value);

watch(
    () => props.value,
    (val) => {
        current.value = val;
    },
);

const select = (id) => {
    current.value = id;
    emit('update:value', id);
};
</script>

<template>
    <div class="border-b border-slate-200 mb-4">
        <nav class="-mb-px flex space-x-4" aria-label="Tabs">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                type="button"
                @click="select(tab.id)"
                class="whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium transition-colors"
                :class="[
                    current === tab.id
                        ? 'border-indigo-500 text-indigo-600'
                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
                ]"
            >
                {{ tab.label }}
            </button>
        </nav>
    </div>
</template>

