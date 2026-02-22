<script setup>
import UIButton from '@/Components/UIButton.vue';
import UIInput from '@/Components/base/UIInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';

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

        <p class="auth-intro">
            Forgot your password? No problem. Just let us know your email
            address and we will email you a password reset link that will allow
            you to choose a new one.
        </p>

        <p v-if="status" class="auth-status">
            {{ status }}
        </p>

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

            <div class="form-actions">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Back to Log in
                </Link>
                <Link
                    :href="route('register')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Register
                </Link>
                <UIButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Email Password Reset Link
                </UIButton>
            </div>
        </form>
    </GuestLayout>
</template>
