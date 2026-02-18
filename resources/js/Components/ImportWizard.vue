<script setup>
import { ref, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    selectedMessenger: {
        type: String,
        default: 'telegram',
    },
});

const emit = defineEmits(['imported']);

const file = ref(null);
const loading = ref(false);
const message = ref('');

const submit = async () => {
    if (!file.value) {
        message.value = 'Выберите файл экспорта переписки.';
        return;
    }

    loading.value = true;
    message.value = '';

    try {
        const formData = new FormData();
        formData.append('messenger_type', props.selectedMessenger);
        formData.append('file', file.value);

        await window.axios.post('/api/import/chats', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        message.value =
            'Импорт поставлен в очередь. Переписка будет автоматически определена по ID из файла.';

        file.value = null;
        if (fileInputRef.value) {
            fileInputRef.value.value = '';
        }

        emit('imported');
    } catch (error) {
        message.value =
            'Не удалось запустить импорт. Проверьте файл и попробуйте ещё раз.';
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const fileInputRef = ref(null);

const onFileChange = (event) => {
    const [selected] = event.target.files;
    file.value = selected ?? null;
};
</script>

<template>
    <div class="border border-dashed border-slate-300 rounded-lg p-4 bg-slate-50">
        <div class="flex flex-col gap-3">
            <div class="text-sm font-semibold text-slate-700">
                Импорт переписки
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <label class="text-slate-600">Мессенджер:</label>
                <select
                    :value="selectedMessenger"
                    disabled
                    class="rounded-md border-slate-300 text-sm shadow-sm bg-slate-100"
                >
                    <option value="telegram">Telegram (.json экспорт)</option>
                    <option value="whatsapp" disabled>WhatsApp (скоро)</option>
                    <option value="viber" disabled>Viber (скоро)</option>
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <label class="text-slate-600">Файл экспорта:</label>
                <input
                    ref="fileInputRef"
                    type="file"
                    class="text-sm"
                    @change="onFileChange"
                />
            </div>
            <div class="flex items-center gap-3">
                <PrimaryButton
                    type="button"
                    :disabled="loading"
                    @click="submit"
                >
                    {{ loading ? 'Импортируем...' : 'Запустить импорт' }}
                </PrimaryButton>
                <span v-if="message" class="text-xs text-slate-600">
                    {{ message }}
                </span>
            </div>
        </div>
    </div>
</template>

