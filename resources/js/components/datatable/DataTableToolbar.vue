<script setup lang="ts" generic="TData">
import type { Table } from '@tanstack/vue-table';
import DataTableFilters from '@/components/datatable/DataTableFilters.vue';
import DataTableViewOptions from '@/components/datatable/DataTableViewOptions.vue';
import type { DataTableFilterDefinition } from '@/types';

defineProps<{
    clearSelection: () => void;
    filterDefinitions: DataTableFilterDefinition[];
    filterValues: Record<string, unknown>;
    selectedRows: TData[];
    table: Table<TData>;
}>();

const emit = defineEmits<{
    (e: 'filter-change', payload: { key: string; value: unknown }): void;
}>();
</script>

<template>
    <div
        class="grid gap-4 lg:grid-cols-[minmax(0,42rem)_auto] lg:items-start lg:justify-between xl:grid-cols-[minmax(0,46rem)_auto]"
    >
        <div class="min-w-0 space-y-3">
            <DataTableFilters
                :definitions="filterDefinitions"
                :values="filterValues"
                @filter-change="(payload) => emit('filter-change', payload)"
            />

            <div v-if="$slots.filters" class="flex min-w-0 flex-wrap gap-3">
                <slot name="filters" :selected-rows="selectedRows" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 lg:justify-end">
            <slot
                v-if="selectedRows.length > 0"
                name="bulk-actions"
                :clear-selection="clearSelection"
                :selected-rows="selectedRows"
            />

            <DataTableViewOptions :table="table" />

            <slot name="actions" :selected-rows="selectedRows" />
        </div>
    </div>
</template>
