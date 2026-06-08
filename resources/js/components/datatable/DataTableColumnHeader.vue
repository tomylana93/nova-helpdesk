<script setup lang="ts">
import { ArrowDown, ArrowUp, ChevronsUpDown } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

type SortableDataTableColumn = {
    getCanSort: () => boolean;
    getIsSorted: () => false | 'asc' | 'desc';
    toggleSorting: (desc?: boolean) => void;
};

defineProps<{
    column: SortableDataTableColumn;
    title: string;
}>();
</script>

<template>
    <div class="flex items-center gap-1">
        <Button
            v-if="column.getCanSort()"
            variant="ghost"
            size="sm"
            class="-ml-3 h-8 px-3"
            @click="column.toggleSorting(column.getIsSorted() === 'asc')"
        >
            <span>{{ title }}</span>
            <ArrowDown v-if="column.getIsSorted() === 'desc'" class="size-4" />
            <ArrowUp
                v-else-if="column.getIsSorted() === 'asc'"
                class="size-4"
            />
            <ChevronsUpDown v-else class="size-4 text-muted-foreground" />
        </Button>

        <span v-else class="px-3 text-sm font-medium">{{ title }}</span>
    </div>
</template>
