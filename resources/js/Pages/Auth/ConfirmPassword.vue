<script setup>
import UIButton from '@/Components/UIButton.vue';
import UIInput from '@/Components/base/UIInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password"/>

        <p class="auth-intro">
            This is a secure area of the application. Please confirm your
            password before continuing.
        </p>

        <form @submit.prevent="submit">
            <UIInput
                id="password"
                v-model="form.password"
                :error="form.errors.password"
                label="Password"
                autocomplete="current-password"
                autofocus
                required
                type="password"
            />

            <div class="form-actions">
                <UIButton
                    type="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Confirm
                </UIButton>
            </div>
        </form>
    </GuestLayout>
</template>
