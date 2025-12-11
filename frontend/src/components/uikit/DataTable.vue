<template>
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ column.label }}
                    </th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                <tr
                    v-for="(row, rowIndex) in data"
                    :key="rowIndex"
                    class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                    <td
                        v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        <slot
                            :name="`cell-${column.key}`"
                            :value="row[column.key]"
                            :row="row"
                            :index="rowIndex">
                            <span class="font-mono">
                                {{ formatValue(row[column.key]) }}
                            </span>
                        </slot>
                    </td>
                </tr>

                <tr v-if="data.length === 0">
                    <td
                        :colspan="columns.length"
                        class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center">
                            <div class="text-4xl mb-2">📊</div>
                            <p class="text-lg font-medium mb-1">No data available</p>
                            <p class="text-sm">Try executing a query to see results</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup lang="ts">
interface Column {
    key: string;
    label: string;
    width?: string;
}

interface Props {
    columns: Column[];
    data: any[];
}

const props = defineProps<Props>();

const formatValue = (value: any): string => {
    if (value === null || value === undefined) {
        return 'NULL';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    return String(value);
};
</script>
