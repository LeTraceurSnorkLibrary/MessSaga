<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import {ref} from 'vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const tabs = [
    {id: 'info', label: 'Данные профиля'},
    {id: 'password', label: 'Смена пароля'},
    {id: 'delete', label: 'Удаление аккаунта'},
];

const activeTab = ref('info');

// Опционально: синхронизация с hash при первой загрузке
if (typeof window !== 'undefined' && window.location.hash) {
    const hash = window.location.hash.slice(1);
    if (tabs.some((t) => t.id === hash)) {
        activeTab.value = hash;
    }
}

const setTab = (id) => {
    activeTab.value = id;
};
</script>
<template>
    <Head title="Профиль"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="profile-header__title">
                Профиль
            </h2>
        </template>

        <div class="profile-page">
            <div class="profile-page__container">
                <nav aria-label="Разделы профиля" class="profile-page__tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        :class="{ 'profile-page__tab--active': activeTab === tab.id }"
                        class="profile-page__tab"
                        type="button"
                        @click="setTab(tab.id)"
                    >
                        {{ tab.label }}
                    </button>
                </nav>

                <div class="profile-page__panel">
                    <section
                        v-show="activeTab === 'info'"
                        class="profile-page__section"
                    >
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="profile-page__form"
                        />
                    </section>

                    <section
                        v-show="activeTab === 'password'"
                        class="profile-page__section"
                    >
                        <UpdatePasswordForm class="profile-page__form"/>
                    </section>

                    <section
                        v-show="activeTab === 'delete'"
                        class="profile-page__section"
                    >
                        <DeleteUserForm class="profile-page__form"/>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style lang="scss" scoped>
@use '../../../scss/typography' as typography;

.profile-header__title {
    @include typography.text--150(1.25rem, typography.$font-weight--underbold);

    margin: 0;
    color: var(--gray-800);
}

.profile-page {
    padding: 2rem 0;
}

.profile-page__container {
    max-width: 80rem;
    margin: 0 auto;
    padding: 0 1rem;

    @media (min-width: 640px) {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    @media (min-width: 1024px) {
        padding-left: 2rem;
        padding-right: 2rem;
    }
}

.profile-page__tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: -1px;
    border-bottom: 1px solid var(--gray-200);
}

.profile-page__panel {
    margin-top: 1rem;
}

.profile-page__tab {
    @include typography.text--150(0.875rem, typography.$font-weight--semibold);

    cursor: pointer;
    padding: 0.5rem 0.75rem;
    color: var(--gray-500);
    white-space: nowrap;
    border: none;
    border-bottom: 2px solid transparent;
    background: transparent;
    transition: color 0.2s ease, border-color 0.2s ease;
}

.profile-page__tab:hover {
    color: var(--gray-700);
    border-color: var(--gray-300);
}

.profile-page__tab--active {
    color: var(--indigo-600);
    border-color: var(--indigo-500);
}

.profile-page__section {
    padding: 1.5rem 0 0;

    @media (min-width: 640px) {
        padding-top: 2rem;
    }
}

.profile-page__form {
    max-width: 36rem;
}
</style>
