<script setup lang="ts" generic="TData">
import { type Table } from '@tanstack/vue-table';
import { trans } from 'laravel-vue-i18n';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface DataTablePaginationProps<TData> {
    table: Table<TData>;
}

defineProps<DataTablePaginationProps<TData>>();
</script>

<template>
    <div class="flex items-center justify-between px-2 py-4">
        <div class="flex w-full items-center justify-between">
            <div class="text-sm text-muted-foreground">
                {{
                    trans('ui.datatable.pagination.selected_of_total', {
                        selected: String(
                            table.getFilteredSelectedRowModel().rows.length,
                        ),
                        total: String(table.getFilteredRowModel().rows.length),
                    })
                }}
            </div>
            <div class="hidden items-center space-x-2 md:flex">
                <p class="text-sm font-medium">
                    {{ trans('ui.datatable.pagination.rows_per_page') }}
                </p>
                <Select
                    :model-value="`${table.getState().pagination.pageSize}`"
                    @update:model-value="
                        (value) => table.setPageSize(Number(value))
                    "
                >
                    <SelectTrigger>
                        <SelectValue
                            :placeholder="`${table.getState().pagination.pageSize}`"
                        />
                    </SelectTrigger>
                    <SelectContent side="top">
                        <SelectItem
                            v-for="pageSize in [10, 25, 50, 100]"
                            :key="pageSize"
                            :value="`${pageSize}`"
                        >
                            {{ pageSize }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div
                class="hidden items-center justify-center text-sm font-medium md:flex"
            >
                {{
                    trans('ui.datatable.pagination.page_of_total', {
                        current: String(
                            table.getState().pagination.pageIndex + 1,
                        ),
                        total: String(table.getPageCount()),
                    })
                }}
            </div>
            <div class="flex items-center space-x-2">
                <Button
                    variant="outline"
                    class="hidden h-8 w-8 p-0 lg:flex"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.setPageIndex(0)"
                >
                    <span class="sr-only">
                        {{ trans('ui.datatable.pagination.go_to_first_page') }}
                    </span>
                    <ChevronsLeft class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    class="h-8 w-8 p-0"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.previousPage()"
                >
                    <span class="sr-only">
                        {{
                            trans('ui.datatable.pagination.go_to_previous_page')
                        }}
                    </span>
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    class="h-8 w-8 p-0"
                    :disabled="!table.getCanNextPage()"
                    @click="table.nextPage()"
                >
                    <span class="sr-only">
                        {{ trans('ui.datatable.pagination.go_to_next_page') }}
                    </span>
                    <ChevronRight class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    class="hidden h-8 w-8 p-0 lg:flex"
                    :disabled="!table.getCanNextPage()"
                    @click="table.setPageIndex(table.getPageCount() - 1)"
                >
                    <span class="sr-only">
                        {{ trans('ui.datatable.pagination.go_to_last_page') }}
                    </span>
                    <ChevronsRight class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>