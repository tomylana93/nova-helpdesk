<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { useTrans } from '@/composables/useTrans';
import type { DataTableFilterDefinition } from '@/types';
import DataTableSelectFilter from './DataTableSelectFilter.vue';

const props = defineProps<{
    definitions: DataTableFilterDefinition[];
    values: Record<string, unknown>;
}>();

const emit = defineEmits<{
    (e: 'filter-change', payload: { key: string; value: unknown }): void;
}>();

const searchDefinitions = computed(() =>
    props.definitions.filter(
        (
            item,
        ): item is Extract<DataTableFilterDefinition, { type: 'search' }> =>
            item.type === 'search',
    ),
);
const selectDefinitions = computed(() =>
    props.definitions.filter(
        (
            item,
        ): item is Extract<DataTableFilterDefinition, { type: 'select' }> =>
            item.type === 'select',
    ),
);
const { trans } = useTrans();
</script>

<template>
    <div class="grid min-w-0 gap-3">
        <Input
            v-for="definition in searchDefinitions"
            :key="definition.key"
            :model-value="String(values[definition.key] ?? '')"
            :placeholder="
                definition.placeholder ?? trans('datatable.placeholder.search')
            "
            class="w-full lg:max-w-md xl:max-w-sm"
            @update:model-value="
                (value) =>
                    emit('filter-change', {
                        key: definition.key,
                        value: String(value),
                    })
            "
        />

        <div class="grid gap-3 lg:flex lg:flex-row lg:flex-wrap">
            <DataTableSelectFilter
                v-for="definition in selectDefinitions"
                :key="definition.key"
                :model-value="
                    (values[definition.key] as string | null) ?? 'all'
                "
                :options="definition.options"
                :all-label="definition.allLabel"
                :placeholder="definition.placeholder ?? definition.label ?? ''"
                trigger-class="w-full lg:w-48 xl:w-56"
                @update:model-value="
                    (value) =>
                        emit('filter-change', {
                            key: definition.key,
                            value,
                        })
                "
            />
        </div>
    </div>
</template>
