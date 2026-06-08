<script setup lang="ts" generic="TData, TValue">
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type {
    ColumnDef,
    PaginationState,
    RowSelectionState,
    SortingState,
    VisibilityState,
} from '@tanstack/vue-table';
import { computed, shallowRef, watch } from 'vue';
import DataTablePagination from '@/components/datatable/DataTablePagination.vue';
import DataTableToolbar from '@/components/datatable/DataTableToolbar.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { valueUpdater } from '@/components/ui/table/utils';
import {
    DEFAULT_DATA_TABLE_META,
    DEFAULT_DATA_TABLE_PER_PAGE_OPTIONS,
} from '@/composables/useDataTableState';
import { useTrans } from '@/composables/useTrans';
import type { DataTableFilterDefinition, DataTableMeta } from '@/types';

const { trans } = useTrans();

const props = withDefaults(
    defineProps<{
        columns: ColumnDef<TData, TValue>[];
        data: TData[];
        emptyMessage?: string;
        enableRowSelection?: boolean;
        filterDefinitions?: DataTableFilterDefinition[];
        filterValues?: Record<string, unknown>;
        getRowId?: (row: TData, index: number) => string;
        meta?: DataTableMeta;
        perPageOptions?: number[];
        sorting: SortingState;
    }>(),
    {
        enableRowSelection: true,
        filterDefinitions: () => [],
        filterValues: () => ({}),
        meta: () => DEFAULT_DATA_TABLE_META,
        perPageOptions: () => DEFAULT_DATA_TABLE_PER_PAGE_OPTIONS,
    },
);

const emit = defineEmits<{
    (e: 'filter-change', payload: { key: string; value: unknown }): void;
    (e: 'page-change', value: number): void;
    (e: 'per-page-change', value: number): void;
    (e: 'selection-change', value: TData[]): void;
    (e: 'sorting-change', value: SortingState): void;
}>();

const columnVisibility = shallowRef<VisibilityState>({});
const rowSelection = shallowRef<RowSelectionState>({});

const pagination = computed<PaginationState>(() => ({
    pageIndex: Math.max(props.meta.currentPage - 1, 0),
    pageSize: props.meta.perPage,
}));
const resolvedEmptyMessage = computed(
    () => props.emptyMessage ?? trans('datatable.message.empty'),
);

const table = useVueTable({
    get columns() {
        return props.columns;
    },
    get data() {
        return props.data;
    },
    enableRowSelection: props.enableRowSelection,
    getCoreRowModel: getCoreRowModel(),
    getRowId: (row, index) => props.getRowId?.(row, index) ?? String(index),
    manualPagination: true,
    manualSorting: true,
    get pageCount() {
        return props.meta.lastPage;
    },
    get rowCount() {
        return props.meta.total;
    },
    onColumnVisibilityChange: (updater) =>
        valueUpdater(updater, columnVisibility),
    onRowSelectionChange: (updater) => valueUpdater(updater, rowSelection),
    onSortingChange: (updater) => {
        emit(
            'sorting-change',
            updater instanceof Function ? updater(props.sorting) : updater,
        );
    },
    state: {
        get columnVisibility() {
            return columnVisibility.value;
        },
        get pagination() {
            return pagination.value;
        },
        get rowSelection() {
            return rowSelection.value;
        },
        get sorting() {
            return props.sorting;
        },
    },
});

const selectedRows = computed(() =>
    table.getSelectedRowModel().rows.map((row) => row.original),
);

watch(
    rowSelection,
    () => {
        emit(
            'selection-change',
            table.getSelectedRowModel().rows.map((row) => row.original),
        );
    },
    { deep: true },
);
watch(
    () => props.data,
    () => {
        rowSelection.value = {};
    },
);

function clearSelection(): void {
    rowSelection.value = {};
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col gap-4 rounded-xl border bg-card p-4">
            <DataTableToolbar
                :clear-selection="clearSelection"
                :filter-definitions="filterDefinitions"
                :filter-values="filterValues"
                :selected-rows="selectedRows"
                :table="table"
                @filter-change="(payload) => emit('filter-change', payload)"
            >
                <template #filters="{ selectedRows: rows }">
                    <slot name="filters" :selected-rows="rows" />
                </template>

                <template
                    #bulk-actions="{ clearSelection, selectedRows: rows }"
                >
                    <slot
                        name="bulk-actions"
                        :clear-selection="clearSelection"
                        :selected-rows="rows"
                    />
                </template>

                <template #actions="{ selectedRows: rows }">
                    <slot name="actions" :selected-rows="rows" />
                </template>
            </DataTableToolbar>

            <div class="overflow-x-auto rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow
                            v-for="headerGroup in table.getHeaderGroups()"
                            :key="headerGroup.id"
                            class="bg-muted/40"
                        >
                            <TableHead
                                v-for="header in headerGroup.headers"
                                :key="header.id"
                                class="text-muted-foreground"
                            >
                                <FlexRender
                                    v-if="!header.isPlaceholder"
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                            </TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <template v-if="table.getRowModel().rows.length > 0">
                            <TableRow
                                v-for="row in table.getRowModel().rows"
                                :key="row.id"
                                :data-state="
                                    row.getIsSelected() ? 'selected' : undefined
                                "
                            >
                                <TableCell
                                    v-for="cell in row.getVisibleCells()"
                                    :key="cell.id"
                                >
                                    <FlexRender
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </TableCell>
                            </TableRow>
                        </template>

                        <TableEmpty
                            v-else
                            :colspan="table.getVisibleLeafColumns().length"
                        >
                            {{ resolvedEmptyMessage }}
                        </TableEmpty>
                    </TableBody>
                </Table>
            </div>
        </div>

        <DataTablePagination
            :meta="meta"
            :per-page-options="perPageOptions"
            @page-change="(page) => emit('page-change', page)"
            @per-page-change="(value) => emit('per-page-change', value)"
        />
    </div>
</template>
