<script setup>
import MessengerCard from './MessengerCard.vue';
import TelegramIcon from './TelegramIcon.vue';
import WhatsAppIcon from './WhatsAppIcon.vue';
import VkIcon from './VkIcon.vue';
</script>

<template>
  <div class="mockup">
    <div class="mockup__gradient" />
    <div class="mockup__window">
      <div class="mockup__titlebar">
        <div class="mockup__dots">
          <span class="mockup__dot mockup__dot--red" />
          <span class="mockup__dot mockup__dot--yellow" />
          <span class="mockup__dot mockup__dot--green" />
        </div>
        <div class="mockup__title">MessSaga</div>
      </div>
      <div class="mockup__body">
        <div class="mockup__grid">
          <MessengerCard name="Telegram" subtitle="543 сообщения" variant="blue">
            <template #icon>
              <TelegramIcon />
            </template>
          </MessengerCard>
          <MessengerCard name="WhatsApp" subtitle="129 сообщений" variant="green">
            <template #icon>
              <WhatsAppIcon />
            </template>
          </MessengerCard>
          <MessengerCard name="ВКонтакте" subtitle="87 сообщений" variant="vk">
            <template #icon>
              <VkIcon />
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
  background: linear-gradient(to top, var(--welcome-gradient-to), transparent);
  filter: blur(48px);
  pointer-events: none;
}

.mockup__window {
  position: relative;
  background: var(--welcome-white);
  border-radius: 1.5rem;
  box-shadow: var(--welcome-shadow-2xl);
  border: 1px solid var(--welcome-orange-100);
  overflow: hidden;
}

.mockup__titlebar {
  background: linear-gradient(90deg, var(--welcome-orange-500), var(--welcome-rose-500));
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

.mockup__dot--red { background: var(--welcome-red-400); }
.mockup__dot--yellow { background: var(--welcome-yellow-400); }
.mockup__dot--green { background: var(--welcome-green-400); }

.mockup__title {
  flex: 1;
  text-align: center;
  color: var(--welcome-white);
  font-size: 0.875rem;
  font-weight: 500;
}

.mockup__body {
  padding: 2rem;
  background: linear-gradient(135deg, var(--welcome-gradient-from), var(--welcome-gradient-to));
}

.mockup__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}
</style>
