<template>
    <div class="min-h-screen flex flex-col">
        <AppHeader />

        <main class="flex-grow container-custom py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    Database Sandbox
                </h1>

                <p class="text-gray-600 dark:text-gray-400">
                    Execute SQL queries in isolated environments. Your tables are prefixed with
                    <code class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded">
                        user_{{ userId }}_
                    </code>
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Database Selection and Query Input -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                Query Editor
                            </h2>

                            <div class="flex items-center space-x-2">
                                <select v-model="selectedDatabase" class="input-base py-2 pr-10">
                                    <option value="mysql">MySQL</option>
                                    <option value="postgresql">PostgreSQL</option>
                                    <option value="sqlite">SQLite</option>
                                </select>

                                <PrimaryButton
                                    :loading="isExecuting"
                                    :disabled="!query.trim()"
                                    :icon-left="PlayIcon"
                                    @click="executeQuery">
                                    Execute
                                </PrimaryButton>
                            </div>
                        </div>

                        <CodeEditor
                            v-model="query"
                            label="SQL Query"
                            language="sql"
                            :rows="12"
                            :copyable="true"
                            :clearable="true"
                            placeholder="SELECT * FROM users;"
                            @clear="clearQuery"
                            @keydown.ctrl.enter="executeQuery" />

                        <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            Press
                            <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded">
                                Ctrl+Enter
                            </kbd>
                            to execute
                        </div>
                    </div>

                    <!-- Query Result -->
                    <div v-if="lastResult" class="card">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            Result
                            <span
                                class="ml-2 px-3 py-1 text-xs rounded-full"
                                :class="
                                    lastResult.success
                                        ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300'
                                        : 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-300'
                                ">
                                {{ lastResult.success ? 'SUCCESS' : 'ERROR' }}
                                <span v-if="lastResult.execution_time_ms" class="ml-1">
                                    • {{ lastResult.execution_time_ms }}ms
                                </span>
                            </span>
                        </h2>

                        <div
                            v-if="!lastResult.success"
                            class="p-4 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                            <p class="text-danger-800 dark:text-danger-300 font-mono">
                                {{ lastResult.error }}
                            </p>

                            <PrimaryButton
                                v-if="lastResult.error"
                                class="mt-4"
                                variant="danger"
                                size="sm"
                                @click="explainError">
                                <AiIcon class="w-4 h-4 mr-2" />
                                Explain Error with AI
                            </PrimaryButton>
                        </div>

                        <div v-else>
                            <div
                                v-if="lastResult.data && lastResult.data.length > 0"
                                class="overflow-hidden">
                                <DataTable :columns="resultColumns" :data="lastResult.data" />

                                <div
                                    class="mt-4 text-sm text-gray-500 dark:text-gray-400 flex items-center justify-between">
                                    <span>
                                        {{ lastResult.data.length }} row{{
                                            lastResult.data.length !== 1 ? 's' : ''
                                        }}
                                        <span v-if="lastResult.affected_rows !== undefined">
                                            • {{ lastResult.affected_rows }} affected
                                        </span>
                                    </span>

                                    <PrimaryButton
                                        v-if="lastResult.data.length > 0"
                                        size="sm"
                                        variant="secondary"
                                        @click="optimizeQuery">
                                        <AiIcon class="w-4 h-4 mr-2" />
                                        Optimize with AI
                                    </PrimaryButton>
                                </div>
                            </div>

                            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <p class="text-lg">Query executed successfully</p>
                                <p v-if="lastResult.affected_rows !== undefined" class="mt-2">
                                    {{ lastResult.affected_rows }} row{{
                                        lastResult.affected_rows !== 1 ? 's' : ''
                                    }}
                                    affected
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Database Info and History -->
                <div class="space-y-6">
                    <!-- Database Structure -->
                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                Database Structure
                            </h2>

                            <SecondaryButton
                                size="sm"
                                :icon-left="StructureIcon"
                                @click="loadStructure">
                                Refresh
                            </SecondaryButton>
                        </div>

                        <div v-if="structureLoading" class="text-center py-8">
                            <LoadingSpinnerIcon class="w-8 h-8 mx-auto text-primary-600" />
                        </div>

                        <div
                            v-else-if="structureError"
                            class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                            <p class="text-warning-800 dark:text-warning-300">
                                {{ structureError }}
                            </p>
                        </div>

                        <div
                            v-else-if="Object.keys(structure).length === 0"
                            class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <StructureIcon class="w-12 h-12 mx-auto mb-2 opacity-50" />
                            <p>No tables found</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="(columns, tableName) in structure"
                                :key="tableName"
                                class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <div
                                    class="font-mono font-semibold text-sm text-gray-900 dark:text-white mb-2">
                                    {{ tableName }}
                                </div>

                                <div class="space-y-1">
                                    <div
                                        v-for="column in columns"
                                        :key="column.Field || column.column_name || column.name"
                                        class="flex items-center justify-between text-xs">
                                        <span class="font-mono text-gray-700 dark:text-gray-300">
                                            {{ column.Field || column.column_name || column.name }}
                                        </span>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ column.Type || column.data_type || column.type }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Query History -->
                    <div class="card">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                Recent Queries
                            </h2>

                            <SecondaryButton
                                size="sm"
                                :icon-left="HistoryIcon"
                                @click="loadHistory">
                                Refresh
                            </SecondaryButton>
                        </div>

                        <div v-if="historyLoading" class="text-center py-4">
                            <LoadingSpinnerIcon class="w-6 h-6 mx-auto text-primary-600" />
                        </div>

                        <div
                            v-else-if="historyError"
                            class="p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                            <p class="text-warning-800 dark:text-warning-300 text-sm">
                                {{ historyError }}
                            </p>
                        </div>

                        <div
                            v-else-if="history.length === 0"
                            class="text-center py-4 text-gray-500 dark:text-gray-400">
                            <p>No queries yet</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="log in history"
                                :key="log.id"
                                class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                                @click="loadQueryFromHistory(log)">
                                <div class="flex items-center justify-between mb-1">
                                    <span
                                        class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                        {{ log.database_type }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatTimeAgo(log.executed_at) }}
                                    </span>
                                </div>

                                <p class="text-sm font-mono text-gray-900 dark:text-white truncate">
                                    {{ log.query }}
                                </p>

                                <div class="flex items-center justify-between mt-2">
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full"
                                        :class="
                                            log.is_successful
                                                ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-300'
                                                : 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-300'
                                        ">
                                        {{ log.is_successful ? 'Success' : 'Error' }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ log.execution_time_ms }}ms
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />

        <AiAssistantModal
            :is-open="showAiModal"
            :context-type="aiContextType"
            :database-type="selectedDatabase"
            :sql-query="query"
            :error-message="lastResult?.error"
            :initial-question="aiInitialQuestion"
            @close="showAiModal = false"
            @response="handleAiResponse" />
    </div>
</template>

<script setup lang="ts">
import { DatabaseType } from '@/types/enums';
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import AiAssistantModal from '@/components/AiAssistantModal.vue';
import AiIcon from '@icons/AiIcon.vue';
import api from '@/services/api';
import AppFooter from '@/components/layout/AppFooter.vue';
import AppHeader from '@/components/layout/AppHeader.vue';
import CodeEditor from '@/components/uikit/CodeEditor.vue';
import DataTable from '@/components/uikit/DataTable.vue';
import HistoryIcon from '@icons/HistoryIcon.vue';
import LoadingSpinnerIcon from '@icons/LoadingSpinnerIcon.vue';
import PlayIcon from '@icons/PlayIcon.vue';
import PrimaryButton from '@components/uikit/buttons/PrimaryButton.vue';
import SecondaryButton from '@components/uikit/buttons/SecondaryButton.vue';
import StructureIcon from '@icons/StructureIcon.vue';
import type { SandboxQueryResult, SandboxStructure } from '@/types';

const authStore = useAuthStore();

const selectedDatabase = ref<DatabaseType>(DatabaseType.MYSQL);
const query = ref('SELECT 1;');
const isExecuting = ref(false);
const lastResult = ref<SandboxQueryResult | null>(null);
const structure = ref<SandboxStructure>({});
const structureLoading = ref(false);
const structureError = ref('');
const history = ref<any[]>([]);
const historyLoading = ref(false);
const historyError = ref('');
const showAiModal = ref(false);
const aiContextType = ref<'sql_error' | 'sql_optimization'>('sql_error');
const aiInitialQuestion = ref('');

const userId = computed(() => authStore.user?.id || 0);

const resultColumns = computed(() => {
    if (!lastResult.value?.data || lastResult.value.data.length === 0) {
        return [];
    }

    return Object.keys(lastResult.value.data[0]).map(key => ({
        key,
        label: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
    }));
});

const executeQuery = async () => {
    if (!query.value.trim()) {
        return;
    }

    isExecuting.value = true;

    try {
        const response = await api.post(`/sandbox/${selectedDatabase.value}/query`, {
            query: query.value,
        });

        if (response.status === 'success') {
            lastResult.value = response.data;
            await loadStructure();
            await loadHistory();
        }
    } catch (error: any) {
        console.error('Query execution failed:', error);

        lastResult.value = {
            success: false,
            error: error.response?.data?.message || 'Failed to execute query',
            warnings: [],
            execution_time_ms: 0,
        };
    } finally {
        isExecuting.value = false;
    }
};

const loadStructure = async () => {
    structureLoading.value = true;
    structureError.value = '';

    try {
        const response = await api.get(`/sandbox/${selectedDatabase.value}/structure`);

        if (response.status === 'success') {
            structure.value = response.data;
        }
    } catch (error: any) {
        console.error('Failed to load structure:', error);

        structureError.value = error.response?.data?.message || 'Failed to load database structure';
    } finally {
        structureLoading.value = false;
    }
};

const loadHistory = async () => {
    historyLoading.value = true;
    historyError.value = '';

    try {
        const response = await api.get('/sandbox/logs', {
            params: {
                database_type: selectedDatabase.value,
                limit: 5,
            },
        });

        if (response.status === 'success') {
            history.value = response.data.data;
        }
    } catch (error: any) {
        console.error('Failed to load history:', error);

        historyError.value = error.response?.data?.message || 'Failed to load query history';
    } finally {
        historyLoading.value = false;
    }
};

const clearQuery = () => {
    query.value = '';
    lastResult.value = null;
};

const loadQueryFromHistory = (log: any) => {
    query.value = log.query;
    selectedDatabase.value = log.database_type as DatabaseType;
};

const explainError = async () => {
    if (!lastResult.value?.error) {
        return;
    }

    aiContextType.value = 'sql_error';
    aiInitialQuestion.value = `I got this error: "${lastResult.value.error}" with query: "${query.value}". Can you explain what's wrong?`;
    showAiModal.value = true;
};

const optimizeQuery = async () => {
    if (!query.value.trim()) {
        return;
    }

    aiContextType.value = 'sql_optimization';
    aiInitialQuestion.value = `Can you optimize this query for better performance? Query: ${query.value}`;
    showAiModal.value = true;
};

const handleAiResponse = (response: any) => {
    if (response?.response) {
        console.log('AI response:', response);
    }
};

const formatTimeAgo = (timestamp: string) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) {
        return 'Just now';
    }

    if (diffMins < 60) {
        return `${diffMins}m ago`;
    }

    const diffHours = Math.floor(diffMins / 60);

    if (diffHours < 24) {
        return `${diffHours}h ago`;
    }

    const diffDays = Math.floor(diffHours / 24);

    return `${diffDays}d ago`;
};

onMounted(() => {
    loadStructure();
    loadHistory();
});
</script>
