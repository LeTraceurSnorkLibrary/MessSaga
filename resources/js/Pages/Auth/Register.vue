<script setup>
import UIButton from '@/Components/UIButton.vue';
import UIInput from '@/Components/base/UIInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';

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
                class="mt-1"
                label="Name"
                required
                type="text"
            />

            <UIInput
                id="email"
                v-model="form.email"
                :error="form.errors.email"
                autocomplete="username"
                class="mt-4"
                label="Email"
                required
                type="email"
            />

            <UIInput
                id="password"
                v-model="form.password"
                :error="form.errors.password"
                autocomplete="new-password"
                class="mt-4"
                label="Password"
                required
                type="password"
            />

            <UIInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                :error="form.errors.password_confirmation"
                autocomplete="new-password"
                class="mt-4"
                label="Confirm Password"
                required
                type="password"
            />

            <div class="mt-4 flex flex-wrap items-center justify-end gap-3">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Already registered?
                </Link>
                <UIButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </UIButton>
            </div>
        </form>
    </GuestLayout>
</template>
