<script setup>
import ConversationList from '@/Components/ConversationList.vue';
import ImportWizard from '@/Components/ImportWizard.vue';
import MessageThread from '@/Components/MessageThread.vue';
import MessengerTabs from '@/Components/MessengerTabs.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import {computed, onUnmounted, ref, watch} from 'vue';

const selectedMessenger = ref('telegram');
const conversations = ref([]);
const messages = ref([]);
const loadingConversations = ref(false);
const loadingMessages = ref(false);
const currentConversation = ref(null);
const pollingInterval = ref(null);
const pollingStartTime = ref(null);
const POLLING_INTERVAL_MS = 2000;
const POLLING_TIMEOUT_MS = 60000;

// Новые состояния для режима импорта
const importMode = ref('auto'); // 'auto', 'new', 'select'
const selectedConversationId = ref(null);

const previousConversationsCount = ref(0);
const previousMessagesCount = ref(0);

/**
 * Опции для селектора в зависимости от мессенджера
 * @type {ComputedRef<Array<{ value: 'auto' | 'new' | 'select', label: string }>>}
 */
const modeOptions = computed(() => {
    switch (selectedMessenger.value) {
        case 'telegram':
            return [
                {value: 'auto', label: 'Авто'},
                {value: 'new', label: 'Всегда новая'},
                {value: 'select', label: 'В указанную переписку...'},
            ];
        case 'whatsapp':
            return [
                {value: 'auto', label: 'Авто (тестовый режим)'},
                {value: 'new', label: 'Всегда новая'},
                {value: 'select', label: 'В указанную переписку...'},
            ];
        default:
            return [
                {value: 'new', label: 'Всегда новая'},
                {value: 'select', label: 'В указанную переписку...'},
            ];
    }
});

// Флаг для включения режима выбора в списке переписок
const isSelectionMode = computed(() => importMode.value === 'select');

const loadConversations = async () => {
    loadingConversations.value = true;
    try {
        const response = await window.axios.get('/api/conversations', {
            params: {messenger: selectedMessenger.value},
        });
        previousConversationsCount.value = conversations.value.length;
        conversations.value = response.data;

        if (!pollingInterval.value && !currentConversation.value && conversations.value.length > 0) {
            await handleConversationSelect(conversations.value[0]);
        }
    } finally {
        loadingConversations.value = false;
    }
};

const startPolling = () => {
    stopPolling();
    pollingStartTime.value = Date.now();
    previousConversationsCount.value = conversations.value.length;
    previousMessagesCount.value = messages.value.length;

    pollingInterval.value = setInterval(async () => {
        if (Date.now() - pollingStartTime.value > POLLING_TIMEOUT_MS) {
            stopPolling();
            return;
        }
        try {
            const convResponse = await window.axios.get('/api/conversations', {
                params: {messenger: selectedMessenger.value},
            });

            const newConversationsCount = convResponse.data.length;
            const conversationsChanged = newConversationsCount !== previousConversationsCount.value;
            conversations.value = convResponse.data;
            previousConversationsCount.value = newConversationsCount;

            let messagesChanged = false;
            if (currentConversation.value) {
                const msgResponse = await window.axios.get(
                    `/api/conversations/${currentConversation.value.id}/messages`
                );

                const newMessagesCount = msgResponse.data.length;
                messagesChanged = newMessagesCount !== previousMessagesCount.value;
                messages.value = msgResponse.data;
                previousMessagesCount.value = newMessagesCount;
            }

            if (conversationsChanged || messagesChanged) {
                stopPolling();
                if (!currentConversation.value && conversations.value.length > 0) {
                    await handleConversationSelect(conversations.value[0]);
                }
            }
        } catch (error) {
            console.error('Ошибка при polling:', error);
            stopPolling();
        }
    }, POLLING_INTERVAL_MS);
};

const stopPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
    pollingStartTime.value = null;
};

const handleImportStarted = () => {
    startPolling();
};

const loadMessages = async (conversationId) => {
    if (!conversationId) {
        return;
    }
    loadingMessages.value = true;
    try {
        const response = await window.axios.get(
            `/api/conversations/${conversationId}/messages`
        );
        previousMessagesCount.value = messages.value.length;
        messages.value = response.data;
    } finally {
        loadingMessages.value = false;
    }
};

const handleMessengerChange = async (value) => {
    selectedMessenger.value = value;
    currentConversation.value = null;
    messages.value = [];
    await loadConversations();
};

const handleConversationSelect = (conversation) => {
    if (isSelectionMode.value) {
        selectedConversationId.value = conversation.id;
    }

    currentConversation.value = conversation;
    loadMessages(conversation.id);
};

const handleConversationDelete = async () => {
    if (!currentConversation.value) {
        return;
    }
    if (!window.confirm('Удалить этот чат вместе со всеми сообщениями?')) {
        return;
    }

    await window.axios.delete(
        `/api/conversations/${currentConversation.value.id}`
    );
    currentConversation.value = null;
    messages.value = [];
    await loadConversations();
};

watch(currentConversation, (newConv, oldConv) => {
    if (newConv?.id !== oldConv?.id && newConv) {
        loadMessages(newConv.id);
    }
});

watch(importMode, (newMode) => {
    if (newMode !== 'select') {
        selectedConversationId.value = null;
    }
});

onUnmounted(() => {
    stopPolling();
});

loadConversations();
</script>
<template>
    <Head title="MessSaga"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="dashboard-page__heading">
                MessSaga — ваши переписки из разных мессенджеров в одном месте
            </h2>
        </template>

        <div class="dashboard-page">
            <div class="dashboard-page__container">

                <ImportWizard
                    :mode="importMode"
                    :selected-conversation-id="selectedConversationId"
                    :selected-messenger="selectedMessenger"
                    @imported="handleImportStarted"
                >
                    <template #import-mode>
                        <div class="import-mode-selector">
                            <span class="import-mode-selector__label">Режим загрузки:</span>
                            <select v-model="importMode" class="import-mode-selector__select">
                                <option v-for="opt in modeOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                    </template>
                </ImportWizard>

                <div class="dashboard-page__content">
                    <MessengerTabs
                        v-model:value="selectedMessenger"
                        class="dashboard-page__tabs"
                        @update:value="handleMessengerChange"
                    />
                    <div class="dashboard-page__grid">
                        <div class="dashboard-page__sidebar">
                            <ConversationList
                                :conversations="conversations"
                                :loading="loadingConversations"
                                :selected-id="selectedConversationId"
                                :selection-mode="isSelectionMode"
                                @select="handleConversationSelect"
                            />
                        </div>
                        <div class="dashboard-page__thread">
                            <MessageThread
                                :conversation-title="currentConversation?.title"
                                :loading="loadingMessages"
                                :messages="messages"
                                @delete="handleConversationDelete"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style scoped>
.dashboard-page {
    padding: 2rem 0;
}

.dashboard-page__container {
    max-width: 80rem;
    margin: 0 auto;
    padding: 0 1rem;
}

@media (min-width: 640px) {
    .dashboard-page__container {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
}

@media (min-width: 1024px) {
    .dashboard-page__container {
        padding-left: 2rem;
        padding-right: 2rem;
    }
}

.dashboard-page__heading {
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.3;
    color: var(--gray-800);
    margin: 0;
}

.dashboard-page__content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.dashboard-page__tabs {
    margin-bottom: 0.25rem;
}

.dashboard-page__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

@media (min-width: 768px) {
    .dashboard-page__grid {
        grid-template-columns: 1fr 2fr;
    }
}

.dashboard-page__sidebar {
    min-height: 0;
}

.dashboard-page__thread {
    min-height: 24rem;
}

.import-mode-selector {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.import-mode-selector__label {
    font-size: 0.875rem;
    color: var(--gray-600);
}

.import-mode-selector__select {
    padding: 0.375rem 2rem 0.375rem 0.75rem;
    font-size: 0.875rem;
    border: 1px solid var(--gray-300);
    background-color: var(--gray-0);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    appearance: none;

    &:focus {
        outline: 2px solid var(--orange-200);
        border-color: var(--orange-400);
    }
}
</style>
