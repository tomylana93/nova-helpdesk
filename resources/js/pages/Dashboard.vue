<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    Clock,
    Inbox,
    Ticket,
    UserCheck,
} from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed } from 'vue';

import BreakdownDonut from '@/components/dashboard/BreakdownDonut.vue';
import MetricCard from '@/components/dashboard/MetricCard.vue';
import PeriodControl from '@/components/dashboard/PeriodControl.vue';
import SlaGauge from '@/components/dashboard/SlaGauge.vue';
import TrendChart from '@/components/dashboard/TrendChart.vue';
import { useDashboard } from '@/composables/useDashboard';
import { dashboard } from '@/routes';
import type { AuthenticatedSharedPageProps, DashboardProps } from '@/types';

const props = defineProps<DashboardProps>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const page = usePage<AuthenticatedSharedPageProps>();
const { trans, periodLabel, previousPeriodLabel } = useDashboard();

const userName = computed(() => page.props.auth.user?.name ?? '');
const periodText = computed(() => periodLabel(props.period));
const previousText = computed(() => previousPeriodLabel(props.period));

const liveIcons: Record<string, Component> = {
    active: Ticket,
    assigned: UserCheck,
    unassigned: Inbox,
    pending_approval: Clock,
    sla_breached: AlertTriangle,
};

const metricIcons: Record<string, Component> = {
    created: Ticket,
    resolved: CheckCircle2,
};
</script>

<template>
    <div class="flex flex-1 flex-col gap-6 p-6">
        <Head title="Dashboard" />

        <!-- Header: greeting + period control (design-system aligned, no gradient) -->
        <div
            class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between"
        >
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ trans('dashboard.greeting', { name: userName }) }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ trans('dashboard.subtitle', { period: periodText }) }}
                </p>
            </div>
            <PeriodControl :period="period" />
        </div>

        <!-- Zone: Live snapshot (not period-filtered) -->
        <section class="space-y-3">
            <h2
                class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
            >
                {{ trans('dashboard.live.heading') }}
            </h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <MetricCard
                    v-for="metric in live"
                    :key="metric.key"
                    :label="trans(`dashboard.live.${metric.key}`)"
                    :value="metric.value"
                    :icon="liveIcons[metric.key] ?? Ticket"
                />
            </div>
        </section>

        <!-- Zone: Period (filtered) -->
        <section class="space-y-3">
            <h2
                class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
            >
                {{ trans('dashboard.metric.heading', { period: periodText }) }}
            </h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <MetricCard
                    v-for="metric in periodMetrics"
                    :key="metric.key"
                    :label="trans(`dashboard.metric.${metric.key}`)"
                    :value="metric.value"
                    :icon="metricIcons[metric.key] ?? Ticket"
                    :delta-percent="metric.deltaPercent"
                    :direction="metric.direction"
                    :sentiment="metric.sentiment"
                    :caption="
                        trans('dashboard.period.vs_previous', {
                            period: previousText,
                        })
                    "
                />
                <SlaGauge
                    v-if="compliance"
                    :compliance="compliance"
                    :previous-label="previousText"
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <TrendChart
                        :granularity="trend.granularity"
                        :points="trend.points"
                    />
                </div>
                <BreakdownDonut
                    :type="breakdown.type"
                    :segments="breakdown.segments"
                />
            </div>
        </section>
    </div>
</template>
