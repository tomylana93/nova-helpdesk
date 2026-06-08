<script setup lang="ts">
import type { AcceptableValue } from 'reka-ui';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { SelectOption } from '@/types';

const props = withDefaults(
    defineProps<{
        allLabel?: string;
        allValue?: string;
        modelValue?: string | null;
        options: SelectOption[];
        placeholder: string;
        triggerClass?: string;
    }>(),
    {
        allLabel: 'All',
        allValue: 'all',
        modelValue: null,
        triggerClass: 'w-full lg:min-w-44',
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: AcceptableValue | AcceptableValue[]): void;
}>();

function handleUpdate(value: AcceptableValue | AcceptableValue[]): void {
    if (Array.isArray(value)) {
        emit('update:modelValue', value);

        return;
    }

    emit('update:modelValue', value === props.allValue ? null : value);
}
</script>

<template>
    <Select
        :model-value="props.modelValue ?? props.allValue"
        @update:model-value="handleUpdate"
    >
        <SelectTrigger :class="props.triggerClass">
            <SelectValue :placeholder="props.placeholder" />
        </SelectTrigger>

        <SelectContent>
            <SelectItem :value="props.allValue">{{
                props.allLabel
            }}</SelectItem>
            <SelectItem
                v-for="option in props.options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
