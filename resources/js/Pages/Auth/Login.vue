<script setup>
import UIButton from '@/Components/UIButton.vue';
import UILink from '@/Components/UILink.vue';
import UIInput from '@/Components/base/UIInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>
<template>
    <GuestLayout>
        <Head title="Log in"/>

        <p v-if="status" class="auth-status">
            {{ status }}
        </p>

        <form @submit.prevent="submit">
            <UIInput
                id="email"
                v-model="form.email"
                :error="form.errors.email"
                autocomplete="username"
                autofocus
                label="Email"
                required
                type="email"
            />

            <UIInput
                id="password"
                v-model="form.password"
                :error="form.errors.password"
                autocomplete="current-password"
                label="Пароль"
                required
                type="password"
            >
                <template #label>
                    Пароль
                    <UILink
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        theme="muted"
                        variant="underlined"
                    >
                        <sub>(я не помню)</sub>
                    </UILink>
                </template>
            </UIInput>

            <div class="form-remember">
                <label class="form-remember__label">
                    <Checkbox v-model:checked="form.remember" name="remember"/>
                    <span class="form-remember__text">Запомнить меня</span>
                </label>
            </div>

            <div class="form-actions">
                <UIButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    type="submit"
                    variant="primary"
                >
                    Войти
                </UIButton>
                <UILink
                    :href="route('register')"
                    theme="muted"
                    variant="underlined"
                >
                    Зарегистрироваться
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
