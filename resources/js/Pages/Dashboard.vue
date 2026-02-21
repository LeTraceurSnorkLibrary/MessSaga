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
const POLLING_INTERVAL_MS = 2000;
const POLLING_TIMEOUT_MS = 60000;

const loadConversations = async () => {
  loadingConversations.value = true;
  try {
    const response = await window.axios.get('/api/conversations', {
      params: { messenger: selectedMessenger.value },
    });
    const previousCount = conversations.value.length;
    conversations.value = response.data;

    if (!currentConversation.value && conversations.value.length > 0) {
      handleConversationSelect(conversations.value[0]);
    }

    if (pollingInterval.value && conversations.value.length !== previousCount) {
      stopPolling();
    }
  } finally {
    loadingConversations.value = false;
  }
};

const startPolling = () => {
  stopPolling();
  pollingStartTime.value = Date.now();

  pollingInterval.value = setInterval(async () => {
    if (Date.now() - pollingStartTime.value > POLLING_TIMEOUT_MS) {
      stopPolling();
      return;
    }
    try {
      const response = await window.axios.get('/api/conversations', {
        params: { messenger: selectedMessenger.value },
      });
      const previousCount = conversations.value.length;
      conversations.value = response.data;

      if (conversations.value.length > previousCount) {
        stopPolling();
        if (!currentConversation.value && conversations.value.length > 0) {
          handleConversationSelect(conversations.value[0]);
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
  if (!conversationId) return;
  loadingMessages.value = true;
  try {
    const response = await window.axios.get(
      `/api/conversations/${conversationId}/messages`
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
  if (!window.confirm('Удалить этот чат вместе со всеми сообщениями?')) return;

  await window.axios.delete(
    `/api/conversations/${currentConversation.value.id}`
  );
  currentConversation.value = null;
  messages.value = [];
  await loadConversations();
};

onUnmounted(() => {
  stopPolling();
});

loadConversations();
</script>

<template>
  <Head title="MessSaga" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="dashboard-page__heading">
        MessSaga — ваши переписки из разных мессенджеров в одном месте
      </h2>
    </template>

    <div class="dashboard-page">
      <div class="dashboard-page__container">
        <ImportWizard
          :selected-messenger="selectedMessenger"
          @imported="handleImportStarted"
        />

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
                @select="handleConversationSelect"
              />
            </div>
            <div class="dashboard-page__thread">
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
  color: var(--welcome-gray-800);
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
</style>
