<template>
    <BaseInput v-bind="$attrs" type="password">
        <slot />
    </BaseInput>
</template>

<script setup lang="ts">
import BaseInput from './BaseInput.vue';
</script>
