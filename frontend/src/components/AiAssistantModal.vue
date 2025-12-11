<template>
    <BaseModal
        :is-open="isOpen"
        :title="title"
        size="xl"
        :show-footer="false"
        @close="$emit('close')">
        <div class="space-y-4">
            <!-- AI Status Indicator -->
            <div
                v-if="aiStatus.enabled"
                class="p-3 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg">
                <div class="flex items-center">
                    <AiIcon class="w-5 h-5 text-success-600 dark:text-success-400 mr-2" />
                    <span class="text-sm text-success-800 dark:text-success-300">
                        AI Assistant is connected using {{ aiStatus.provider }}
                    </span>
                </div>
            </div>

            <div
                v-else
                class="p-3 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg">
                <div class="flex items-center">
                    <AiIcon class="w-5 h-5 text-warning-600 dark:text-warning-400 mr-2" />
                    <span class="text-sm text-warning-800 dark:text-warning-300">
                        AI Assistant is disabled. Enable it in your .env configuration.
                    </span>
                </div>
            </div>

            <!-- Context Information -->
            <div v-if="context.sqlQuery" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SQL Query</h4>
                <pre
                    class="text-sm font-mono text-gray-900 dark:text-gray-100 bg-gray-900 p-3 rounded overflow-x-auto"
                    >{{ context.sqlQuery }}</pre
                >
            </div>

            <div
                v-if="context.errorMessage"
                class="bg-danger-50 dark:bg-danger-900/20 rounded-lg p-4">
                <h4 class="text-sm font-medium text-danger-700 dark:text-danger-300 mb-2">
                    Error Message
                </h4>
                <pre
                    class="text-sm font-mono text-danger-800 dark:text-danger-300 p-3 bg-danger-100 dark:bg-danger-900/30 rounded overflow-x-auto"
                    >{{ context.errorMessage }}</pre
                >
            </div>

            <!-- Question Input -->
            <div v-if="aiStatus.enabled">
                <div class="mb-4">
                    <label class="label-base">Your Question</label>
                    <textarea
                        v-model="question"
                        :placeholder="questionPlaceholder"
                        rows="3"
                        class="input-base"
                        :disabled="isLoading" />
                </div>

                <!-- AI Response -->
                <div v-if="response" class="mt-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        AI Response
                    </h4>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <div
                            class="prose dark:prose-invert max-w-none"
                            v-html="formattedResponse" />

                        <div
                            class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex items-center justify-between">
                                <span>Response from {{ aiStatus.provider }}</span>
                                <span>{{ responseTime }}ms</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="isLoading" class="text-center py-8">
                    <LoadingSpinnerIcon class="w-8 h-8 mx-auto text-primary-600" />
                    <p class="mt-2 text-gray-600 dark:text-gray-400">AI Assistant is thinking...</p>
                </div>

                <!-- Error State -->
                <div
                    v-if="error"
                    class="p-4 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                    <p class="text-danger-800 dark:text-danger-300">{{ error }}</p>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex flex-wrap gap-2">
                    <PrimaryButton
                        :loading="isLoading"
                        :disabled="!question.trim() || !aiStatus.enabled"
                        @click="handleAskQuestion">
                        <AiIcon class="w-4 h-4 mr-2" />
                        Ask AI Assistant
                    </PrimaryButton>

                    <SecondaryButton
                        v-if="contextType === 'sql_error'"
                        :disabled="!aiStatus.enabled"
                        @click="handleExplainError">
                        Explain This Error
                    </SecondaryButton>

                    <SecondaryButton
                        v-if="contextType === 'sql_optimization'"
                        :disabled="!aiStatus.enabled"
                        @click="handleOptimizeQuery">
                        Optimize This Query
                    </SecondaryButton>

                    <SecondaryButton v-if="response" @click="handleCopyResponse">
                        Copy Response
                    </SecondaryButton>
                </div>
            </div>
        </div>
    </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import aiService from '@/services/ai';
import BaseModal from './uikit/modals/BaseModal.vue';
import PrimaryButton from './uikit/buttons/PrimaryButton.vue';
import SecondaryButton from './uikit/buttons/SecondaryButton.vue';
import LoadingSpinnerIcon from '@icons/LoadingSpinnerIcon.vue';
import AiIcon from '@icons/AiIcon.vue';
import { DatabaseType } from '@/types/enums';
import { useNotificationStore } from '@/stores/notifications';

const notificationStore = useNotificationStore();

interface Props {
    isOpen: boolean;
    contextType?: 'sql_error' | 'sql_optimization' | 'exercise_help' | 'general';
    sqlQuery?: string;
    errorMessage?: string;
    databaseType?: DatabaseType;
    exerciseId?: number;
    initialQuestion?: string;
    schema?: string;
}

const props = withDefaults(defineProps<Props>(), {
    isOpen: false,
    contextType: 'general',
    sqlQuery: '',
    errorMessage: '',
    databaseType: DatabaseType.MYSQL,
    exerciseId: undefined,
    initialQuestion: '',
    schema: '',
});

const emit = defineEmits<{
    close: [];
    response: [response: any];
}>();

const aiStatus = ref({ enabled: false, provider: 'disabled' });
const question = ref(props.initialQuestion);
const response = ref<any>(null);
const responseTime = ref(0);
const isLoading = ref(false);
const error = ref('');
const formattedResponse = ref('');

const title = computed(() => {
    switch (props.contextType) {
        case 'sql_error':
            return 'AI SQL Error Explanation';
        case 'sql_optimization':
            return 'AI Query Optimization';
        case 'exercise_help':
            return 'AI Exercise Help';
        default:
            return 'AI Assistant';
    }
});

const questionPlaceholder = computed(() => {
    switch (props.contextType) {
        case 'sql_error':
            return 'Ask about this SQL error...';
        case 'sql_optimization':
            return 'Ask how to optimize this query...';
        case 'exercise_help':
            return 'Ask for help with this exercise...';
        default:
            return 'Ask the AI assistant anything about SQL or databases...';
    }
});

const context = computed(() => ({
    sqlQuery: props.sqlQuery,
    errorMessage: props.errorMessage,
    databaseType: props.databaseType,
    exerciseId: props.exerciseId,
    schema: props.schema,
}));

watch(
    () => response.value?.response,
    async newResponse => {
        if (!newResponse) {
            formattedResponse.value = '';
            return;
        }

        try {
            const markedHtml = await marked(newResponse);
            formattedResponse.value = DOMPurify.sanitize(markedHtml);
        } catch (err) {
            console.error('Failed to format AI response:', err);
            formattedResponse.value = newResponse;
        }
    },
    { immediate: true },
);

const checkAiStatus = async () => {
    const status = await aiService.getAiAssistantStatus();
    aiStatus.value = status;
};

const handleAskQuestion = async () => {
    if (!question.value.trim() || !aiStatus.value.enabled) return;

    isLoading.value = true;
    error.value = '';
    response.value = null;
    formattedResponse.value = '';

    try {
        const startTime = Date.now();

        const aiResponse = await aiService.askQuestion({
            question: question.value,
            context_type: props.contextType,
            exercise_id: props.exerciseId,
            database_type: props.databaseType,
            sql_query: props.sqlQuery,
            error_message: props.errorMessage,
        });

        responseTime.value = Date.now() - startTime;

        if (aiResponse.status === 'success') {
            response.value = aiResponse.data;
            emit('response', aiResponse.data);
        } else {
            error.value = aiResponse.message || 'Unknown error';
        }
    } catch (err: any) {
        console.error('Failed to get AI response:', err);
        error.value = err.response?.data?.message || 'Failed to get AI response';
    } finally {
        isLoading.value = false;
    }
};

const handleExplainError = async () => {
    if (!props.sqlQuery || !props.errorMessage || !props.databaseType) return;
    if (!aiStatus.value.enabled) return;

    isLoading.value = true;
    error.value = '';
    response.value = null;
    formattedResponse.value = '';

    try {
        const startTime = Date.now();

        const aiResponse = await aiService.explainError(
            props.sqlQuery,
            props.errorMessage,
            props.databaseType,
        );

        responseTime.value = Date.now() - startTime;

        if (aiResponse.status === 'success') {
            response.value = aiResponse.data;
            emit('response', aiResponse.data);
        } else {
            error.value = aiResponse.message || 'Unknown error';
        }
    } catch (err: any) {
        console.error('Failed to explain error:', err);
        error.value = err.response?.data?.message || 'Failed to explain error';
    } finally {
        isLoading.value = false;
    }
};

const handleOptimizeQuery = async () => {
    if (!props.sqlQuery || !props.databaseType) return;
    if (!aiStatus.value.enabled) return;

    isLoading.value = true;
    error.value = '';
    response.value = null;
    formattedResponse.value = '';

    try {
        const startTime = Date.now();

        const aiResponse = await aiService.optimizeQuery(
            props.sqlQuery,
            props.databaseType,
            props.schema,
        );

        responseTime.value = Date.now() - startTime;

        if (aiResponse.status === 'success') {
            response.value = aiResponse.data;
            emit('response', aiResponse.data);
        } else {
            error.value = aiResponse.message || 'Unknown error';
        }
    } catch (err: any) {
        console.error('Failed to optimize query:', err);
        error.value = err.response?.data?.message || 'Failed to optimize query';
    } finally {
        isLoading.value = false;
    }
};

const handleCopyResponse = async () => {
    if (!response.value?.response) return;

    try {
        await navigator.clipboard.writeText(response.value.response);
        notificationStore.addNotification('success', 'AI response copied to clipboard', 'Copied');
    } catch (err) {
        console.error('Failed to copy response:', err);
        notificationStore.addNotification(
            'error',
            'Failed to copy response to clipboard',
            'Copy Failed',
        );
    }
};

watch(
    () => props.isOpen,
    isOpen => {
        if (isOpen) {
            checkAiStatus();
            if (props.initialQuestion) {
                question.value = props.initialQuestion;
            }
        } else {
            // Reset state when modal closes
            response.value = null;
            formattedResponse.value = '';
            error.value = '';
            isLoading.value = false;
        }
    },
);

onMounted(() => {
    checkAiStatus();

    // If there's an initial question, ask it automatically
    if (props.initialQuestion && props.isOpen) {
        setTimeout(() => {
            handleAskQuestion();
        }, 500);
    }
});
</script>
