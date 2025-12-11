<template>
    <BaseButton v-bind="$attrs" variant="secondary">
        <slot />
    </BaseButton>
</template>

<script setup lang="ts">
import BaseButton from './BaseButton.vue';
</script>
