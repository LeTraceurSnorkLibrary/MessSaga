<script setup>
import MessengerCard from './MessengerCard.vue';
import SectionTitle from './SectionTitle.vue';
import TelegramIcon from "@/Components/particles/icons/Messengers/TelegramIcon.vue";
import WhatsAppIcon from "@/Components/particles/icons/Messengers/WhatsAppIcon.vue";
import VkIcon from "@/Components/particles/icons/Messengers/VkIcon.vue";
</script>

<template>
    <section class="supported">
        <div class="supported__container">
            <SectionTitle
                title="Поддерживаемые мессенджеры"
                subtitle="Мы постоянно добавляем новые платформы"
            />
            <div class="supported__grid">
                <MessengerCard name="Telegram"
                               variant="telegram"
                >
                    <template #icon>
                        <TelegramIcon/>
                    </template>
                </MessengerCard>
                <MessengerCard name="WhatsApp"
                               variant="whatsapp"
                >
                    <template #icon>
                        <WhatsAppIcon/>
                    </template>
                </MessengerCard>
                <MessengerCard name="ВКонтакте"
                               variant="vk"
                >
                    <template #icon>
                        <VkIcon/>
                    </template>
                </MessengerCard>
                <MessengerCard name="Скоро..."
                               variant="work-in-progress"
                               placeholder
                />
            </div>
        </div>
    </section>
</template>

<style scoped>
.supported {
    padding: 5rem 1.5rem;
    background: linear-gradient(135deg, var(--orange-50), var(--amber-50));
}

.supported__container {
    max-width: 56rem;
    margin: 0 auto;
    text-align: center;
}

.supported__grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .supported__grid {
        grid-template-columns: repeat(4, 1fr);
    }
}
</style>
