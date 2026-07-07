<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTrans } from '@/composables/useTrans';
import type { DataTableMeta } from '@/types';

const { trans } = useTrans();

const props = defineProps<{
    meta: DataTableMeta;
    perPageOptions: number[];
}>();

const emit = defineEmits<{
    (e: 'page-change', value: number): void;
    (e: 'per-page-change', value: number): void;
}>();

const canGoNext = computed(() => props.meta.currentPage < props.meta.lastPage);
const canGoPrevious = computed(() => props.meta.currentPage > 1);
</script>

<template>
    <div
        class="flex flex-col gap-4 border-t px-4 py-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <p class="text-sm text-muted-foreground">
            {{
                trans('datatable.helper.showing', {
                    from: meta.from ?? 0,
                    to: meta.to ?? 0,
                    total: meta.total,
                })
            }}
        </p>

        <div
            class="grid gap-3 sm:grid-cols-[auto_1fr] sm:items-center lg:flex lg:flex-wrap lg:items-center lg:justify-end"
        >
            <div
                class="flex items-center justify-between gap-2 sm:justify-start"
            >
                <span class="text-sm text-muted-foreground">{{
                    trans('datatable.label.rows')
                }}</span>

                <Select
                    :model-value="String(meta.perPage)"
                    @update:model-value="
                        (value) => emit('per-page-change', Number(value))
                    "
                >
                    <SelectTrigger class="w-24">
                        <SelectValue />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectItem
                            v-for="option in perPageOptions"
                            :key="option"
                            :value="String(option)"
                        >
                            {{ option }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div
                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end"
            >
                <span class="text-sm text-muted-foreground sm:text-right">
                    {{
                        trans('datatable.helper.pages', {
                            current: meta.currentPage,
                            last: meta.lastPage,
                        })
                    }}
                </span>

                <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                    <Button
                        variant="outline"
                        size="sm"
                        class="w-full sm:w-auto"
                        :disabled="!canGoPrevious"
                        @click="emit('page-change', meta.currentPage - 1)"
                    >
                        <ChevronLeft class="size-4" />
                        {{ trans('datatable.button.previous') }}
                    </Button>

                    <Button
                        variant="outline"
                        size="sm"
                        class="w-full sm:w-auto"
                        :disabled="!canGoNext"
                        @click="emit('page-change', meta.currentPage + 1)"
                    >
                        {{ trans('datatable.button.next') }}
                        <ChevronRight class="size-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
