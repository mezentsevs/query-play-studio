<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'btn-base',
            sizeClasses,
            variantClasses,
            { 'opacity-50 cursor-not-allowed': disabled || loading },
        ]"
        @click="handleClick">
        <span v-if="loading" class="flex items-center">
            <LoadingSpinnerIcon class="w-4 h-4 mr-2" />
            {{ loadingText }}
        </span>
        <span v-else class="flex items-center">
            <component :is="iconLeft" v-if="iconLeft" class="w-4 h-4 mr-2" />
            <slot />
            <component :is="iconRight" v-if="iconRight" class="w-4 h-4 ml-2" />
        </span>
    </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import LoadingSpinnerIcon from '@icons/LoadingSpinnerIcon.vue';

interface Props {
    type?: 'button' | 'submit' | 'reset';
    variant?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger' | 'ghost';
    size?: 'xs' | 'sm' | 'md' | 'lg';
    disabled?: boolean;
    loading?: boolean;
    loadingText?: string;
    iconLeft?: any;
    iconRight?: any;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'button',
    variant: 'primary',
    size: 'md',
    disabled: false,
    loading: false,
    loadingText: 'Loading...',
    iconLeft: undefined,
    iconRight: undefined,
});

const emit = defineEmits<{
    click: [event: MouseEvent];
}>();

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'xs':
            return 'px-2 py-1 text-xs';
        case 'sm':
            return 'px-3 py-1.5 text-sm';
        case 'md':
            return 'px-4 py-2 text-sm';
        case 'lg':
            return 'px-6 py-3 text-base';
        default:
            return 'px-4 py-2 text-sm';
    }
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500';
        case 'secondary':
            return 'bg-secondary-600 text-white hover:bg-secondary-700 focus:ring-secondary-500';
        case 'success':
            return 'bg-success-600 text-white hover:bg-success-700 focus:ring-success-500';
        case 'warning':
            return 'bg-warning-600 text-white hover:bg-warning-700 focus:ring-warning-500';
        case 'danger':
            return 'bg-danger-600 text-white hover:bg-danger-700 focus:ring-danger-500';
        case 'ghost':
            return 'bg-transparent text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:ring-gray-500 border border-gray-300 dark:border-gray-600';
        default:
            return 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500';
    }
});

const handleClick = (event: MouseEvent) => {
    if (!props.disabled && !props.loading) {
        emit('click', event);
    }
};
</script>
