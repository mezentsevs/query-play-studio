<template>
    <div class="min-h-screen flex flex-col">
        <AppHeader />

        <main class="flex-grow container-custom py-8">
            <div v-if="loading" class="text-center py-12">
                <LoadingSpinnerIcon class="w-12 h-12 mx-auto text-primary-600" />
                <p class="mt-4 text-gray-600 dark:text-gray-400">Loading exercise...</p>
            </div>

            <div
                v-else-if="error"
                class="p-6 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                <p class="text-danger-800 dark:text-danger-300">{{ error }}</p>
                <PrimaryButton class="mt-4" @click="$router.push('/exercises')">
                    Back to Exercises
                </PrimaryButton>
            </div>

            <div v-else class="space-y-6">
                <!-- Header -->
                <div class="flex items-start justify-between">
                    <div>
                        <router-link
                            to="/exercises"
                            class="inline-flex items-center text-sm text-primary-600 dark:text-primary-400 hover:underline mb-4">
                            ← Back to Exercises
                        </router-link>

                        <div class="flex items-center space-x-3 mb-2">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ exercise.title }}
                            </h1>

                            <span
                                class="px-3 py-1 text-sm font-medium rounded-full"
                                :class="getDifficultyBadgeClass(exercise.difficulty)">
                                {{ getDifficultyLabel(exercise.difficulty) }}
                            </span>

                            <span
                                class="px-3 py-1 text-sm font-medium rounded-full"
                                :class="getDatabaseBadgeClass(exercise.database_type)">
                                {{ exercise.database_type.toUpperCase() }}
                            </span>
                        </div>

                        <p class="text-gray-600 dark:text-gray-400">
                            {{ exercise.description }}
                        </p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <PrimaryButton
                            v-if="!progress || progress.status !== 'completed'"
                            @click="showAiHelp">
                            <AiIcon class="w-4 h-4 mr-2" />
                            AI Help
                        </PrimaryButton>

                        <SecondaryButton v-if="progress" @click="resetProgress">
                            Reset Progress
                        </SecondaryButton>
                    </div>
                </div>

                <!-- Progress Indicator -->
                <div v-if="progress" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-900 dark:text-white">
                            Status: {{ progress.status.replace('_', ' ').toUpperCase() }}
                        </span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Attempts: {{ progress.attempts || 0 }}
                            <span v-if="progress.score">• Score: {{ progress.score }}%</span>
                        </span>
                    </div>

                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div
                            class="h-2 rounded-full transition-all duration-300"
                            :class="
                                progress.status === 'completed'
                                    ? 'bg-success-600'
                                    : 'bg-primary-600'
                            "
                            :style="{ width: progress.status === 'completed' ? '100%' : '50%' }" />
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Instructions and Schema -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Instructions -->
                        <div class="card">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                Instructions
                            </h2>

                            <div class="prose dark:prose-invert max-w-none">
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                    {{ exercise.instructions }}
                                </p>
                            </div>
                        </div>

                        <!-- Initial Schema -->
                        <div class="card">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                    Initial Database Schema
                                </h2>

                                <SecondaryButton size="sm" @click="copySchema">
                                    Copy Schema
                                </SecondaryButton>
                            </div>

                            <CodeEditor
                                :model-value="exercise.initial_schema"
                                language="sql"
                                :rows="10"
                                :disabled="true"
                                :copyable="true" />
                        </div>
                    </div>

                    <!-- Right Column: Query Editor and Results -->
                    <div class="space-y-6">
                        <!-- Query Editor -->
                        <div class="card">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                Your Solution
                            </h2>

                            <div class="mb-4">
                                <label class="label-base">Write your SQL query below:</label>
                                <CodeEditor
                                    v-model="userQuery"
                                    language="sql"
                                    :rows="12"
                                    :copyable="true"
                                    :clearable="true"
                                    placeholder="SELECT * FROM table;"
                                    @clear="clearQuery"
                                    @keydown.ctrl.enter="submitSolution" />
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Press
                                    <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded">
                                        Ctrl+Enter
                                    </kbd>
                                    to submit
                                </div>

                                <PrimaryButton
                                    :loading="isSubmitting"
                                    :disabled="!userQuery.trim()"
                                    @click="submitSolution">
                                    Submit Solution
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Previous Attempts -->
                        <div v-if="progress && (progress.attempts || 0) > 0" class="card">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                Previous Attempts: {{ progress.attempts }}
                            </h2>

                            <div
                                v-if="progress.user_query"
                                class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <p
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Last attempt:
                                </p>
                                <pre
                                    class="text-sm font-mono text-gray-900 dark:text-gray-100 overflow-x-auto"
                                    >{{ progress.user_query }}</pre
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Section -->
                <div v-if="lastResult" class="card">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        Results
                        <span
                            class="ml-2 px-3 py-1 text-sm rounded-full"
                            :class="
                                lastResult.success
                                    ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300'
                                    : 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-300'
                            ">
                            {{ lastResult.success ? 'CORRECT' : 'INCORRECT' }}
                        </span>
                    </h2>

                    <div v-if="!lastResult.success" class="space-y-4">
                        <div
                            class="p-4 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                            <p class="text-danger-800 dark:text-danger-300">
                                Your solution doesn't match the expected result. Try again!
                            </p>

                            <PrimaryButton
                                class="mt-4"
                                variant="danger"
                                size="sm"
                                @click="showAiHelpWithError">
                                <AiIcon class="w-4 h-4 mr-2" />
                                Get AI Help with This Error
                            </PrimaryButton>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white mb-2">
                                    Your Result
                                </h3>

                                <div
                                    class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 max-h-64 overflow-y-auto">
                                    <DataTable
                                        v-if="
                                            lastResult.query_result.data &&
                                            lastResult.query_result.data.length > 0
                                        "
                                        :columns="resultColumns(lastResult.query_result.data[0])"
                                        :data="lastResult.query_result.data" />
                                    <div
                                        v-else
                                        class="text-center py-8 text-gray-500 dark:text-gray-400">
                                        <p>No data returned</p>
                                        <p
                                            v-if="lastResult.query_result.affected_rows"
                                            class="text-sm mt-1">
                                            {{ lastResult.query_result.affected_rows }} rows
                                            affected
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white mb-2">
                                    Expected Result
                                </h3>

                                <div
                                    class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 max-h-64 overflow-y-auto">
                                    <DataTable
                                        v-if="
                                            lastResult.expected_result &&
                                            lastResult.expected_result.length > 0
                                        "
                                        :columns="resultColumns(lastResult.expected_result[0])"
                                        :data="lastResult.expected_result" />
                                    <div
                                        v-else
                                        class="text-center py-8 text-gray-500 dark:text-gray-400">
                                        No data expected
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="p-6 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-12 h-12 rounded-full bg-success-100 dark:bg-success-900 flex items-center justify-center">
                                    <span class="text-2xl">🎉</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3
                                    class="text-lg font-medium text-success-800 dark:text-success-300">
                                    Congratulations!
                                </h3>

                                <p class="text-success-700 dark:text-success-400">
                                    Your solution is correct. You've completed this exercise!
                                </p>

                                <div class="mt-2 text-sm text-success-600 dark:text-success-500">
                                    <span v-if="progress">
                                        Score: {{ progress.score || 0 }}% • Attempts:
                                        {{ progress.attempts || 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex space-x-3">
                            <PrimaryButton @click="$router.push('/exercises')">
                                View All Exercises
                            </PrimaryButton>

                            <SecondaryButton @click="resetProgress">Try Again</SecondaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />

        <AiAssistantModal
            :is-open="showAiModal"
            :context-type="aiContextType"
            :exercise-id="exercise.id"
            :database-type="exercise.database_type"
            :sql-query="userQuery"
            :error-message="lastResult?.query_result?.error"
            :initial-question="aiInitialQuestion"
            @close="showAiModal = false"
            @response="handleAiResponse" />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useNotificationStore } from '@/stores/notifications';
import { useRoute } from 'vue-router';
import AiAssistantModal from '@/components/AiAssistantModal.vue';
import AiIcon from '@icons/AiIcon.vue';
import api from '@/services/api';
import AppFooter from '@/components/layout/AppFooter.vue';
import AppHeader from '@/components/layout/AppHeader.vue';
import CodeEditor from '@/components/uikit/CodeEditor.vue';
import DataTable from '@/components/uikit/DataTable.vue';
import LoadingSpinnerIcon from '@icons/LoadingSpinnerIcon.vue';
import PrimaryButton from '@components/uikit/buttons/PrimaryButton.vue';
import SecondaryButton from '@components/uikit/buttons/SecondaryButton.vue';
import type { ExerciseProgress } from '@/types';

const notificationStore = useNotificationStore();
const route = useRoute();

const exercise = ref<any>({});
const progress = ref<ExerciseProgress | null>(null);
const loading = ref(false);
const error = ref('');
const userQuery = ref('');
const isSubmitting = ref(false);
const lastResult = ref<any>(null);
const showAiModal = ref(false);
const aiContextType = ref<'exercise_help' | 'sql_error'>('exercise_help');
const aiInitialQuestion = ref('');

const getDifficultyBadgeClass = (difficulty: number) => {
    switch (difficulty) {
        case 1:
            return 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300';
        case 2:
            return 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-300';
        case 3:
            return 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const getDifficultyLabel = (difficulty: number) => {
    switch (difficulty) {
        case 1:
            return 'Beginner';
        case 2:
            return 'Intermediate';
        case 3:
            return 'Advanced';
        default:
            return 'Unknown';
    }
};

const getDatabaseBadgeClass = (dbType: string) => {
    switch (dbType) {
        case 'mysql':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'postgresql':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 'sqlite':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const resultColumns = (firstRow: any) => {
    if (!firstRow) return [];

    return Object.keys(firstRow).map(key => ({
        key,
        label: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
    }));
};

const loadExercise = async () => {
    const exerciseId = parseInt(route.params.id as string);

    if (!exerciseId) {
        error.value = 'Invalid exercise ID';

        return;
    }

    loading.value = true;
    error.value = '';

    try {
        // Load exercise details
        const exerciseResponse = await api.get(`/exercises/${exerciseId}`);

        if (exerciseResponse.status === 'success') {
            exercise.value = exerciseResponse.data;
        } else {
            error.value = 'Exercise not found';

            return;
        }

        // Load user progress
        const progressResponse = await api.get('/exercises/progress');

        if (progressResponse.status === 'success') {
            const exercisesWithProgress = progressResponse.data;
            const currentProgress = exercisesWithProgress.find(
                (item: any) => item.exercise.id === exerciseId,
            );

            if (currentProgress) {
                progress.value = currentProgress.progress;

                if (currentProgress.progress?.user_query) {
                    userQuery.value = currentProgress.progress.user_query;
                }
            }
        }

        // If no progress exists, start the exercise
        if (!progress.value) {
            await api.post(`/exercises/${exerciseId}/start`);

            const updatedProgressResponse = await api.get('/exercises/progress');

            if (updatedProgressResponse.status === 'success') {
                const exercisesWithProgress = updatedProgressResponse.data;
                const currentProgress = exercisesWithProgress.find(
                    (item: any) => item.exercise.id === exerciseId,
                );

                progress.value = currentProgress?.progress || null;
            }
        }
    } catch (err: any) {
        console.error('Failed to load exercise:', err);

        error.value = err.response?.data?.message || 'Failed to load exercise';
    } finally {
        loading.value = false;
    }
};

const submitSolution = async () => {
    if (!userQuery.value.trim()) {
        return;
    }

    isSubmitting.value = true;

    try {
        const response = await api.post(`/exercises/${exercise.value.id}/submit`, {
            query: userQuery.value,
        });

        if (response.status === 'success' || response.status === 'error') {
            lastResult.value = response.data;

            // Reload progress to get updated status
            const progressResponse = await api.get('/exercises/progress');

            if (progressResponse.status === 'success') {
                const exercisesWithProgress = progressResponse.data;
                const currentProgress = exercisesWithProgress.find(
                    (item: any) => item.exercise.id === exercise.value.id,
                );

                progress.value = currentProgress?.progress || null;
            }
        }
    } catch (err: any) {
        console.error('Failed to submit solution:', err);

        lastResult.value = {
            success: false,
            query_result: {
                error: err.response?.data?.message || 'Failed to submit solution',
            },
        };
    } finally {
        isSubmitting.value = false;
    }
};

const resetProgress = async () => {
    if (!confirm('Are you sure you want to reset your progress for this exercise?')) {
        return;
    }

    try {
        await api.post('/exercises/progress/reset', {
            confirm: true,
            exercise_id: exercise.value.id,
        });

        await loadExercise();

        userQuery.value = '';
        lastResult.value = null;
    } catch (err: any) {
        console.error('Failed to reset progress:', err);

        alert(err.response?.data?.message || 'Failed to reset progress');
    }
};

const clearQuery = () => {
    userQuery.value = '';
};

const copySchema = async () => {
    try {
        await navigator.clipboard.writeText(exercise.value.initial_schema);

        notificationStore.addNotification(
            'success',
            'Database schema copied to clipboard',
            'Copied',
        );
    } catch (err) {
        console.error('Failed to copy schema:', err);

        notificationStore.addNotification(
            'error',
            'Failed to copy schema to clipboard',
            'Copy Failed',
        );
    }
};

const showAiHelp = () => {
    aiContextType.value = 'exercise_help';
    aiInitialQuestion.value = `I need help with exercise "${exercise.value.title}". Can you give me a hint without giving away the solution?`;
    showAiModal.value = true;
};

const showAiHelpWithError = () => {
    if (lastResult.value?.query_result?.error) {
        aiContextType.value = 'sql_error';
        aiInitialQuestion.value = `I got this error: "${lastResult.value.query_result.error}" with query: "${userQuery.value}". Can you explain what's wrong?`;
    } else {
        aiContextType.value = 'exercise_help';
        aiInitialQuestion.value = `My query doesn't produce the expected result. Can you help me understand what's wrong?`;
    }

    showAiModal.value = true;
};

const handleAiResponse = (response: any) => {
    // We can update UI by AI response
    console.log('AI response:', response);
};

onMounted(() => {
    loadExercise();
});
</script>
