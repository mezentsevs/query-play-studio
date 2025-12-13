<template>
    <div class="min-h-screen flex flex-col">
        <AppHeader />

        <main class="flex-grow container-custom py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Exercises</h1>

                <p class="text-gray-600 dark:text-gray-400">
                    Practice SQL with structured exercises. Track your progress and improve your
                    skills.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Sidebar: Filters and Progress -->
                <div class="lg:col-span-1">
                    <div class="card sticky top-24">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            Filters
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label class="label-base">Database Type</label>
                                <select v-model="selectedDbType" class="input-base py-2">
                                    <option value="">All Databases</option>
                                    <option value="mysql">MySQL</option>
                                    <option value="postgresql">PostgreSQL</option>
                                    <option value="sqlite">SQLite</option>
                                </select>
                            </div>

                            <div>
                                <label class="label-base">Difficulty</label>
                                <select v-model="selectedDifficulty" class="input-base py-2">
                                    <option value="">All Difficulties</option>
                                    <option value="1">Beginner</option>
                                    <option value="2">Intermediate</option>
                                    <option value="3">Advanced</option>
                                </select>
                            </div>

                            <div>
                                <label class="label-base">Status</label>
                                <select v-model="selectedStatus" class="input-base py-2">
                                    <option value="">All Statuses</option>
                                    <option value="not_started">Not Started</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                                Your Progress
                            </h3>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Completed</span>
                                    <span class="font-semibold text-success-600">
                                        {{ completedCount }}/{{ filteredExercises.length }}
                                    </span>
                                </div>

                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div
                                        class="bg-success-600 h-2 rounded-full transition-all duration-300"
                                        :style="{ width: progressPercentage + '%' }" />
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        In Progress
                                    </span>
                                    <span class="font-semibold text-warning-600">
                                        {{ inProgressCount }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content: Exercises List -->
                <div class="lg:col-span-3">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ filteredExercises.length }} Exercise{{
                                    filteredExercises.length !== 1 ? 's' : ''
                                }}
                            </h2>

                            <p class="text-gray-600 dark:text-gray-400">
                                Showing {{ filteredExercises.length }} of
                                {{ exercises.length }} exercises
                            </p>
                        </div>

                        <PrimaryButton v-if="hasInProgress" @click="resetProgress">
                            Reset All Progress
                        </PrimaryButton>
                    </div>

                    <div v-if="loading" class="text-center py-12">
                        <LoadingSpinnerIcon class="w-12 h-12 mx-auto text-primary-600" />
                        <p class="mt-4 text-gray-600 dark:text-gray-400">Loading exercises...</p>
                    </div>

                    <div
                        v-else-if="error"
                        class="p-6 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                        <p class="text-danger-800 dark:text-danger-300">{{ error }}</p>
                    </div>

                    <div v-else-if="filteredExercises.length === 0" class="text-center py-12">
                        <ExerciseIcon
                            class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" />

                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            No exercises found
                        </h3>

                        <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            v-for="item in filteredExercises"
                            :key="item.exercise.id"
                            class="card card-hover cursor-pointer"
                            @click="$router.push(`/exercises/${item.exercise.id}`)">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3
                                        class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                                        {{ item.exercise.title }}
                                    </h3>

                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full"
                                            :class="
                                                getDatabaseBadgeClass(item.exercise.database_type)
                                            ">
                                            {{ item.exercise.database_type.toUpperCase() }}
                                        </span>

                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full"
                                            :class="
                                                getDifficultyBadgeClass(item.exercise.difficulty)
                                            ">
                                            {{ getDifficultyLabel(item.exercise.difficulty) }}
                                        </span>
                                    </div>
                                </div>

                                <div
                                    v-if="item.progress"
                                    class="px-3 py-1 text-xs font-medium rounded-full"
                                    :class="getStatusBadgeClass(item.progress.status)">
                                    {{ item.progress.status.replace('_', ' ').toUpperCase() }}
                                </div>
                            </div>

                            <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                                {{ item.exercise.description }}
                            </p>

                            <div
                                class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <span v-if="item.progress">
                                        {{ item.progress.attempts || 0 }} attempt{{
                                            item.progress.attempts !== 1 ? 's' : ''
                                        }}
                                        <span v-if="item.progress.score">
                                            • Score: {{ item.progress.score }}%
                                        </span>
                                    </span>
                                    <span v-else>Not started</span>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <SecondaryButton
                                        size="sm"
                                        @click.stop="
                                            $router.push(`/exercises/${item.exercise.id}`)
                                        ">
                                        {{ item.progress ? 'Continue' : 'Start' }}
                                    </SecondaryButton>

                                    <PrimaryButton
                                        v-if="item.progress && item.progress.status !== 'completed'"
                                        size="sm"
                                        @click.stop="resetExerciseProgress(item.exercise.id)">
                                        Reset
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';
import AppFooter from '@/components/layout/AppFooter.vue';
import AppHeader from '@/components/layout/AppHeader.vue';
import ExerciseIcon from '@icons/ExerciseIcon.vue';
import LoadingSpinnerIcon from '@icons/LoadingSpinnerIcon.vue';
import PrimaryButton from '@components/uikit/buttons/PrimaryButton.vue';
import SecondaryButton from '@components/uikit/buttons/SecondaryButton.vue';
import type { Exercise, ExerciseProgress } from '@/types';

interface ExerciseWithProgress {
    exercise: Exercise;
    progress: ExerciseProgress | null;
}

const exercises = ref<ExerciseWithProgress[]>([]);
const loading = ref(false);
const error = ref('');
const selectedDbType = ref('');
const selectedDifficulty = ref('');
const selectedStatus = ref('');

const filteredExercises = computed(() => {
    return exercises.value.filter(item => {
        // Filter by database type
        if (selectedDbType.value && item.exercise.database_type !== selectedDbType.value) {
            return false;
        }

        // Filter by difficulty
        if (
            selectedDifficulty.value &&
            item.exercise.difficulty !== parseInt(selectedDifficulty.value)
        ) {
            return false;
        }

        // Filter by status
        if (selectedStatus.value) {
            if (selectedStatus.value === 'not_started' && item.progress) {
                return false;
            }

            if (
                selectedStatus.value === 'in_progress' &&
                (!item.progress || item.progress.status !== 'in_progress')
            ) {
                return false;
            }

            if (
                selectedStatus.value === 'completed' &&
                (!item.progress || item.progress.status !== 'completed')
            ) {
                return false;
            }
        }

        return true;
    });
});

const completedCount = computed(() => {
    return filteredExercises.value.filter(
        item => item.progress && item.progress.status === 'completed',
    ).length;
});

const inProgressCount = computed(() => {
    return filteredExercises.value.filter(
        item => item.progress && item.progress.status === 'in_progress',
    ).length;
});

const progressPercentage = computed(() => {
    const total = filteredExercises.value.length;

    return total > 0 ? Math.round((completedCount.value / total) * 100) : 0;
});

const hasInProgress = computed(() => {
    return exercises.value.some(item => item.progress && item.progress.status !== 'completed');
});

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

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'completed':
            return 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300';
        case 'in_progress':
            return 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-300';
        case 'not_started':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const loadExercises = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await api.get('/exercises/progress');

        if (response.status === 'success') {
            exercises.value = response.data;
        }
    } catch (err: any) {
        console.error('Failed to load exercises:', err);

        error.value = err.response?.data?.message || 'Failed to load exercises';
    } finally {
        loading.value = false;
    }
};

const resetProgress = async () => {
    if (!confirm('Are you sure you want to reset all your progress? This cannot be undone.')) {
        return;
    }

    try {
        await api.post('/exercises/progress/reset', { confirm: true });

        await loadExercises();
    } catch (err: any) {
        console.error('Failed to reset progress:', err);

        alert(err.response?.data?.message || 'Failed to reset progress');
    }
};

const resetExerciseProgress = async (exerciseId: number) => {
    if (!confirm('Are you sure you want to reset progress for this exercise?')) {
        return;
    }

    try {
        await api.post('/exercises/progress/reset', {
            confirm: true,
            exercise_id: exerciseId,
        });

        await loadExercises();
    } catch (err: any) {
        console.error('Failed to reset exercise progress:', err);

        alert(err.response?.data?.message || 'Failed to reset exercise progress');
    }
};

onMounted(() => {
    loadExercises();
});
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
