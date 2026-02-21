<!-- resources/js/Pages/Welcome.vue -->
<script setup>
import {Head} from '@inertiajs/vue3';
import WelcomeHeader from '@/Components/Welcome/WelcomeHeader.vue';
import WelcomeHero from '@/Components/Welcome/WelcomeHero.vue';
import WelcomeHeroMockup from '@/Components/Welcome/WelcomeHeroMockup.vue';
import SectionTitle from '@/Components/Welcome/SectionTitle.vue';
import AdvantageCard from '@/Components/base/AdvantageCard.vue';
import HowItWorksStep from '@/Components/Welcome/HowItWorksStep.vue';
import SupportedMessengers from '@/Components/Welcome/SupportedMessengers.vue';
import WelcomeCtaSection from '@/Components/Welcome/WelcomeCtaSection.vue';
import WelcomeFooter from '@/Components/Welcome/WelcomeFooter.vue';
import LockIcon from '@/Components/particles/icons/LockIcon.vue';
import MagnifyingGlassIcon from "@/Components/particles/icons/MagnifyingGlassIcon.vue";
import ImgIcon from "@/Components/particles/icons/ImgIcon.vue";
import UploadIcon from "@/Components/particles/icons/UploadIcon.vue";
import ClockIcon from "@/Components/particles/icons/ClockIcon.vue";
import DollarIcon from "@/Components/particles/icons/DollarIcon.vue";

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

        <section class="welcome-page__section welcome-page__section--light">
            <div class="welcome-page__container">
                <SectionTitle
                    title="Почему "
                    accent="MessSaga?"
                    subtitle="Сохраните важные моменты вашей цифровой жизни в безопасном месте"
                />
                <div class="welcome-page__why-grid">
                    <AdvantageCard
                        title="Полная безопасность"
                        description="Все сообщения шифруются перед сохранением. Только вы имеете доступ к своим данным."
                        variant="orange"
                    >
                        <template #icon>
                            <LockIcon />
                        </template>
                    </AdvantageCard>
                    <AdvantageCard
                        title="Умный поиск"
                        description="Найдите любое сообщение за секунды. Поиск по тексту, дате, контактам и мессенджерам."
                        variant="rose"
                    >
                        <template #icon>
                            <MagnifyingGlassIcon/>
                        </template>
                    </AdvantageCard>
                    <AdvantageCard
                        title="С медиафайлами"
                        description="Фото, видео, голосовые сообщения — всё сохраняется вместе с перепиской."
                        variant="amber"
                    >
                        <template #icon>
                            <ImgIcon/>
                        </template>
                    </AdvantageCard>
                    <AdvantageCard
                        title="Простой импорт"
                        description="Экспортируйте чат из мессенджера и загрузите в MessSaga. Всё автоматически!"
                        variant="blue"
                    >
                        <template #icon>
                            <UploadIcon/>
                        </template>
                    </AdvantageCard>
                    <AdvantageCard
                        title="Навсегда"
                        description="Ваши переписки не исчезнут, даже если аккаунт в мессенджере будет удалён."
                        variant="purple"
                    >
                        <template #icon>
                            <ClockIcon/>
                        </template>
                    </AdvantageCard>
                    <AdvantageCard
                        title="Бесплатно"
                        description="MessSaga абсолютно бесплатен. Без рекламы, без платных подписок."
                        variant="green"
                    >
                        <template #icon>
                            <DollarIcon/>
                        </template>
                    </AdvantageCard>
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
