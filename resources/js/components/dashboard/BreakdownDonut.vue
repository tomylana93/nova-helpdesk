<script setup lang="ts">
import { VisDonut, VisSingleContainer } from '@unovis/vue';
import { CircleHelp } from 'lucide-vue-next';
import { computed } from 'vue';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTrans } from '@/composables/useTrans';
import type { DashboardBreakdownSegment } from '@/types';

const props = defineProps<{
    type: 'priority' | 'status';
    segments: DashboardBreakdownSegment[];
}>();

const { trans } = useTrans();

const title = computed(() =>
    props.type === 'status'
        ? trans('dashboard.breakdown.status_title')
        : trans('dashboard.breakdown.priority_title'),
);

function segmentLabel(key: string): string {
    return trans(`dashboard.${props.type}.${key}`);
}

const total = computed(() =>
    props.segments.reduce((sum, segment) => sum + segment.value, 0),
);

const data = computed(() => props.segments.map((segment) => segment.value));

function colorAt(index: number): string {
    return `var(--chart-${(index % 5) + 1})`;
}
</script>

<template>
    <Card class="flex flex-col border-border/60">
        <CardHeader class="pb-2">
            <CardTitle class="text-sm font-semibold text-muted-foreground">
                {{ title }}
            </CardTitle>
            <CardDescription>
                {{ trans('dashboard.breakdown.tickets') }}: {{ total }}
            </CardDescription>
        </CardHeader>
        <CardContent
            class="flex min-h-[220px] flex-1 items-center justify-center"
        >
            <div
                v-if="total > 0"
                class="flex w-full flex-col items-center gap-4"
            >
                <div class="aspect-square w-full max-w-[180px]">
                    <VisSingleContainer :data="data">
                        <VisDonut
                            :value="(d: number) => d"
                            :arc-width="20"
                            :color="(_: number, i: number) => colorAt(i)"
                            :central-label="String(total)"
                            :central-sub-label="
                                trans('dashboard.breakdown.tickets')
                            "
                        />
                    </VisSingleContainer>
                </div>
                <ul class="grid w-full grid-cols-2 gap-1 text-xs">
                    <li
                        v-for="(segment, index) in segments"
                        :key="segment.key"
                        class="flex items-center gap-1.5"
                    >
                        <span
                            class="h-2 w-2 shrink-0 rounded-full"
                            :style="{ backgroundColor: colorAt(index) }"
                        />
                        <span class="truncate text-muted-foreground">
                            {{ segmentLabel(segment.key) }}
                        </span>
                        <span class="ml-auto font-medium">{{
                            segment.value
                        }}</span>
                    </li>
                </ul>
            </div>
            <div
                v-else
                class="flex flex-col items-center gap-2 py-10 text-center text-sm text-muted-foreground"
            >
                <CircleHelp class="h-8 w-8 text-muted-foreground/50" />
                <span>{{ trans('dashboard.breakdown.empty') }}</span>
            </div>
        </CardContent>
    </Card>
</template>
