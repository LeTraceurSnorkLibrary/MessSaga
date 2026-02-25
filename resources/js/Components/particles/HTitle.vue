<script setup>
defineProps({
    size: {type: String, default: 'h2'},
    tag: {type: String, default: 'h2'},
});
</script>
<template>
    <Component
        :is="tag"
        :class="[
            'title',
            `title--${size}`
            ]"
    >
        <slot/>
    </Component>
</template>
<style lang="scss" scoped>
@use '../../../scss/typography' as typography;

.title {
    @include typography.title(3rem);

    &--h2 {
        @include typography.title(2.5rem);
    }

    &--h3 {
        @include typography.title(2rem);
    }

    &--h4 {
        @include typography.title(1.75rem);
    }

    &--h5 {
        @include typography.title(1.5rem);
    }

    &--h6 {
        @include typography.title(1.25rem);
    }
}
</style>
