<template>
    <TransitionRoot as="template" :show="isOpen">
        <Dialog as="div" class="fixed inset-0 z-50 overflow-y-auto" @close="handleClose">
            <div
                class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0">
                    <div
                        class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" />
                </TransitionChild>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">
                    &#8203;
                </span>

                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to="opacity-100 translate-y-0 sm:scale-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100 translate-y-0 sm:scale-100"
                    leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <div
                        class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                        :class="sizeClasses">
                        <div class="absolute top-0 right-0 pt-4 pr-4">
                            <button
                                type="button"
                                class="rounded-md text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                @click="handleClose">
                                <span class="sr-only">Close</span>
                                <ClearIcon class="w-6 h-6" />
                            </button>
                        </div>

                        <div v-if="title" class="mb-4">
                            <DialogTitle class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ title }}
                            </DialogTitle>
                        </div>

                        <div class="mt-2">
                            <slot />
                        </div>

                        <div v-if="showFooter" class="mt-6 flex justify-end space-x-3">
                            <SecondaryButton @click="handleClose">
                                {{ cancelText }}
                            </SecondaryButton>

                            <PrimaryButton
                                v-if="showConfirm"
                                :loading="confirmLoading"
                                :disabled="confirmDisabled"
                                @click="handleConfirm">
                                {{ confirmText }}
                            </PrimaryButton>
                        </div>
                    </div>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Dialog, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue';
import PrimaryButton from '../buttons/PrimaryButton.vue';
import SecondaryButton from '../buttons/SecondaryButton.vue';
import ClearIcon from '@icons/ClearIcon.vue';

interface Props {
    isOpen: boolean;
    title?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
    showFooter?: boolean;
    showConfirm?: boolean;
    cancelText?: string;
    confirmText?: string;
    confirmLoading?: boolean;
    confirmDisabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isOpen: false,
    title: '',
    size: 'md',
    showFooter: true,
    showConfirm: true,
    cancelText: 'Cancel',
    confirmText: 'Confirm',
    confirmLoading: false,
    confirmDisabled: false,
});

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'sm:max-w-sm';
        case 'md':
            return 'sm:max-w-md';
        case 'lg':
            return 'sm:max-w-lg';
        case 'xl':
            return 'sm:max-w-xl';
        default:
            return 'sm:max-w-md';
    }
});

const handleClose = () => {
    emit('close');
};

const handleConfirm = () => {
    emit('confirm');
};
</script>
