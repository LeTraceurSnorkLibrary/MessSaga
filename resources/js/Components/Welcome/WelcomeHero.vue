<script setup>
import WelcomeBadge from './WelcomeBadge.vue';
import TrustIndicator from './TrustIndicator.vue';
import UIButton from "@/Components/UIButton.vue";
import ArrowLeftToRight from "@/Components/base/ArrowLeftToRight.vue";

defineProps({
    canLogin: {type: Boolean, default: false},
    canRegister: {type: Boolean, default: false},
});
</script>
<template>
    <section class="welcome-hero">
        <div class="welcome-hero__container">
            <div class="welcome-hero__content">
                <WelcomeBadge text="Сохраните свои воспоминания навсегда"/>
                <h1 class="welcome-hero__title">
                    <span class="welcome-hero__title-accent">Ваш личный архив</span>
                    <br/>
                    <span class="welcome-hero__title-dark">всех переписок</span>
                </h1>
                <p class="welcome-hero__subtitle">
                    Сохраните переписки из Telegram, WhatsApp, VK и других мессенджеров
                    в одном безопасном месте. Ищите, вспоминайте, храните навсегда.
                </p>
                <div class="welcome-hero__actions">
                    <div class="welcome-hero__btn welcome-hero__btn--primary">
                        <UIButton
                            tag="a"
                            v-if="canRegister"
                            :href="route('register')"
                        >
                            <span>Начать прямо сейчас</span>
                            <ArrowLeftToRight/>
                        </UIButton>
                    </div>
                    <UIButton tag="a"
                              href="#how-it-works"
                              variant="secondary"
                              is-shadow
                    >
                        Как это работает?
                    </UIButton>
                </div>
                <div class="welcome-hero__trust">
                    <TrustIndicator text="Бесплатно"/>
                    <TrustIndicator text="Зашифровано"/>
                    <TrustIndicator text="Без рекламы"/>
                </div>
            </div>
            <div class="welcome-hero__mockup">
                <slot name="mockup"/>
            </div>
        </div>
    </section>
</template>
<style lang="scss" scoped>
@use '../../../scss/typography' as typography;

.welcome-hero {
    padding: 8rem 1.5rem 5rem;
}

.welcome-hero__container {
    max-width: 72rem;
    margin: 0 auto;
}

.welcome-hero__content {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2rem;
}

.welcome-hero__title {
    @include typography.text--120(clamp(2.5rem, 5vw, 4.375rem), typography.$font-weight--bold);

    margin: 0;
}

.welcome-hero__title-accent {
    background: linear-gradient(90deg, var(--orange-600), var(--rose-600), var(--amber-600));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.welcome-hero__title-dark {
    color: var(--gray-800);
}

.welcome-hero__subtitle {
    font-size: clamp(1.125rem, 2vw, 1.5rem);
    color: var(--gray-600);
    max-width: 48rem;
    margin: 0;
    line-height: 1.6;
}

.welcome-hero__actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding-top: 1rem;
}

@media (min-width: 640px) {
    .welcome-hero__actions {
        flex-direction: row;
        justify-content: center;
    }
}

.welcome-hero__trust {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    padding-top: 2rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.welcome-hero__mockup {
    margin-top: 4rem;
    position: relative;
}
</style>
