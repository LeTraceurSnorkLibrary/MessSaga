<script setup>
const props = defineProps({
    messages: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    conversationTitle: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['delete']);
</script>

<template>
    <div class="border border-slate-200 rounded-lg bg-white flex flex-col h-full">
        <div class="border-b border-slate-200 px-4 py-2 text-sm text-slate-600 flex items-center justify-between gap-2">
            <span class="font-semibold">
                {{ conversationTitle || 'Выберите переписку' }}
            </span>
            <button
                v-if="conversationTitle"
                type="button"
                class="text-xs text-red-600 hover:text-red-700 hover:underline"
                @click="emit('delete')"
            >
                Удалить чат
            </button>
        </div>
        <div v-if="loading" class="p-4 text-sm text-slate-500">Загрузка сообщений...</div>
        <div
            v-else
            class="flex-1 p-4 space-y-3 overflow-y-auto max-h-[24rem]"
        >
            <div
                v-for="message in messages"
                :key="message.id"
                class="flex flex-col"
            >
                <div class="text-xs text-slate-500 flex justify-between">
                    <span class="font-medium text-slate-700">
                        {{ message.sender_name || 'Неизвестный' }}
                    </span>
                    <span>
                        {{ message.sent_at }}
                    </span>
                </div>
                <div class="mt-1 text-sm text-slate-900 whitespace-pre-wrap">
                    {{ message.text }}
                </div>
            </div>
            <div v-if="!messages.length" class="text-sm text-slate-500">
                Сообщений пока нет.
            </div>
        </div>
    </div>
</template>

