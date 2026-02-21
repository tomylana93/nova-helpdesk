<script setup lang="ts" generic="TData">
import type { Table } from '@tanstack/vue-table';
import { trans } from 'laravel-vue-i18n';
import { FileSpreadsheet, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import { DataTableViewOptions } from '@/components/datatable';
import type {
    DataTableFilterKey,
    DataTableFilterOption,
    DataTableFiltersConfig,
    DataTableFilterState,
} from '@/components/datatable/types';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';



const CLEAR_VALUE_PREFIX = '__clear__:';

interface DataTableToolbarProps {
    table: Table<TData>;
    filters?: DataTableFiltersConfig;
    filterState?: DataTableFilterState;
    canExport?: boolean;
}

const props = defineProps<DataTableToolbarProps>();

const emit = defineEmits<{
    export: [format: 'excel'];
    'update-filter': [key: DataTableFilterKey, value: string];
}>();

const handleExport = (): void => {
    emit('export', 'excel');
};

const hasFilters = computed(() => Boolean(props.filters && props.filterState));

const commandOpen = ref<Record<string, boolean>>({});

const optionLabel = (
    options: DataTableFilterOption[] | undefined,
    value: string,
): string | null => {
    if (!options || !value) {
        return null;
    }

    return options.find((o) => o.value === value)?.label ?? null;
};

const labelForFieldValue = (fieldKey: string): string | null => {
    if (!props.filters || !props.filterState) {
        return null;
    }

    const field = props.filters.fields.find((f) => f.key === fieldKey);
    if (!field) {
        return null;
    }

    return optionLabel(field.options, props.filterState[fieldKey] ?? '');
};

const updateFilter = (key: DataTableFilterKey, value: string) => {
    emit('update-filter', key, value);
};
</script>

<template>
    <div class="flex items-center justify-between">
        <div class="flex flex-1 flex-col gap-3">
            <Input
                v-if="
                    !table.getIsSomePageRowsSelected() &&
                    !table.getIsAllPageRowsSelected()
                "
                id="search"
                class="max-w-sm"
                :placeholder="trans('ui.datatable.search')"
                :model-value="table.getState().globalFilter"
                @update:model-value="table.setGlobalFilter"
            />
            <template v-else>
                <div class="flex items-center gap-2">
                    <p class="text-sm text-muted-foreground">
                        {{
                            trans('ui.datatable.selected', {
                                count: String(
                                    table.getFilteredSelectedRowModel().rows
                                        .length,
                                ),
                            })
                        }}
                    </p>
                    <Button
                        v-if="props.canExport"
                        variant="outline"
                        size="sm"
                        @click="handleExport"
                    >
                        <FileSpreadsheet class="mr-2 h-4 w-4" />
                        {{ trans('ui.datatable.export_excel') }}
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="table.resetRowSelection()"
                    >
                        <X class="mr-2 h-4 w-4" />
                        {{ trans('ui.datatable.clear_selection') }}
                    </Button>
                </div>
            </template>
        </div>
        <DataTableViewOptions :table="table" />
    </div>
    <div
        v-if="
            hasFilters &&
            !table.getIsSomePageRowsSelected() &&
            !table.getIsAllPageRowsSelected()
        "
        class="grid grid-cols-1 gap-3 md:grid-cols-6"
    >
        <template v-for="field in props.filters!.fields" :key="field.key">
            <Select
                v-if="field.kind === 'select'"
                :model-value="props.filterState![field.key] ?? ''"
                @update:model-value="
                    (v) => {
                        const value = String(v ?? '');

                        updateFilter(
                            field.key,
                            value === `${CLEAR_VALUE_PREFIX}${field.key}`
                                ? ''
                                : value,
                        );
                    }
                "
            >
                <SelectTrigger class="w-full">
                    <SelectValue :placeholder="field.allLabel" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="`${CLEAR_VALUE_PREFIX}${field.key}`">
                        {{ field.allLabel }}
                    </SelectItem>
                    <SelectItem
                        v-for="o in field.options"
                        :key="o.value"
                        :value="o.value"
                    >
                        {{ o.label }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Popover
                v-else
                :open="commandOpen[field.key] ?? false"
                @update:open="(v) => (commandOpen[field.key] = v)"
            >
                <PopoverTrigger as-child>
                    <Button
                        type="button"
                        variant="outline"
                        role="combobox"
                        class="w-full justify-between"
                    >
                        {{ labelForFieldValue(field.key) ?? field.allLabel }}
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="p-0" align="start">
                    <Command>
                        <CommandInput
                            :placeholder="
                                field.searchPlaceholder ??
                                field.placeholder ??
                                trans('ui.datatable.search')
                            "
                        />
                        <CommandList>
                            <CommandEmpty>{{
                                trans('ui.datatable.no_results')
                            }}</CommandEmpty>
                            <CommandGroup>
                                <CommandItem
                                    value=""
                                    @select="
                                        () => {
                                            updateFilter(field.key, '');
                                            commandOpen[field.key] = false;
                                        }
                                    "
                                >
                                    {{ field.allLabel }}
                                </CommandItem>
                                <CommandItem
                                    v-for="o in field.options"
                                    :key="o.value"
                                    :value="o.value"
                                    @select="
                                        () => {
                                            updateFilter(field.key, o.value);
                                            commandOpen[field.key] = false;
                                        }
                                    "
                                >
                                    {{ o.label }}
                                </CommandItem>
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
        </template>
    </div>
</template>