<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Check, Monitor, Moon, Sun } from 'lucide-vue-next';
import type { AcceptableValue } from 'reka-ui';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/composables/useAppearance';
import type { Appearance } from '@/types';

const { appearance, updateAppearance } = useAppearance();

type AppearanceOption = {
    value: Appearance;
    icon: typeof Sun;
    label: string;
};

const options = computed<AppearanceOption[]>(() => [
    {
        value: 'light',
        icon: Sun,
        label: trans('appearance.switcher.light'),
    },
    {
        value: 'dark',
        icon: Moon,
        label: trans('appearance.switcher.dark'),
    },
    {
        value: 'system',
        icon: Monitor,
        label: trans('appearance.switcher.system'),
    },
]);

const currentOption = computed<AppearanceOption>(() => {
    return (
        options.value.find((option) => option.value === appearance.value) ??
        options.value[2]
    );
});

const onValueChange = (value: AcceptableValue): void => {
    if (value === 'light' || value === 'dark' || value === 'system') {
        updateAppearance(value);
    }
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="size-8 text-muted-foreground hover:text-foreground"
                :aria-label="trans('appearance.switcher.aria_label')"
            >
                <component :is="currentOption.icon" class="size-4" />
                <span class="sr-only">{{
                    trans('appearance.switcher.aria_label')
                }}</span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-44">
            <DropdownMenuLabel>
                {{ trans('appearance.switcher.aria_label') }}
            </DropdownMenuLabel>
            <DropdownMenuSeparator />

            <DropdownMenuRadioGroup
                :model-value="appearance"
                @update:model-value="onValueChange"
            >
                <DropdownMenuRadioItem
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                    class="gap-2"
                >
                    <component :is="option.icon" class="size-4" />
                    <span>{{ option.label }}</span>
                    <template #indicator-icon>
                        <Check class="size-3" />
                    </template>
                </DropdownMenuRadioItem>
            </DropdownMenuRadioGroup>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
