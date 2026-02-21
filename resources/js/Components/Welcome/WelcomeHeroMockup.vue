<script setup>
import MessengerCard from '../base/MessengerCard.vue';
import TelegramIcon from "@/Components/particles/icons/Messengers/TelegramIcon.vue";
import WhatsAppIcon from "@/Components/particles/icons/Messengers/WhatsAppIcon.vue";
import VkIcon from "@/Components/particles/icons/Messengers/VkIcon.vue";
</script>
<template>
    <div class="mockup">
        <div class="mockup__gradient"/>
        <div class="mockup__window">
            <div class="mockup__titlebar">
                <div class="mockup__dots">
                    <span class="mockup__dot mockup__dot--red"/>
                    <span class="mockup__dot mockup__dot--yellow"/>
                    <span class="mockup__dot mockup__dot--green"/>
                </div>
                <div class="mockup__title">MessSaga</div>
            </div>
            <div class="mockup__body">
                <div class="mockup__grid">
                    <MessengerCard name="Telegram"
                                   subtitle="543 сообщения"
                                   variant="telegram"
                    >
                        <template #icon>
                            <TelegramIcon/>
                        </template>
                    </MessengerCard>
                    <MessengerCard name="WhatsApp"
                                   subtitle="129 сообщений"
                                   variant="whatsapp"
                    >
                        <template #icon>
                            <WhatsAppIcon/>
                        </template>
                    </MessengerCard>
                    <MessengerCard name="ВКонтакте"
                                   subtitle="87 сообщений"
                                   variant="vk"
                    >
                        <template #icon>
                            <VkIcon/>
                        </template>
                    </MessengerCard>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.mockup {
    position: relative;
}

.mockup__gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(254, 243, 199, 0.5), transparent);
    filter: blur(48px);
    pointer-events: none;
}

.mockup__window {
    position: relative;
    background: var(--gray-0);
    border-radius: 1.5rem;
    box-shadow: var(--shadow-2xl);
    border: 1px solid var(--orange-100);
    overflow: hidden;
}

.mockup__titlebar {
    background: linear-gradient(90deg, var(--orange-500), var(--rose-500));
    padding: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.mockup__dots {
    display: flex;
    gap: 0.375rem;
}

.mockup__dot {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
}

.mockup__dot--red {
    background: var(--red-400);
}

.mockup__dot--yellow {
    background: var(--yellow-400);
}

.mockup__dot--green {
    background: var(--green-400);
}

.mockup__title {
    flex: 1;
    text-align: center;
    color: var(--gray-0);
    font-size: 0.875rem;
    font-weight: 500;
}

.mockup__body {
    padding: 2rem;
    background: linear-gradient(135deg, rgba(255, 247, 237, 0.5), rgba(255, 241, 242, 0.5));
}

.mockup__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}
</style>
