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
</script>

<template>
    <div class="border border-slate-200 rounded-lg bg-white flex flex-col h-full">
        <div class="border-b border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">
            {{ conversationTitle || 'Выберите переписку' }}
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

