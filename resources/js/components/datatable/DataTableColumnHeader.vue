<script setup lang="ts">
import type { Column } from '@tanstack/vue-table';
import { trans } from 'laravel-vue-i18n';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';

const props = defineProps<{
    column: Column<any, any>;
    title: string;
    align?: 'left' | 'center' | 'right';
}>();

const alignClass = {
    left: 'justify-start',
    center: 'justify-center',
    right: 'justify-end',
};

const translatedTitle = computed(() => trans(props.title));
</script>

<template>
    <div
        v-if="!column.getCanSort()"
        class="flex items-center"
        :class="alignClass[props.align || 'left']"
    >
        {{ translatedTitle }}
    </div>
    <div
        v-else
        :class="cn('flex items-center', alignClass[props.align || 'left'])"
    >
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button
                    variant="ghost"
                    size="sm"
                    class="-ml-3 h-8 data-[state=open]:bg-accent"
                >
                    <span>{{ translatedTitle }}</span>
                    <ArrowDown
                        v-if="column.getIsSorted() === 'desc'"
                        class="ml-2 h-4 w-4"
                    />
                    <ArrowUp
                        v-else-if="column.getIsSorted() === 'asc'"
                        class="ml-2 h-4 w-4"
                    />
                    <ArrowUpDown v-else class="ml-2 h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start">
                <DropdownMenuItem @click="column.toggleSorting(false)">
                    <ArrowUp
                        class="mr-2 h-3.5 w-3.5 text-muted-foreground/70"
                    />
                    {{ trans('ui.datatable.sort_ascending') }}
                </DropdownMenuItem>
                <DropdownMenuItem @click="column.toggleSorting(true)">
                    <ArrowDown
                        class="mr-2 h-3.5 w-3.5 text-muted-foreground/70"
                    />
                    {{ trans('ui.datatable.sort_descending') }}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>