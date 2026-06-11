<script setup lang="ts">
import { ArrowDown, ArrowUp, Minus } from 'lucide-vue-next';
import { computed } from 'vue';

import { useTrans } from '@/composables/useTrans';
import { cn } from '@/lib/utils';

const props = defineProps<{
    deltaPercent: number | null;
    direction: 'up' | 'down' | 'flat';
    sentiment: 'higher_is_better' | 'lower_is_better' | 'neutral';
}>();

const { trans } = useTrans();

const icon = computed(() => {
    if (props.direction === 'up') {
        return ArrowUp;
    }

    if (props.direction === 'down') {
        return ArrowDown;
    }

    return Minus;
});

// Colour only when the metric has a sentiment; neutral stays muted.
const toneClass = computed(() => {
    if (props.sentiment === 'neutral' || props.direction === 'flat') {
        return 'text-muted-foreground';
    }

    const isGood =
        props.sentiment === 'higher_is_better'
            ? props.direction === 'up'
            : props.direction === 'down';

    return isGood
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-destructive';
});

const label = computed(() => {
    if (props.deltaPercent === null) {
        return trans('dashboard.metric.new');
    }

    return `${Math.abs(props.deltaPercent)}%`;
});
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex items-center gap-0.5 text-xs font-medium',
                toneClass,
            )
        "
    >
        <component :is="icon" class="h-3 w-3" />
        {{ label }}
    </span>
</template>
