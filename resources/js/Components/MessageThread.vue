<script setup>
import {useDate} from '@/composables/useDate';
import {ref} from 'vue';

const props = defineProps({
    messages: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    conversationTitle: { type: String, default: '' },
    conversationId: { type: Number, default: null },
});

const emit = defineEmits(['delete', 'media-uploaded']);
const { formatDate } = useDate();
const mediaUploadInput = ref(null);

function showAsImage(message) {
    const t = (message.message_type || '').toLowerCase();
    return ['photo', 'gif', 'image'].includes(t);
}

function showAsAudio(message) {
    const t = (message.message_type || '').toLowerCase();
    return ['voice_message', 'audio', 'voice'].includes(t);
}

function showAsVideo(message) {
    const t = (message.message_type || '').toLowerCase();
    return ['video', 'video_message'].includes(t);
}

function triggerMediaUpload() {
    if (mediaUploadInput.value) mediaUploadInput.value.click();
}

async function onMediaFileSelected(event) {
    const file = event.target.files?.[0];
    if (!file || !props.conversationId) return;
    try {
        const form = new FormData();
        form.append('file', file);
        await window.axios.post(`/api/conversations/${props.conversationId}/media`, form, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        emit('media-uploaded');
    } finally {
        event.target.value = '';
    }
}
</script>

<template>
    <div class="message-thread">
        <div class="message-thread__head">
            <span class="message-thread__title">{{ conversationTitle || 'Выберите переписку' }}</span>
            <div class="message-thread__head-actions">
                <template v-if="conversationId && conversationTitle">
                    <input
                        ref="mediaUploadInput"
                        type="file"
                        accept=".zip"
                        class="message-thread__file-input"
                        @change="onMediaFileSelected"
                    />
                    <button
                        class="message-thread__upload-media"
                        type="button"
                        @click="triggerMediaUpload"
                    >
                        Догрузить медиа
                    </button>
                </template>
                <button
                    v-if="conversationTitle"
                    class="message-thread__delete"
                    type="button"
                    @click="emit('delete')"
                >
                    Удалить чат
                </button>
            </div>
        </div>
        <div v-if="loading" class="message-thread__loading">Загрузка сообщений...</div>
        <div v-else class="message-thread__body">
            <div
                v-for="message in messages"
                :key="message.id"
                class="message-thread__message"
            >
                <div class="message-thread__meta">
                    <span class="message-thread__sender">{{ message.sender_name || 'Неизвестный' }}</span>
                    <time :datetime="message.sent_at">{{ formatDate(message.sent_at) }}</time>
                </div>
                <template v-if="message.attachment_url">
                    <div v-if="showAsImage(message)" class="message-thread__attachment">
                        <img :src="message.attachment_url" :alt="message.attachment_export_path || 'Медиа'" class="message-thread__img" loading="lazy" />
                    </div>
                    <div v-else-if="showAsAudio(message)" class="message-thread__attachment">
                        <audio controls :src="message.attachment_url" class="message-thread__audio">
                            Ваш браузер не поддерживает аудио.
                        </audio>
                    </div>
                    <div v-else-if="showAsVideo(message)" class="message-thread__attachment">
                        <video controls :src="message.attachment_url" class="message-thread__video">
                            Ваш браузер не поддерживает видео.
                        </video>
                    </div>
                    <div v-else class="message-thread__attachment">
                        <a :href="message.attachment_url" target="_blank" rel="noopener" class="message-thread__download">
                            Скачать вложение
                        </a>
                    </div>
                </template>
                <div v-else-if="message.is_media_without_file" class="message-thread__placeholder">
                    <span class="message-thread__placeholder-text">Медиа-вложение не загружено</span>
                    <span v-if="message.attachment_export_path" class="message-thread__placeholder-filename">{{ message.attachment_export_path }}</span>
                </div>
                <div v-if="message.text" class="message-thread__text">{{ message.text }}</div>
            </div>
            <div v-if="!messages.length" class="message-thread__empty">Сообщений пока нет.</div>
        </div>
    </div>
</template>

<style scoped>
.message-thread {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    background: var(--gray-0);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.message-thread__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    border-bottom: 1px solid var(--gray-200);
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.message-thread__title {
    font-weight: 600;
}

.message-thread__head-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.message-thread__file-input {
    position: absolute;
    width: 0;
    height: 0;
    opacity: 0;
    pointer-events: none;
}

.message-thread__upload-media {
    font-size: 0.75rem;
    color: var(--gray-600);
    background: none;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md, 0.25rem);
    cursor: pointer;
    padding: 0.25rem 0.5rem;
}

.message-thread__upload-media:hover {
    background: var(--gray-100);
    color: var(--gray-800);
}

.message-thread__delete {
    font-size: 0.75rem;
    color: var(--red-600);
    background: none;
    border: none;
    cursor: pointer;
    text-decoration: none;
    padding: 0;
}

.message-thread__delete:hover {
    color: var(--red-700);
    text-decoration: underline;
}

.message-thread__loading {
    padding: 1rem;
    font-size: 0.875rem;
    color: var(--gray-500);
}

.message-thread__body {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    max-height: 24rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.message-thread__message {
    display: flex;
    flex-direction: column;
}

.message-thread__meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: var(--gray-500);
}

.message-thread__sender {
    font-weight: 500;
    color: var(--gray-700);
}

.message-thread__text {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--gray-800);
    white-space: pre-wrap;
}

.message-thread__attachment {
    margin-top: 0.25rem;
}

.message-thread__img {
    max-width: 100%;
    max-height: 16rem;
    border-radius: var(--radius-md, 0.25rem);
    display: block;
}

.message-thread__audio,
.message-thread__video {
    max-width: 100%;
    display: block;
}

.message-thread__download {
    font-size: 0.875rem;
    color: var(--orange-600);
    text-decoration: none;
}

.message-thread__download:hover {
    text-decoration: underline;
}

.message-thread__placeholder {
    margin-top: 0.25rem;
    padding: 0.5rem;
    background: var(--gray-100);
    border-radius: var(--radius-md, 0.25rem);
    font-size: 0.8125rem;
    color: var(--gray-600);
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.message-thread__placeholder-text {
    font-style: italic;
}

.message-thread__placeholder-filename {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.message-thread__empty {
    font-size: 0.875rem;
    color: var(--gray-500);
}
</style>
