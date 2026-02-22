<script setup>
defineProps({
    conversations: {type: Array, default: () => []},
    loading: {type: Boolean, default: false},
});

const emit = defineEmits(['select']);
</script>

<template>
    <div class="conv-list">
        <div class="conv-list__head">Переписки</div>
        <div v-if="loading" class="conv-list__loading">Загрузка...</div>
        <ul v-else class="conv-list__items">
            <li
                v-for="conversation in conversations"
                :key="conversation.id"
                class="conv-list__item"
                @click="emit('select', conversation)"
            >
                <div class="conv-list__title">{{ conversation.title || 'Без названия' }}</div>
                <div class="conv-list__preview">{{ conversation.preview || '' }}</div>
            </li>
            <li v-if="!conversations.length" class="conv-list__empty">Пока нет переписок.</li>
        </ul>
    </div>
</template>

<style scoped>
.conv-list {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    overflow: hidden;
    background: var(--gray-0);
}

.conv-list__head {
    border-bottom: 1px solid var(--gray-200);
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-600);
}

.conv-list__loading {
    padding: 1rem;
    font-size: 0.875rem;
    color: var(--gray-500);
}

.conv-list__items {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 24rem;
    overflow-y: auto;
}

.conv-list__item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid var(--gray-100);
    transition: background 150ms ease;
}

.conv-list__item:last-child {
    border-bottom: none;
}

.conv-list__item:hover {
    background: var(--gray-50);
}

.conv-list__title {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-800);
}

.conv-list__preview {
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: var(--gray-500);
}

.conv-list__empty {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: var(--gray-500);
}
</style>
