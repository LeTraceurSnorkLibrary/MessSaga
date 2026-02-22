<script setup>
import {ref} from 'vue';
import UIButton from "@/Components/UIButton.vue";

const props = defineProps({
    selectedMessenger: {type: String, default: 'telegram'},
});

const emit = defineEmits(['imported']);

const file = ref(null);
const loading = ref(false);
const message = ref('');
const fileInputRef = ref(null);

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
            headers: {'Content-Type': 'multipart/form-data'},
        });

        message.value =
            'Импорт поставлен в очередь. Переписка будет автоматически определена по ID из файла.';

        file.value = null;
        if (fileInputRef.value) fileInputRef.value.value = '';

        emit('imported');
    } catch (error) {
        message.value =
            'Не удалось запустить импорт. Проверьте файл и попробуйте ещё раз.';
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const onFileChange = (event) => {
    const [selected] = event.target.files;
    file.value = selected ?? null;
};
</script>

<template>
    <div class="import-wizard">
        <div class="import-wizard__inner">
            <div class="import-wizard__title">Импорт переписки</div>
            <div class="import-wizard__row">
                <label class="import-wizard__label">Мессенджер:</label>
                <select
                    :value="selectedMessenger"
                    class="import-wizard__select"
                    disabled
                >
                    <option value="telegram">Telegram (.json экспорт)</option>
                    <option disabled value="whatsapp">WhatsApp (скоро)</option>
                    <option disabled value="viber">Viber (скоро)</option>
                </select>
            </div>
            <div class="import-wizard__row">
                <label class="import-wizard__label">Файл экспорта:</label>
                <input
                    ref="fileInputRef"
                    class="import-wizard__file"
                    type="file"
                    @change="onFileChange"
                />
            </div>
            <div class="import-wizard__actions">
                <UIButton
                    :disabled="loading"
                    type="button"
                    @click="submit"
                >
                    {{ loading ? 'Импортируем...' : 'Запустить импорт' }}
                </UIButton>
                <span v-if="message" class="import-wizard__message">{{ message }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.import-wizard {
    border: 1px dashed var(--gray-300);
    border-radius: var(--radius-lg);
    padding: 1rem;
    background: var(--gray-50);
}

.import-wizard__inner {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.import-wizard__title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-700);
}

.import-wizard__row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
}

.import-wizard__label {
    color: var(--gray-600);
}

.import-wizard__select {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-800);
    background: var(--gray-100);
    border: 1px solid var(--gray-300);
    border-radius: 0.375rem;
    box-shadow: var(--shadow-sm);
}

.import-wizard__file {
    font-size: 0.875rem;
}

.import-wizard__actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.import-wizard__message {
    font-size: 0.75rem;
    color: var(--gray-600);
}
</style>
