<script setup>
import UIInput from '@/Components/base/UIInput.vue';
import UIButton from '@/Components/UIButton.vue';
import {useForm} from '@inertiajs/vue3';
import {ref} from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>
<template>
    <section class="profile-form">
        <header class="profile-form__header">
            <h2 class="profile-form__title">
                Смена пароля
            </h2>
            <p class="profile-form__desc">
                Используйте надёжный пароль для безопасности аккаунта.
            </p>
        </header>

        <form class="profile-form__body" @submit.prevent="updatePassword">
            <UIInput
                id="current_password"
                ref="currentPasswordInput"
                v-model="form.current_password"
                :error="form.errors.current_password"
                autocomplete="current-password"
                label="Текущий пароль"
                type="password"
            />

            <UIInput
                id="password"
                ref="passwordInput"
                v-model="form.password"
                :error="form.errors.password"
                autocomplete="new-password"
                label="Новый пароль"
                type="password"
            />

            <UIInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                :error="form.errors.password_confirmation"
                autocomplete="new-password"
                label="Подтверждение пароля"
                type="password"
            />

            <div class="profile-form__actions">
                <UIButton :disabled="form.processing" type="submit">
                    Сохранить
                </UIButton>
                <Transition
                    enter-active-class="profile-form__success--enter-active"
                    enter-from-class="profile-form__success--enter-from"
                    leave-active-class="profile-form__success--leave-active"
                    leave-to-class="profile-form__success--leave-to"
                >
                    <p v-if="form.recentlySuccessful" class="profile-form__success">
                        Сохранено.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
<style scoped>
.profile-form__header {
    margin-bottom: 1.5rem;
}

.profile-form__title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 500;
    color: var(--gray-900);
}

.profile-form__desc {
    margin: 0.25rem 0 0;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.profile-form__body {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.profile-form__actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.profile-form__success {
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.profile-form__success--enter-active,
.profile-form__success--leave-active {
    transition: opacity 0.15s ease;
}

.profile-form__success--enter-from,
.profile-form__success--leave-to {
    opacity: 0;
}
</style>
