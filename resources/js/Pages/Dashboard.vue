<script setup>
import { ref, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import MessengerTabs from '@/Components/MessengerTabs.vue';
import ConversationList from '@/Components/ConversationList.vue';
import MessageThread from '@/Components/MessageThread.vue';
import ImportWizard from '@/Components/ImportWizard.vue';

const selectedMessenger = ref('telegram');
const conversations = ref([]);
const messages = ref([]);
const loadingConversations = ref(false);
const loadingMessages = ref(false);
const currentConversation = ref(null);
const pollingInterval = ref(null);
const pollingStartTime = ref(null);
const POLLING_INTERVAL_MS = 2000; // Проверяем каждые 2 секунды
const POLLING_TIMEOUT_MS = 60000; // Максимум 60 секунд

const loadConversations = async () => {
    loadingConversations.value = true;
    try {
        const response = await window.axios.get('/api/conversations', {
            params: { messenger: selectedMessenger.value },
        });
        const previousCount = conversations.value.length;
        conversations.value = response.data;

        // если ещё нет выбранной переписки, выберем первую
        if (!currentConversation.value && conversations.value.length > 0) {
            handleConversationSelect(conversations.value[0]);
        }

        // Если количество переписок изменилось и мы в режиме polling - остановим его
        if (pollingInterval.value && conversations.value.length !== previousCount) {
            stopPolling();
        }
    } finally {
        loadingConversations.value = false;
    }
};

const startPolling = () => {
    // Останавливаем предыдущий polling, если он был
    stopPolling();

    pollingStartTime.value = Date.now();

    pollingInterval.value = setInterval(async () => {
        // Проверяем таймаут
        if (Date.now() - pollingStartTime.value > POLLING_TIMEOUT_MS) {
            stopPolling();
            return;
        }

        // Обновляем список переписок без показа индикатора загрузки
        try {
            const response = await window.axios.get('/api/conversations', {
                params: { messenger: selectedMessenger.value },
            });
            const previousCount = conversations.value.length;
            conversations.value = response.data;

            // Если появилась новая переписка - останавливаем polling
            if (conversations.value.length > previousCount) {
                stopPolling();
                // Если нет выбранной переписки, выберем первую новую
                if (!currentConversation.value && conversations.value.length > 0) {
                    handleConversationSelect(conversations.value[0]);
                }
            }
        } catch (error) {
            // При ошибке останавливаем polling
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
    // Запускаем polling после начала импорта
    startPolling();
};

const loadMessages = async (conversationId) => {
    if (!conversationId) return;
    loadingMessages.value = true;
    try {
        const response = await window.axios.get(
            `/api/conversations/${conversationId}/messages`,
        );
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

const handleConversationSelect = async (conversation) => {
    currentConversation.value = conversation;
    await loadMessages(conversation.id);
};

const handleConversationDelete = async () => {
    if (!currentConversation.value) return;

    if (!window.confirm('Удалить этот чат вместе со всеми сообщениями?')) {
        return;
    }

    await window.axios.delete(
        `/api/conversations/${currentConversation.value.id}`,
    );

    // Обновляем список переписок и очищаем текущую
    currentConversation.value = null;
    messages.value = [];
    await loadConversations();
};

// Очищаем polling при размонтировании компонента
onUnmounted(() => {
    stopPolling();
});

// начальная загрузка
loadConversations();
</script>

<template>
    <Head title="MessSaga" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                MessSaga — ваши переписки из разных мессенджеров в одном месте
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <ImportWizard
                    :selected-messenger="selectedMessenger"
                    @imported="handleImportStarted"
                />

                <div class="bg-transparent">
                    <MessengerTabs
                        v-model:value="selectedMessenger"
                        class="mb-4"
                        @update:value="handleMessengerChange"
                    />
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-1">
                            <ConversationList
                                :conversations="conversations"
                                :loading="loadingConversations"
                                @select="handleConversationSelect"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <MessageThread
                                :conversation-title="currentConversation?.title"
                                :messages="messages"
                                :loading="loadingMessages"
                                @delete="handleConversationDelete"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
