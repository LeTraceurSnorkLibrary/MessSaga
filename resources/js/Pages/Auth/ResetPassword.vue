<script setup>
import UIButton from '@/Components/UIButton.vue';
import UIInput from '@/Components/base/UIInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password"/>

        <form @submit.prevent="submit">
            <UIInput
                id="email"
                v-model="form.email"
                :error="form.errors.email"
                label="Email"
                autocomplete="username"
                autofocus
                required
                type="email"
            />

            <UIInput
                id="password"
                v-model="form.password"
                :error="form.errors.password"
                label="Password"
                autocomplete="new-password"
                required
                type="password"
            />

            <UIInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                :error="form.errors.password_confirmation"
                label="Confirm Password"
                autocomplete="new-password"
                required
                type="password"
            />

            <div class="form-actions">
                <UIButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Reset Password
                </UIButton>
            </div>
        </form>
    </GuestLayout>
</template>
