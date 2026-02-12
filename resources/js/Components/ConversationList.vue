<script setup>
const props = defineProps({
    conversations: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['select']);
</script>

<template>
    <div class="border border-slate-200 rounded-lg overflow-hidden bg-white">
        <div class="border-b border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">
            Переписки
        </div>
        <div v-if="loading" class="p-4 text-sm text-slate-500">Загрузка...</div>
        <ul v-else class="divide-y divide-slate-100 max-h-[24rem] overflow-y-auto">
            <li
                v-for="conversation in conversations"
                :key="conversation.id"
                class="px-4 py-3 cursor-pointer hover:bg-slate-50 transition-colors"
                @click="emit('select', conversation)"
            >
                <div class="text-sm font-medium text-slate-900">
                    {{ conversation.title || 'Без названия' }}
                </div>
                <div class="mt-1 text-xs text-slate-500">
                    {{ conversation.preview || '' }}
                </div>
            </li>
            <li v-if="!conversations.length" class="px-4 py-3 text-sm text-slate-500">
                Пока нет переписок.
            </li>
        </ul>
    </div>
</template>

