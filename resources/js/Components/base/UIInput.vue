<script setup>
import {computed, onMounted, ref, useAttrs} from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'l',
        validator: (v) => ['l', 'm', 's'].includes(v),
    },
    labelSrOnly: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:modelValue']);

const inputEl = ref(null);
const attrs = useAttrs();
const inputAttrs = computed(() => {
    const {class: _c, ...rest} = attrs;
    return rest;
});

onMounted(() => {
    if (inputEl.value?.hasAttribute?.('autofocus')) {
        inputEl.value.focus();
    }
});

defineExpose({focus: () => inputEl.value?.focus()});
</script>
<template>
    <div :class="[`input--${size}`]" class="input">
        <label
            v-if="label || $slots.label"
            :class="{ 'input__label--sr-only': labelSrOnly }"
            :for="attrs.id"
            class="input__label"
        >
            <slot name="label">{{ label }}</slot>
        </label>
        <input
            ref="inputEl"
            :class="{ 'input__field--error': !!error }"
            :value="modelValue"
            class="input__field"
            v-bind="inputAttrs"
            @input="$emit('update:modelValue', ($event.target && $event.target.value) || '')"
        />
        <div aria-live="polite" class="input__error-line">
            {{ error }}
        </div>
    </div>
</template>
<style lang="scss" scoped>
@use '../../../scss/typography/index' as typography;

.input {
    --input-field-padding: 0.75rem 1rem;
    --input-field-font-size: 1rem;

    display: block;
    width: 100%;

    &--l {
        --input-field-padding: 0.75rem 1rem;
        --input-field-font-size: 1rem;
    }

    &--m {
        --input-field-padding: 0.5rem 0.75rem;
        --input-field-font-size: 0.875rem;
    }

    &--s {
        --input-field-padding: 0.375rem 0.5rem;
        --input-field-font-size: 0.75rem;
    }
}

.input__label {
    @include typography.text--150(0.875rem, typography.$font-weight--normal);

    display: block;
    margin-bottom: 0.375rem;
    color: var(--gray-700);

    &--sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
}

.input__field {
    @include typography.text--150(var(--input-field-font-size));

    display: block;
    width: 100%;
    color: var(--gray-900);
    padding: var(--input-field-padding);
    border: 2px solid var(--gray-300);
    border-radius: 0.375rem;
    background: var(--gray-0);
    transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
    box-sizing: border-box;

    &::placeholder {
        color: var(--gray-400);
    }

    &:focus {
        outline: none;
        border-color: var(--gray-500);
        box-shadow: 0 0 0 2px var(--gray-200);
    }

    &--error {
        border-color: var(--rose-500);

        &:focus {
            border-color: var(--rose-500);
            box-shadow: 0 0 0 2px var(--rose-100);
        }
    }
}

.input__error-line {
    @include typography.text--150(0.75rem, typography.$font-weight--normal);

    min-height: 1.25rem;
    margin-top: 0.25rem;
    color: var(--rose-600);
}
</style>
