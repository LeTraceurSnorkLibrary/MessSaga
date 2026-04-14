<script setup>
import {onMounted, onUnmounted, ref, watch} from 'vue';

const open = ref(false);

const close = () => {
    open.value = false;
};

const toggle = () => {
    open.value = !open.value;
};

const onEscape = (event) => {
    if (event.key === 'Escape') {
        close();
    }
};

const lockBodyScroll = (locked) => {
    document.body.style.overflow = locked ? 'hidden' : '';
};

watch(open, (isOpen) => {
    lockBodyScroll(isOpen);
});

onMounted(() => document.addEventListener('keydown', onEscape));
onUnmounted(() => {
    document.removeEventListener('keydown', onEscape);
    lockBodyScroll(false);
});
</script>
<template>
    <div class="hamburger">
        <button
            :aria-expanded="open"
            aria-label="Меню"
            class="hamburger__button"
            type="button"
            @click="toggle"
        >
            <svg class="hamburger__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
            </svg>
        </button>
        <Teleport to="body">
            <div class="hamburger__portal" :style="{ pointerEvents: open ? 'auto' : 'none' }">
                <Transition enter-active-class="hamburger__backdrop-transition-active"
                            enter-from-class="hamburger__backdrop-transition-enter-start"
                            enter-to-class="hamburger__backdrop-transition-enter-end"
                            leave-active-class="hamburger__backdrop-transition-active"
                            leave-from-class="hamburger__backdrop-transition-leave-start"
                            leave-to-class="hamburger__backdrop-transition-leave-end"
                >
                    <div v-if="open" aria-hidden="true" class="hamburger__backdrop" @click="close"/>
                </Transition>

                <Transition enter-active-class="hamburger__sheet-transition-active"
                            enter-from-class="hamburger__sheet-transition-enter-start"
                            enter-to-class="hamburger__sheet-transition-enter-end"
                            leave-active-class="hamburger__sheet-transition-active"
                            leave-from-class="hamburger__sheet-transition-leave-start"
                            leave-to-class="hamburger__sheet-transition-leave-end"
                >
                    <div v-if="open" aria-modal="true" class="hamburger__sheet" role="dialog">
                        <div class="hamburger__closer">
                            <slot :close="close" name="closer">
                                <button
                                    aria-label="Закрыть меню"
                                    class="hamburger__close-button"
                                    type="button"
                                    @click="close"
                                >
                                    <svg class="hamburger__icon" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"/>
                                    </svg>
                                </button>
                            </slot>
                        </div>
                        <div class="hamburger__body">
                            <slot :close="close"/>
                        </div>
                    </div>
                </Transition>
            </div>
        </Teleport>
    </div>
</template>
<style lang="scss" scoped>
@use '../../../scss/components/hamburger/hamburger';
</style>
