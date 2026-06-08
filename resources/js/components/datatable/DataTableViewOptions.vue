<script setup lang="ts" generic="TData">
import type { Column, Table } from '@tanstack/vue-table';
import { Columns3, MoreHorizontal } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTrans } from '@/composables/useTrans';

const { trans } = useTrans();

const props = defineProps<{
    table: Table<TData>;
}>();

const columns = computed(() =>
    props.table
        .getAllColumns()
        .filter(
            (column) =>
                typeof column.accessorFn !== 'undefined' && column.getCanHide(),
        ),
);

function getColumnVisibilityLabel(column: Column<TData, unknown>): string {
    const meta = column.columnDef.meta as { label?: string } | undefined;

    if (meta?.label) {
        return meta.label;
    }

    if (typeof column.columnDef.header === 'string') {
        return column.columnDef.header;
    }

    return column.id.replaceAll('_', ' ');
}
</script>

<template>
    <DropdownMenu v-if="columns.length > 0">
        <DropdownMenuTrigger as-child>
            <Button
                variant="outline"
                size="sm"
                class="size-9 shrink-0 px-0 sm:h-8 sm:w-auto sm:px-3"
                :aria-label="trans('datatable.label.columns')"
            >
                <MoreHorizontal class="size-4 sm:hidden" />
                <Columns3 class="hidden size-4 sm:block" />
                <span class="sr-only sm:not-sr-only">
                    {{ trans('datatable.label.columns') }}
                </span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-48">
            <DropdownMenuCheckboxItem
                v-for="column in columns"
                :key="column.id"
                :model-value="column.getIsVisible()"
                class="capitalize"
                @update:model-value="
                    (value) => column.toggleVisibility(!!value)
                "
            >
                {{ getColumnVisibilityLabel(column) }}
            </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
