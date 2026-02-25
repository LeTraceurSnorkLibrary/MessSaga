<script setup>
import {computed, onMounted, onUnmounted, ref} from 'vue';

const props = defineProps({
    align: {type: String, default: 'right'},
    width: {type: [String, Number], default: '48'},
    contentClasses: {type: String, default: ''},
});

const open = ref(false);

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') open.value = false;
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthStyle = computed(() => ({
    width: props.width === '48' ? '12rem' : `${Number(props.width)}px`,
}));

const alignmentStyle = computed(() => {
    if (props.align === 'left') return {left: 0};
    if (props.align === 'right') return {right: 0, left: 'auto'};
    return {left: '50%', transform: 'translateX(-50%)'};
});

const panelStyle = computed(() => ({...widthStyle.value, ...alignmentStyle.value}));
</script>

<template>
    <div class="dropdown">
        <div @click="open = !open">
            <slot name="trigger"/>
        </div>

        <div
            v-show="open"
            aria-hidden="true"
            class="dropdown__overlay"
            @click="open = false"
        />

        <Transition
            enter-active-class="dropdown__enter-active"
            enter-from-class="dropdown__enter-from"
            enter-to-class="dropdown__enter-to"
            leave-active-class="dropdown__leave-active"
            leave-from-class="dropdown__leave-from"
            leave-to-class="dropdown__leave-to"
        >
            <div
                v-show="open"
                :style="panelStyle"
                class="dropdown__panel"
                @click="open = false"
            >
                <div :class="contentClasses" class="dropdown__content">
                    <slot name="content"/>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.dropdown {
    position: relative;
}

.dropdown__overlay {
    position: fixed;
    inset: 0;
    z-index: 40;
}

.dropdown__panel {
    position: absolute;
    z-index: 50;
    margin-top: 0.5rem;
    border-radius: 0.375rem;
    box-shadow: var(--shadow-lg);
}

.dropdown__enter-active,
.dropdown__leave-active {
    transition: opacity 200ms ease, transform 200ms ease;
}

.dropdown__enter-from,
.dropdown__leave-to {
    opacity: 0;
    transform: scale(0.95);
}

.dropdown__enter-to,
.dropdown__leave-from {
    opacity: 1;
    transform: scale(1);
}

.dropdown__content {
    padding: 0.25rem 0;
    background: var(--gray-0);
    border-radius: 0.375rem;
    box-shadow: 0 0 0 1px rgb(0 0 0 / 0.05);
}
</style>
