<script setup>
import UIButton from '@/Components/UIButton.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';
import {computed} from 'vue';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <p class="auth-intro">
            Thanks for signing up! Before getting started, could you verify your
            email address by clicking on the link we just emailed to you? If you
            didn't receive the email, we will gladly send you another.
        </p>

        <p v-if="verificationLinkSent" class="auth-status">
            A new verification link has been sent to the email address you
            provided during registration.
        </p>

        <form @submit.prevent="submit">
            <div class="form-actions form-actions--between">
                <UIButton
                    type="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Resend Verification Email
                </UIButton>

                <Link
                    :href="route('logout')"
                    as="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    method="post"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
