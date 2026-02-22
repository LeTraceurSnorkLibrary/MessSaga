<script setup>
import {useDate} from '@/composables/useDate';

defineProps({
    messages: {type: Array, default: () => []},
    loading: {type: Boolean, default: false},
    conversationTitle: {type: String, default: ''},
});

const emit = defineEmits(['delete']);
const {formatDate} = useDate();
</script>

<template>
    <div class="message-thread">
        <div class="message-thread__head">
            <span class="message-thread__title">{{ conversationTitle || 'Выберите переписку' }}</span>
            <button
                v-if="conversationTitle"
                class="message-thread__delete"
                type="button"
                @click="emit('delete')"
            >
                Удалить чат
            </button>
        </div>
        <div v-if="loading" class="message-thread__loading">Загрузка сообщений...</div>
        <div v-else class="message-thread__body">
            <div
                v-for="message in messages"
                :key="message.id"
                class="message-thread__message"
            >
                <div class="message-thread__meta">
                    <span class="message-thread__sender">{{ message.sender_name || 'Неизвестный' }}</span>
                    <time :datetime="message.sent_at">{{ formatDate(message.sent_at) }}</time>
                </div>
                <div class="message-thread__text">{{ message.text }}</div>
            </div>
            <div v-if="!messages.length" class="message-thread__empty">Сообщений пока нет.</div>
        </div>
    </div>
</template>

<style scoped>
.message-thread {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    background: var(--gray-0);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.message-thread__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    border-bottom: 1px solid var(--gray-200);
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.message-thread__title {
    font-weight: 600;
}

.message-thread__delete {
    font-size: 0.75rem;
    color: var(--red-600);
    background: none;
    border: none;
    cursor: pointer;
    text-decoration: none;
    padding: 0;
}

.message-thread__delete:hover {
    color: var(--red-700);
    text-decoration: underline;
}

.message-thread__loading {
    padding: 1rem;
    font-size: 0.875rem;
    color: var(--gray-500);
}

.message-thread__body {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    max-height: 24rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.message-thread__message {
    display: flex;
    flex-direction: column;
}

.message-thread__meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: var(--gray-500);
}

.message-thread__sender {
    font-weight: 500;
    color: var(--gray-700);
}

.message-thread__text {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--gray-800);
    white-space: pre-wrap;
}

.message-thread__empty {
    font-size: 0.875rem;
    color: var(--gray-500);
}
</style>
