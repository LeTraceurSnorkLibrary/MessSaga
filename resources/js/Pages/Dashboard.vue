<script setup>
import { ref } from 'vue';
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

const loadConversations = async () => {
    loadingConversations.value = true;
    try {
        const response = await window.axios.get('/api/conversations', {
            params: { messenger: selectedMessenger.value },
        });
        conversations.value = response.data;

        // если ещё нет выбранной переписки, выберем первую
        if (!currentConversation.value && conversations.value.length > 0) {
            handleConversationSelect(conversations.value[0]);
        }
    } finally {
        loadingConversations.value = false;
    }
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
                <ImportWizard />

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
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
