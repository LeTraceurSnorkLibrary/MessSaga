<script setup>
import {ref, watch} from 'vue';

const props = defineProps({
    value: {type: String, default: 'telegram'},
});

const emit = defineEmits(['update:value']);

const tabs = [
    {id: 'telegram', label: 'Telegram'},
    {id: 'whatsapp', label: 'WhatsApp'},
    {id: 'viber', label: 'Viber'},
];

const current = ref(props.value);

watch(
    () => props.value,
    (val) => {
        current.value = val;
    }
);

const select = (id) => {
    current.value = id;
    emit('update:value', id);
};
</script>

<template>
    <div class="messenger-tabs">
        <nav aria-label="Мессенджеры" class="messenger-tabs__nav">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                :class="{ 'messenger-tabs__btn--active': current === tab.id }"
                class="messenger-tabs__btn"
                type="button"
                @click="select(tab.id)"
            >
                {{ tab.label }}
            </button>
        </nav>
    </div>
</template>

<style scoped>
.messenger-tabs {
    border-bottom: 1px solid var(--gray-200);
    margin-bottom: 1rem;
}

.messenger-tabs__nav {
    display: flex;
    gap: 1rem;
    margin-bottom: -1px;
}

.messenger-tabs__btn {
    white-space: nowrap;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-500);
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: color 200ms ease, border-color 200ms ease;
}

.messenger-tabs__btn:hover {
    color: var(--gray-700);
    border-color: var(--gray-300);
}

.messenger-tabs__btn--active {
    color: var(--indigo-600);
    border-color: var(--indigo-500);
}
</style>
