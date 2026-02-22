<script setup>
import SectionTitle from '../Welcome/SectionTitle.vue';
import TelegramIcon from '@/Components/particles/icons/Messengers/TelegramIcon.vue';
import WhatsAppIcon from '@/Components/particles/icons/Messengers/WhatsAppIcon.vue';
import VkIcon from '@/Components/particles/icons/Messengers/VkIcon.vue';
import SupportedMessengerCard from '@/Components/base/SupportedMessengerCard.vue';
</script>
<template>
    <section class="supported">
        <div class="supported__container">
            <SectionTitle
                subtitle="Мы постоянно добавляем новые платформы"
                title="Поддерживаемые мессенджеры"
            />
            <div class="supported__grid">
                <SupportedMessengerCard name="Telegram"
                                        variant="telegram"
                >
                    <template #icon>
                        <TelegramIcon/>
                    </template>
                </SupportedMessengerCard>
                <SupportedMessengerCard name="WhatsApp"
                                        variant="whatsapp"
                >
                    <template #icon>
                        <WhatsAppIcon/>
                    </template>
                </SupportedMessengerCard>
                <SupportedMessengerCard name="ВКонтакте"
                                        variant="vk"
                >
                    <template #icon>
                        <VkIcon/>
                    </template>
                </SupportedMessengerCard>
                <SupportedMessengerCard name="Скоро..."
                                        placeholder
                />
            </div>
        </div>
    </section>
</template>
<style lang="scss" scoped>
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
    grid-template-columns: repeat(1, 1fr);
    gap: 1.5rem;

    @media (min-width: 768px) {
        grid-template-columns: repeat(2, 1fr);
    }

    @media (min-width: 1024px) {
        grid-template-columns: repeat(4, 1fr);
    }
}
</style>
