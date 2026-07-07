<script setup lang="ts">
import { ShieldCheck } from '@lucide/vue';
import { VisDonut, VisSingleContainer } from '@unovis/vue';
import { computed } from 'vue';

import DeltaBadge from '@/components/dashboard/DeltaBadge.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useTrans } from '@/composables/useTrans';
import type { DashboardCompliance } from '@/types';

const props = defineProps<{
    compliance: DashboardCompliance;
    previousLabel: string;
}>();

const { trans } = useTrans();

// Two-segment donut: compliant (chart-2 token) vs remainder (muted).
const data = computed(() => [
    props.compliance.rate,
    100 - props.compliance.rate,
]);

const tooltipText = computed(() =>
    trans('dashboard.compliance.tooltip', {
        within: props.compliance.resolvedWithinDue,
        total: props.compliance.totalResolved,
    }),
);
</script>

<template>
    <TooltipProvider>
        <Tooltip>
            <TooltipTrigger as-child>
                <Card class="border-border/60">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle
                            class="text-sm font-medium text-muted-foreground"
                        >
                            {{ trans('dashboard.compliance.title') }}
                        </CardTitle>
                        <div class="rounded-lg bg-muted p-1.5">
                            <ShieldCheck
                                class="h-4 w-4 text-muted-foreground"
                            />
                        </div>
                    </CardHeader>
                    <CardContent class="flex flex-col items-center gap-2">
                        <div class="relative h-40 w-40">
                            <VisSingleContainer
                                :data="data"
                                :height="160"
                                :width="160"
                            >
                                <VisDonut
                                    :value="(d: number) => d"
                                    :radius="72"
                                    :arc-width="14"
                                    :pad-angle="0.02"
                                    :corner-radius="7"
                                    :color="
                                        (_: number, i: number) =>
                                            i === 0
                                                ? 'var(--chart-2)'
                                                : 'var(--muted)'
                                    "
                                />
                            </VisSingleContainer>
                            <div
                                class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center"
                            >
                                <span
                                    class="text-3xl font-extrabold tracking-tight"
                                >
                                    {{ compliance.rate }}%
                                </span>
                                <DeltaBadge
                                    :delta-percent="compliance.deltaPercent"
                                    :direction="compliance.direction"
                                    sentiment="higher_is_better"
                                />
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ trans('dashboard.compliance.caption') }}
                        </p>
                    </CardContent>
                </Card>
            </TooltipTrigger>
            <TooltipContent side="top" class="max-w-xs">
                <p class="text-xs">{{ tooltipText }}</p>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{
                        trans('dashboard.period.vs_previous', {
                            period: previousLabel,
                        })
                    }}
                </p>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
