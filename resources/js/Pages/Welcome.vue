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
import AddUserIcon from "@/Components/particles/icons/AddUserIcon.vue";
import CloudUploadIcon from "@/Components/particles/icons/CloudUploadIcon.vue";
import LikeIcon from "@/Components/particles/icons/LikeIcon.vue";

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
                            <AddUserIcon/>
                        </template>
                    </HowItWorksStep>
                    <HowItWorksStep
                        :step="2"
                        title="Загрузите чаты"
                        description="Экспортируйте переписки из мессенджера и загрузите файл в MessSaga."
                        variant="rose"
                    >
                        <template #icon>
                            <CloudUploadIcon/>
                        </template>
                    </HowItWorksStep>
                    <HowItWorksStep
                        :step="3"
                        title="Наслаждайтесь!"
                        description="Ищите, читайте, вспоминайте. Ваши данные в безопасности навсегда."
                        variant="amber"
                    >
                        <template #icon>
                            <LikeIcon/>
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
