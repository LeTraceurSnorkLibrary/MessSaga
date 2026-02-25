<script setup>
import UIInput from '@/Components/base/UIInput.vue';
import Modal from '@/Components/Modal.vue';
import UIButton from '@/Components/UIButton.vue';
import {useForm} from '@inertiajs/vue3';
import {nextTick, ref} from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>
<template>
    <section class="profile-form profile-form--danger">
        <header class="profile-form__header">
            <h2 class="profile-form__title">
                Удаление аккаунта
            </h2>
            <p class="profile-form__desc">
                После удаления все данные аккаунта будут безвозвратно удалены. Сохраните нужные переписки и данные
                заранее.
            </p>
        </header>

        <UIButton variant="danger" @click="confirmUserDeletion">
            Удалить аккаунт
        </UIButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="profile-form__modal">
                <h2 class="profile-form__title">
                    Вы уверены, что хотите удалить аккаунт?
                </h2>
                <p class="profile-form__desc">
                    Все данные будут удалены без возможности восстановления. Введите пароль для подтверждения.
                </p>

                <div class="profile-form__modal-field">
                    <UIInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        :error="form.errors.password"
                        label="Пароль"
                        label-sr-only
                        placeholder="Пароль"
                        type="password"
                        @keyup.enter="deleteUser"
                    />
                </div>

                <div class="profile-form__modal-actions">
                    <UIButton variant="secondary" @click="closeModal">
                        Отмена
                    </UIButton>
                    <UIButton
                        :class="{ 'profile-form__button--busy': form.processing }"
                        :disabled="form.processing"
                        variant="danger"
                        @click="deleteUser"
                    >
                        Удалить аккаунт
                    </UIButton>
                </div>
            </div>
        </Modal>
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

.profile-form--danger .profile-form__desc {
    margin-bottom: 1rem;
}

.profile-form__modal {
    padding: 1.5rem;
}

.profile-form__modal .profile-form__title {
    margin-bottom: 0.25rem;
}

.profile-form__modal .profile-form__desc {
    margin-top: 0;
}

.profile-form__modal-field {
    margin-top: 1.5rem;
}

.profile-form__modal-actions {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.profile-form__button--busy {
    opacity: 0.7;
}
</style>
