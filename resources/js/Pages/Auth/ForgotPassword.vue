<script setup>
import UIButton from '@/Components/UIButton.vue';
import UIInput from '@/Components/base/UIInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import UILink from "@/Components/UILink.vue";

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password"/>

        ←
        <UILink
            :href="route('login')"
            theme="muted"
            variant="underlined"
        >
            Обратно к форме входа
        </UILink>

        <p class="auth-intro">
            <b>Забыли пароль?</b><br/>Не проблема, сейчас пришлём ссылку на создание нового!
        </p>

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
                label="E-mail"
                required
                type="email"
            />

            <div class="form-actions">
                <UIButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    type="submit"
                >
                    Отправить ссылку
                </UIButton>
                <UILink
                    :href="route('register')"
                    theme="muted"
                    variant="underlined"
                >
                    Регистрация
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
