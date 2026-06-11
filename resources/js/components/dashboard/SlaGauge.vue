<script setup lang="ts">
import { VisDonut, VisSingleContainer } from '@unovis/vue';
import { ShieldCheck } from 'lucide-vue-next';
import { computed } from 'vue';

import DeltaBadge from '@/components/dashboard/DeltaBadge.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
                <Card
                    class="flex flex-col items-center border-border/60 p-6 text-center"
                >
                    <CardHeader class="pb-2">
                        <CardTitle
                            class="flex items-center gap-1.5 text-sm font-medium text-muted-foreground"
                        >
                            <ShieldCheck class="h-4 w-4 text-[var(--chart-2)]" />
                            {{ trans('dashboard.compliance.title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent
                        class="relative flex items-center justify-center py-2"
                    >
                        <div class="relative h-32 w-32">
                            <VisSingleContainer :data="data">
                                <VisDonut
                                    :value="(d: number) => d"
                                    :arc-width="12"
                                    :pad-angle="0.02"
                                    :color="
                                        (_: number, i: number) =>
                                            i === 0
                                                ? 'var(--chart-2)'
                                                : 'var(--muted)'
                                    "
                                />
                            </VisSingleContainer>
                            <div
                                class="absolute inset-0 flex flex-col items-center justify-center"
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
                    </CardContent>
                    <CardDescription class="text-xs">
                        {{ trans('dashboard.compliance.caption') }}
                    </CardDescription>
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
