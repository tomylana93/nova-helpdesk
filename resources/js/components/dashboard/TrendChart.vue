<script setup lang="ts">
import { VisAxis, VisLine, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useDashboard } from '@/composables/useDashboard';
import { useTrans } from '@/composables/useTrans';
import type { DashboardTrendPoint } from '@/types';

const props = defineProps<{
    granularity: 'day' | 'month';
    points: DashboardTrendPoint[];
}>();

const { trans } = useTrans();
const { trendTick } = useDashboard();

const hasData = computed(() =>
    props.points.some((point) => point.created > 0 || point.resolved > 0),
);

// x = index into the dense series; y accessors read each metric.
const x = (_: DashboardTrendPoint, i: number) => i;
const createdY = (d: DashboardTrendPoint) => d.created;
const resolvedY = (d: DashboardTrendPoint) => d.resolved;

function tickFormat(index: number): string {
    const point = props.points[index];

    return point ? trendTick(props.granularity, point.label) : '';
}
</script>

<template>
    <Card class="flex flex-col border-border/60">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-semibold text-muted-foreground">
                {{ trans('dashboard.trend.title') }}
            </CardTitle>
            <div class="flex items-center gap-3 text-xs text-muted-foreground">
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-[var(--chart-1)]" />
                    {{ trans('dashboard.trend.created') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-[var(--chart-2)]" />
                    {{ trans('dashboard.trend.resolved') }}
                </span>
            </div>
        </CardHeader>
        <CardContent class="min-h-[220px] flex-1">
            <VisXYContainer v-if="hasData" :data="points" :height="220">
                <VisLine :x="x" :y="createdY" color="var(--chart-1)" />
                <VisLine :x="x" :y="resolvedY" color="var(--chart-2)" />
                <VisAxis
                    type="x"
                    :tick-format="tickFormat"
                    :grid-line="false"
                />
                <VisAxis type="y" :grid-line="true" />
            </VisXYContainer>
            <div
                v-else
                class="flex h-[220px] items-center justify-center text-sm text-muted-foreground"
            >
                {{ trans('dashboard.trend.empty') }}
            </div>
        </CardContent>
    </Card>
</template>
