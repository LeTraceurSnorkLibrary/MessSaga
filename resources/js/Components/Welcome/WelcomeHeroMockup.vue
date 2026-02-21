<script setup>
import MessengerCard from './MessengerCard.vue';
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
              <svg fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.941z" />
              </svg>
            </template>
          </MessengerCard>
          <MessengerCard name="WhatsApp" subtitle="129 сообщений" variant="green">
            <template #icon>
              <svg fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
              </svg>
            </template>
          </MessengerCard>
          <MessengerCard name="ВКонтакте" subtitle="87 сообщений" variant="indigo">
            <template #icon>
              <svg fill="currentColor" viewBox="0 0 24 24">
                <path d="M12.785 16.241s.288-.032.436-.194c.136-.148.132-.427.132-.427s-.019-1.302.574-1.494c.586-.189 1.336 1.26 2.131 1.818.605.423 1.064.33 1.064.33l2.137-.03s1.117-.07.587-.966c-.044-.073-.31-.664-1.597-1.88-1.347-1.272-1.166-1.066.456-3.264.988-1.338 1.382-2.154 1.259-2.503-.117-.332-.84-.245-.84-.245l-2.406.015s-.178-.025-.31.056c-.13.079-.213.263-.213.263s-.382 1.036-.89 1.918c-1.07 1.86-1.499 1.958-1.674 1.842-.409-.267-.307-1.075-.307-1.648 0-1.792.266-2.537-.519-2.732-.261-.065-.453-.108-1.12-.115-.857-.009-1.583.003-1.994.208-.274.137-.485.442-.356.459.159.022.52.099.711.365.247.342.238 1.11.238 1.11s.142 2.109-.331 2.372c-.325.18-.77-.188-1.726-1.872-.489-.862-.859-1.816-.859-1.816s-.071-.178-.198-.273c-.154-.115-.37-.152-.37-.152l-2.286.015s-.343.01-.469.162c-.112.135-.009.414-.009.414s1.797 4.282 3.832 6.436c1.87 1.979 3.986 1.848 3.986 1.848h.958z" />
              </svg>
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

.mockup__dot--red { background: var(--red-400); }
.mockup__dot--yellow { background: var(--yellow-400); }
.mockup__dot--green { background: var(--green-400); }

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
