<script setup>
import UIButton from '@/Components/UIButton.vue';
import UIInput from '@/Components/base/UIInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import UILink from "@/Components/UILink.vue";

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register"/>

        <form @submit.prevent="submit">
            <UIInput
                id="name"
                v-model="form.name"
                :error="form.errors.name"
                autocomplete="name"
                autofocus
                label="Имя/логин"
                placeholder="Можно не заполнять"
                type="text"
            />

            <UIInput
                id="email"
                v-model="form.email"
                :error="form.errors.email"
                autocomplete="username"
                label="E-mail"
                required
                type="email"
            />

            <UIInput
                id="password"
                v-model="form.password"
                :error="form.errors.password"
                autocomplete="new-password"
                label="Пароль"
                required
                type="password"
            />

            <UIInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                :error="form.errors.password_confirmation"
                autocomplete="new-password"
                label="Пароль заново"
                required
                type="password"
            />

            <div class="form-actions">
                <UIButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    type="submit"
                >
                    Зарегистрироваться
                </UIButton>
                <UILink :href="route('login')"
                        theme="muted"
                        variant="underlined"
                >
                    Есть аккаунт?
                </UILink>
            </div>
        </form>
    </GuestLayout>
</template>
<style lang="scss" scoped>
.form-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
}
</style>
