<template>
    <div class="w-full">
        <label v-if="label" :for="id" class="label-base">
            {{ label }}
            <span v-if="required" class="text-danger-500 ml-1">*</span>
        </label>

        <div class="relative">
            <div
                v-if="iconLeft"
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <component :is="iconLeft" class="w-5 h-5 text-gray-400" />
            </div>

            <input
                :id="id"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :class="[
                    'input-base',
                    {
                        'pl-10': iconLeft,
                        'pr-10': iconRight || clearable,
                        'border-danger-500 focus:ring-danger-500': error,
                    },
                ]"
                @input="handleInput"
                @blur="handleBlur" />

            <div
                v-if="iconRight"
                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <component :is="iconRight" class="w-5 h-5 text-gray-400" />
            </div>

            <button
                v-if="clearable && modelValue"
                type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                @click="handleClear">
                <ClearIcon class="w-5 h-5 text-gray-400 hover:text-gray-600" />
            </button>
        </div>

        <p v-if="helpText" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ helpText }}
        </p>

        <p v-if="error" class="mt-1 text-sm text-danger-600 dark:text-danger-400">
            {{ error }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import ClearIcon from '@icons/ClearIcon.vue';

interface Props {
    id?: string;
    type?: string;
    label?: string;
    placeholder?: string;
    modelValue?: string | number;
    disabled?: boolean;
    required?: boolean;
    helpText?: string;
    error?: string;
    clearable?: boolean;
    iconLeft?: any;
    iconRight?: any;
}

const props = withDefaults(defineProps<Props>(), {
    id: undefined,
    type: 'text',
    label: '',
    placeholder: '',
    modelValue: '',
    disabled: false,
    required: false,
    helpText: '',
    error: '',
    clearable: false,
    iconLeft: undefined,
    iconRight: undefined,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
    blur: [event: FocusEvent];
    clear: [];
}>();

const inputId = ref<string>(props.id || '');

onMounted(() => {
    if (!inputId.value) {
        inputId.value = `input-${Math.random().toString(36).substr(2, 9)}`;
    }
});

const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement;

    emit('update:modelValue', target.value);
};

const handleBlur = (event: FocusEvent) => {
    emit('blur', event);
};

const handleClear = () => {
    emit('update:modelValue', '');
    emit('clear');
};
</script>
