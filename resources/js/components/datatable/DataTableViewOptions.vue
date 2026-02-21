<script setup lang="ts" generic="TData">
import type { Column, Table } from '@tanstack/vue-table';
import { trans } from 'laravel-vue-i18n';
import { Settings2 } from 'lucide-vue-next';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface DataTableViewOptionsProps {
    table: Table<TData>;
}

const props = defineProps<DataTableViewOptionsProps>();

const columns = computed(() =>
    props.table
        .getAllColumns()
        .filter(
            (column) =>
                typeof column.accessorFn !== 'undefined' && column.getCanHide(),
        ),
);

function getColumnLabel(col: Column<TData, any>) {
    return trans((col.columnDef.meta as any)?.label ?? col.id);
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="outline"
                size="sm"
                class="ml-auto hidden h-8 lg:flex"
            >
                <Settings2 class="mr-2 h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>{{
                trans('ui.datatable.columns.label')
            }}</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuCheckboxItem
                v-for="column in columns"
                :key="column.id"
                :model-value="column.getIsVisible()"
                @update:model-value="
                    (value) => column.toggleVisibility(!!value)
                "
            >
                {{ getColumnLabel(column) }}
            </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
