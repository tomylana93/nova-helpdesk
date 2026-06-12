<script setup lang="ts" generic="TData">
import type { Table } from '@tanstack/vue-table';
import { computed } from 'vue';
import DataTableSelectFilter from '@/components/datatable/DataTableSelectFilter.vue';
import DataTableViewOptions from '@/components/datatable/DataTableViewOptions.vue';
import { Input } from '@/components/ui/input';
import { useTrans } from '@/composables/useTrans';
import type { DataTableFilterDefinition } from '@/types';

const props = defineProps<{
    clearSelection: () => void;
    filterDefinitions: DataTableFilterDefinition[];
    filterValues: Record<string, unknown>;
    selectedRows: TData[];
    table: Table<TData>;
}>();

const emit = defineEmits<{
    (e: 'filter-change', payload: { key: string; value: unknown }): void;
}>();

const { trans } = useTrans();

const searchDefinitions = computed(() =>
    props.filterDefinitions.filter(
        (
            item,
        ): item is Extract<DataTableFilterDefinition, { type: 'search' }> =>
            item.type === 'search',
    ),
);

const selectDefinitions = computed(() =>
    props.filterDefinitions.filter(
        (
            item,
        ): item is Extract<DataTableFilterDefinition, { type: 'select' }> =>
            item.type === 'select',
    ),
);
</script>

<template>
    <div class="space-y-4">
        <!-- Row 1: Search and Actions -->
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div class="w-full min-w-0 lg:max-w-md xl:max-w-sm">
                <Input
                    v-for="definition in searchDefinitions"
                    :key="definition.key"
                    :model-value="String(filterValues[definition.key] ?? '')"
                    :placeholder="
                        definition.placeholder ??
                        trans('datatable.placeholder.search')
                    "
                    class="w-full"
                    @update:model-value="
                        (value) =>
                            emit('filter-change', {
                                key: definition.key,
                                value: String(value),
                            })
                    "
                />
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

        <!-- Row 2: Select Filters -->
        <div
            v-if="selectDefinitions.length > 0 || $slots.filters"
            class="flex flex-wrap items-center gap-3"
        >
            <DataTableSelectFilter
                v-for="definition in selectDefinitions"
                :key="definition.key"
                :model-value="
                    (filterValues[definition.key] as string | null) ?? 'all'
                "
                :options="definition.options"
                :all-label="definition.allLabel"
                :placeholder="definition.placeholder ?? definition.label ?? ''"
                trigger-class="w-full sm:w-48 xl:w-56"
                @update:model-value="
                    (value) =>
                        emit('filter-change', {
                            key: definition.key,
                            value,
                        })
                "
            />
            <slot name="filters" :selected-rows="selectedRows" />
        </div>
    </div>
</template>
