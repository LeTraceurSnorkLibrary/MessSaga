<!-- resources/js/Pages/Welcome.vue -->
<script setup>
import {Head} from '@inertiajs/vue3';
import WelcomeHeader from '@/Components/Welcome/WelcomeHeader.vue';
import WelcomeHero from '@/Components/Welcome/WelcomeHero.vue';
import WelcomeHeroMockup from '@/Components/Welcome/WelcomeHeroMockup.vue';
import SectionTitle from '@/Components/Welcome/SectionTitle.vue';
import WhyMessSagaCard from '@/Components/Welcome/WhyMessSagaCard.vue';
import HowItWorksStep from '@/Components/Welcome/HowItWorksStep.vue';
import SupportedMessengers from '@/Components/Welcome/SupportedMessengers.vue';
import WelcomeCtaSection from '@/Components/Welcome/WelcomeCtaSection.vue';
import WelcomeFooter from '@/Components/Welcome/WelcomeFooter.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    copyrightYear: String,
});
</script>

<template>
    <Head title="MessSaga — Ваш личный архив воспоминаний"/>

    <div class="welcome-page">
        <WelcomeHeader :can-login="canLogin" :can-register="canRegister"/>

        <WelcomeHero :can-login="canLogin" :can-register="canRegister">
            <template #mockup>
                <WelcomeHeroMockup/>
            </template>
        </WelcomeHero>

        <!-- Почему MessSaga? -->
        <section class="welcome-page__section welcome-page__section--light">
            <div class="welcome-page__container">
                <SectionTitle
                    title="Почему "
                    accent="MessSaga?"
                    subtitle="Сохраните важные моменты вашей цифровой жизни в безопасном месте"
                />
                <div class="welcome-page__why-grid">
                    <WhyMessSagaCard
                        title="Полная безопасность"
                        description="Все сообщения шифруются перед сохранением. Только вы имеете доступ к своим данным."
                        variant="orange"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </template>
                    </WhyMessSagaCard>
                    <WhyMessSagaCard
                        title="Умный поиск"
                        description="Найдите любое сообщение за секунды. Поиск по тексту, дате, контактам и мессенджерам."
                        variant="rose"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round"
                                      stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </template>
                    </WhyMessSagaCard>
                    <WhyMessSagaCard
                        title="С медиафайлами"
                        description="Фото, видео, голосовые сообщения — всё сохраняется вместе с перепиской."
                        variant="amber"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </template>
                    </WhyMessSagaCard>
                    <WhyMessSagaCard
                        title="Простой импорт"
                        description="Экспортируйте чат из мессенджера и загрузите в MessSaga. Всё автоматически!"
                        variant="blue"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </template>
                    </WhyMessSagaCard>
                    <WhyMessSagaCard
                        title="Навсегда"
                        description="Ваши переписки не исчезнут, даже если аккаунт в мессенджере будет удалён."
                        variant="purple"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                                      stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </template>
                    </WhyMessSagaCard>
                    <WhyMessSagaCard
                        title="Бесплатно"
                        description="MessSaga абсолютно бесплатен. Без рекламы, без платных подписок."
                        variant="green"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </template>
                    </WhyMessSagaCard>
                </div>
            </div>
        </section>

        <!-- Как это работает? -->
        <section id="how-it-works" class="welcome-page__section">
            <div class="welcome-page__container">
                <SectionTitle
                    title="Как это работает?"
                    subtitle="Три простых шага до сохранения ваших воспоминаний"
                />
                <div class="welcome-page__steps">
                    <div class="welcome-page__steps-connector" aria-hidden="true"/>
                    <HowItWorksStep
                        :step="1"
                        title="Зарегистрируйтесь"
                        description="Создайте бесплатный аккаунт за 30 секунд. Только email и пароль."
                        variant="orange"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </template>
                    </HowItWorksStep>
                    <HowItWorksStep
                        :step="2"
                        title="Загрузите чаты"
                        description="Экспортируйте переписки из мессенджера и загрузите файл в MessSaga."
                        variant="rose"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </template>
                    </HowItWorksStep>
                    <HowItWorksStep
                        :step="3"
                        title="Наслаждайтесь!"
                        description="Ищите, читайте, вспоминайте. Ваши данные в безопасности навсегда."
                        variant="amber"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </template>
                    </HowItWorksStep>
                </div>
            </div>
        </section>

        <SupportedMessengers/>

        <WelcomeCtaSection :can-login="canLogin" :can-register="canRegister"/>

        <WelcomeFooter :copyright-year="copyrightYear"/>
    </div>
</template>

<style scoped>
.welcome-page {
    min-height: 100vh;
    background: linear-gradient(135deg, var(--amber-50), var(--orange-50), var(--rose-50));
}

.welcome-page__section {
    padding: 5rem 1.5rem;
}

.welcome-page__section--light {
    background: rgba(255, 255, 255, 0.5);
}

.welcome-page__container {
    max-width: 72rem;
    margin: 0 auto;
}

.welcome-page__why-grid {
    display: grid;
    gap: 2rem;
}

@media (min-width: 768px) {
    .welcome-page__why-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.welcome-page__steps {
    display: grid;
    gap: 2rem;
    position: relative;
}

@media (min-width: 768px) {
    .welcome-page__steps {
        grid-template-columns: repeat(3, 1fr);
    }
}

.welcome-page__steps-connector {
    display: none;
    position: absolute;
    top: 4rem;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--orange-200), var(--rose-200), var(--amber-200));
    pointer-events: none;
}

@media (min-width: 768px) {
    .welcome-page__steps-connector {
        display: block;
    }
}
</style>
