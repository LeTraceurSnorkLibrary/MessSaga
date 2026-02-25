<script setup>
import UIInput from '@/Components/base/UIInput.vue';
import UIButton from '@/Components/UIButton.vue';
import {Link, useForm, usePage} from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>
<template>
    <section class="profile-form">
        <header class="profile-form__header">
            <h2 class="profile-form__title">
                Данные профиля
            </h2>
            <p class="profile-form__desc">
                Имя и email, привязанные к аккаунту.
            </p>
        </header>

        <form
            class="profile-form__body"
            @submit.prevent="form.patch(route('profile.update'), { preserveScroll: true })"
        >
            <UIInput
                id="name"
                v-model="form.name"
                :error="form.errors.name"
                autocomplete="name"
                autofocus
                label="Имя"
                required
                type="text"
            />

            <UIInput
                id="email"
                v-model="form.email"
                :error="form.errors.email"
                autocomplete="username"
                label="Email"
                required
                type="email"
            />

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="profile-form__verify">
                <p class="profile-form__verify-text">
                    Email не подтверждён.
                    <Link
                        :href="route('verification.send')"
                        as="button"
                        class="profile-form__verify-link"
                        method="post"
                    >
                        Отправить письмо повторно
                    </Link>
                </p>
                <p
                    v-show="status === 'verification-link-sent'"
                    class="profile-form__verify-sent"
                >
                    Ссылка для подтверждения отправлена на вашу почту.
                </p>
            </div>

            <div class="profile-form__actions">
                <UIButton type="submit" :disabled="form.processing">Сохранить</UIButton>
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

.profile-form__verify {
    margin-top: 0.25rem;
}

.profile-form__verify-text {
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray-800);
}

.profile-form__verify-link {
    font-size: 0.875rem;
    color: var(--gray-600);
    text-decoration: underline;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    font: inherit;
}

.profile-form__verify-link:hover {
    color: var(--gray-900);
}

.profile-form__verify-sent {
    margin: 0.5rem 0 0;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--green-600);
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
