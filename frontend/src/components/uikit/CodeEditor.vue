<template>
    <div class="relative w-full">
        <div class="flex items-center justify-between mb-2">
            <label v-if="label" class="label-base">
                {{ label }}
            </label>

            <div class="flex items-center space-x-2">
                <SecondaryButton v-if="copyable" size="xs" @click="handleCopy">
                    {{ copyButtonText }}
                </SecondaryButton>

                <SecondaryButton v-if="clearable" size="xs" @click="handleClear">
                    Clear
                </SecondaryButton>
            </div>
        </div>

        <div class="relative">
            <textarea
                ref="textareaRef"
                :value="modelValue"
                :placeholder="placeholder"
                :rows="rows"
                :disabled="disabled"
                :class="[
                    'w-full font-mono text-sm bg-gray-900 text-gray-100 rounded-lg p-4',
                    'border border-gray-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500 focus:outline-none',
                    'resize-y transition-colors duration-200',
                    { 'opacity-50 cursor-not-allowed': disabled },
                ]"
                @input="handleInput"
                @keydown="handleKeydown" />

            <div
                v-if="language"
                class="absolute top-2 right-2 px-2 py-1 bg-gray-800 text-gray-300 text-xs rounded">
                {{ language.toUpperCase() }}
            </div>
        </div>

        <p v-if="error" class="mt-1 text-sm text-danger-600 dark:text-danger-400">
            {{ error }}
        </p>

        <p v-if="helpText" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ helpText }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { ref, nextTick } from 'vue';
import SecondaryButton from './buttons/SecondaryButton.vue';
import { useNotificationStore } from '@/stores/notifications';

const notificationStore = useNotificationStore();

interface Props {
    label?: string;
    placeholder?: string;
    modelValue?: string;
    language?: string;
    rows?: number;
    disabled?: boolean;
    error?: string;
    helpText?: string;
    copyable?: boolean;
    clearable?: boolean;
    autofocus?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    label: '',
    placeholder: 'Enter your code here...',
    modelValue: '',
    language: 'sql',
    rows: 8,
    disabled: false,
    error: '',
    helpText: '',
    copyable: false,
    clearable: false,
    autofocus: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
    clear: [];
    keydown: [event: KeyboardEvent];
}>();

const textareaRef = ref<HTMLTextAreaElement>();
const copyButtonText = ref('Copy');
let copyTimeout: number | undefined;

const handleInput = (event: Event) => {
    const target = event.target as HTMLTextAreaElement;
    emit('update:modelValue', target.value);
};

const handleClear = () => {
    emit('update:modelValue', '');
    emit('clear');
};

const handleCopy = async () => {
    try {
        await navigator.clipboard.writeText(props.modelValue || '');
        copyButtonText.value = 'Copied!';
        notificationStore.addNotification('success', 'Code copied to clipboard', 'Copied');

        if (copyTimeout) {
            clearTimeout(copyTimeout);
        }

        copyTimeout = window.setTimeout(() => {
            copyButtonText.value = 'Copy';
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
        copyButtonText.value = 'Failed';
        notificationStore.addNotification(
            'error',
            'Failed to copy code to clipboard',
            'Copy Failed',
        );
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    emit('keydown', event);
};

const focus = () => {
    if (textareaRef.value) {
        textareaRef.value.focus();
    }
};

defineExpose({
    focus,
});

if (props.autofocus) {
    nextTick(() => {
        focus();
    });
}
</script>
