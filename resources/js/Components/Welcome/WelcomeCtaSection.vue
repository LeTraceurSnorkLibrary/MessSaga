<script setup>
import {Link} from '@inertiajs/vue3';
import UIButton from "@/Components/UIButton.vue";

defineProps({
    canLogin: {type: Boolean, default: false},
    canRegister: {type: Boolean, default: false},
});
</script>

<template>
    <section class="cta-section">
        <div class="cta-section__container">
            <h2 class="cta-section__title">Готовы сохранить свои воспоминания?</h2>
            <p class="cta-section__subtitle">
                Присоединяйтесь к MessSaga и никогда не теряйте важные моменты
            </p>
            <div class="cta-section__actions">
                <UIButton
                    v-if="canRegister"
                    tag="a"
                    :href="route('register')"
                    variant="tertiary"
                >
                    Начать бесплатно
                </UIButton>
                <UIButton
                    v-if="canLogin"
                    tag="a"
                    :href="route('login')"
                    variant="tertiary"
                    is-stroke
                    is-slight-hover
                >
                    Уже есть аккаунт
                </UIButton>
            </div>
        </div>
    </section>
</template>

<style scoped>
.cta-section {
    padding: 6rem 1.5rem;
    background: linear-gradient(90deg, var(--orange-500), var(--rose-500), var(--amber-500));
}

.cta-section__container {
    max-width: 56rem;
    margin: 0 auto;
    text-align: center;
    color: var(--gray-0);
}

.cta-section__title {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 700;
    margin: 0 0 1.5rem;
}

.cta-section__subtitle {
    font-size: clamp(1.125rem, 2vw, 1.5rem);
    margin: 0 0 2.5rem;
    opacity: 0.9;
}

.cta-section__actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;

    @media (min-width: 640px) {
        flex-direction: row;
    }
}
</style>
