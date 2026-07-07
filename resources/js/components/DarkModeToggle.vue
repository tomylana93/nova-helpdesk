<script setup lang="ts">
import { Monitor, Moon, Sun } from '@lucide/vue';
import type { AcceptableValue } from 'reka-ui';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useAppearance } from '@/composables/useAppearance';
import type { Appearance } from '@/types';

const { appearance, updateAppearance } = useAppearance();

const options: Array<{ value: Appearance; label: string; icon: typeof Sun }> = [
    { value: 'light', label: 'Light mode', icon: Sun },
    { value: 'dark', label: 'Dark mode', icon: Moon },
    { value: 'system', label: 'System theme', icon: Monitor },
];

const handleUpdate = (value: AcceptableValue | AcceptableValue[]) => {
    if (typeof value !== 'string') {
        return;
    }

    updateAppearance(value as Appearance);
};
</script>

<template>
    <TooltipProvider>
        <ToggleGroup
            type="single"
            :model-value="appearance"
            variant="outline"
            size="sm"
            aria-label="Theme"
            @update:model-value="handleUpdate"
        >
            <Tooltip v-for="option in options" :key="option.value">
                <TooltipTrigger as-child>
                    <ToggleGroupItem
                        :value="option.value"
                        class="size-9 px-0"
                        :aria-label="option.label"
                    >
                        <component :is="option.icon" />
                        <span class="sr-only">{{ option.label }}</span>
                    </ToggleGroupItem>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ option.label }}</p>
                </TooltipContent>
            </Tooltip>
        </ToggleGroup>
    </TooltipProvider>
</template>
