<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <nav class="flex flex-wrap gap-3 rounded-lg bg-white p-4 shadow sm:p-6" aria-label="Profile sections">
                    <a
                        href="#profile-information"
                        class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                    >
                        Profile information
                    </a>
                    <a
                        href="#update-password"
                        class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                    >
                        Update password
                    </a>
                    <a
                        href="#delete-account"
                        class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                    >
                        Delete account
                    </a>
                </nav>

                <div
                    id="profile-information"
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8 scroll-mt-6"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    id="update-password"
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8 scroll-mt-6"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div
                    id="delete-account"
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8 scroll-mt-6"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
