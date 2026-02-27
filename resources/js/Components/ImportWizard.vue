<script setup>
import Loader from '@/Components/base/Loader.vue';
import UIButton from '@/Components/UIButton.vue';
import {useCapitalizeFirstLetter} from '@/composables/useCapitalizeFirstLetter.ts';
import {computed, ref} from 'vue';

const props = defineProps({
    selectedMessenger: {type: String, default: 'telegram'},
    mode: {type: String, default: 'auto'},
    selectedConversationId: {type: Number, default: null},
});

const emit = defineEmits(['imported']);

const file = ref(null);
const loading = ref(false);
const message = ref('');
const fileInputRef = ref(null);

const {capitalizeFirstLetter} = useCapitalizeFirstLetter();
const capitalizedMessenger = computed(() => {
    return capitalizeFirstLetter(props.selectedMessenger);
});

const submit = async () => {
    if (!file.value) {
        message.value = 'Выберите файл экспорта переписки.';
        return;
    }

    if (props.mode === 'select' && !props.selectedConversationId) {
        message.value = 'В режиме "Выбрать" необходимо выбрать переписку из списка.';
        return;
    }

    loading.value = true;
    message.value = '';

    try {
        const formData = new FormData();
        formData.append('messenger_type', props.selectedMessenger);
        formData.append('file', file.value);
        formData.append('import_mode', props.mode);
        if (props.mode === 'select' && props.selectedConversationId) {
            formData.append('target_conversation_id', props.selectedConversationId.toString());
        }

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
                <p class="import-wizard__label">Выберите вкладку соответствующего мессенджера из списка ниже.
                    Переписка будет загружена в него.</p>
            </div>
            <input
                :value="selectedMessenger"
                type="hidden"
            />
            <div class="import-wizard__row">
                <label class="import-wizard__label">Файл экспорта:</label>
                <input
                    ref="fileInputRef"
                    class="import-wizard__file"
                    type="file"
                    @change="onFileChange"
                />
            </div>
            <div class="import-wizard__row">
                <slot name="import-mode"/>
            </div>
            <div class="import-wizard__actions">
                <div class="import-wizard__button">
                    <UIButton
                        :disabled="loading"
                        size="s"
                        type="button"
                        w100
                        @click="submit"
                    >
                        <Loader v-if="loading"/>
                        <span v-else>Запустить импорт в {{ capitalizedMessenger.value }}</span>
                    </UIButton>
                </div>
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

.import-wizard__file {
    font-size: 0.875rem;
}

.import-wizard__actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.import-wizard__button {
    min-width: 225px;
}

.import-wizard__message {
    font-size: 0.75rem;
    color: var(--gray-600);
}
</style>
