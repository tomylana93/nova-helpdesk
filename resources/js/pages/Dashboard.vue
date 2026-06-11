<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Donut } from '@unovis/ts';
import { VisDonut, VisSingleContainer } from '@unovis/vue';
import {
    Ticket,
    CheckCircle2,
    Clock,
    AlertTriangle,
    ArrowUpRight,
    ShieldCheck,
    Sparkles,
    CircleHelp,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    ChartContainer,
    ChartTooltip,
    ChartTooltipContent,
    componentToString,
} from '@/components/ui/chart';
import type { ChartConfig } from '@/components/ui/chart';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import { index as ticketsIndex, show } from '@/routes/tickets';
import type { AuthenticatedSharedPageProps } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const props = defineProps<{
    role: 'requester' | 'it_agent' | 'super_admin';
    metrics: Array<{
        label: string;
        value: string;
        description: string;
    }>;
    recentTickets: Array<{
        id: string;
        ticket_number: string;
        subject: string;
        type: string;
        priority: {
            value: string;
            label: string;
            variant: string;
        };
        status: {
            value: string;
            label: string;
            variant: string;
        };
        requester_name: string;
        assignee_name: string;
        created_at: string;
    }>;
    charts: {
        priority?: Array<{ name: string; value: number }>;
        status?: Array<{ name: string; value: number }>;
        slaComplianceRate?: number;
    };
}>();

const page = usePage<AuthenticatedSharedPageProps>();
const auth = computed(() => page.props.auth);

// Dynamic icon resolver for metrics
function getIcon(label: string) {
    if (label.includes('Total')) {
        return Ticket;
    }

    if (label.includes('Active') || label.includes('Assigned')) {
        return Clock;
    }

    if (label.includes('Resolved')) {
        return CheckCircle2;
    }

    if (
        label.includes('Unassigned') ||
        label.includes('Pending') ||
        label.includes('Breached')
    ) {
        return AlertTriangle;
    }

    return Ticket;
}

// Chart configuration mapping
const chartRawData = computed(() => {
    if (props.role === 'it_agent') {
        return props.charts?.status || [];
    } else {
        return props.charts?.priority || [];
    }
});

const chartConfig = computed<ChartConfig>(() => {
    const config: ChartConfig = {
        value: {
            label: 'Tickets',
        },
    };

    chartRawData.value.forEach((item, index) => {
        const key = item.name.toLowerCase().replace(/\s+/g, '_');
        config[key] = {
            label: item.name,
            color: `var(--chart-${(index % 5) + 1})`,
        };
    });

    return config;
});

const formattedChartData = computed(() => {
    return chartRawData.value.map((item, index) => {
        const key = item.name.toLowerCase().replace(/\s+/g, '_');

        return {
            name: key,
            value: item.value,
            fill: `var(--chart-${(index % 5) + 1})`,
        };
    });
});

const totalChartTickets = computed(() => {
    return chartRawData.value.reduce((acc, curr) => acc + curr.value, 0);
});

// Utility formatting date
function formatDate(dateStr: string) {
    if (!dateStr) {
        return '-';
    }

    const d = new Date(dateStr);

    return d.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <div class="flex flex-1 flex-col gap-6 p-6">
        <Head title="Dashboard" />
        <!-- Welcoming banner with vibrant aesthetics -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-indigo-600 to-cyan-500 p-6 text-white shadow-lg md:p-8"
        >
            <div class="relative z-10 flex flex-col gap-2 md:max-w-xl">
                <div
                    class="flex items-center gap-2 text-xs font-semibold tracking-wider text-indigo-100 uppercase"
                >
                    <Sparkles class="h-4 w-4 animate-pulse text-cyan-200" />
                    <span>Welcome to Nova Helpdesk</span>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl">
                    Hello, {{ auth.user?.name }}
                </h1>
                <p class="text-sm text-indigo-100 md:text-base">
                    Here is your helpdesk overview. Manage tickets, track SLA
                    compliance, and view system status.
                </p>
            </div>
            <!-- Decorative light patterns -->
            <div
                class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"
            ></div>
            <div
                class="absolute right-20 -bottom-10 h-32 w-32 rounded-full bg-cyan-400/20 blur-3xl"
            ></div>
        </div>

        <!-- Metric Cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card
                v-for="(metric, idx) in props.metrics"
                :key="idx"
                class="border-sidebar-border/70 transition-transform duration-200 hover:translate-y-[-2px] hover:shadow-md dark:border-sidebar-border"
            >
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">{{
                        metric.label
                    }}</CardTitle>
                    <div class="rounded-lg bg-muted p-1.5">
                        <component
                            :is="getIcon(metric.label)"
                            class="h-4 w-4 text-muted-foreground"
                        />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold tracking-tight">
                        {{ metric.value }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ metric.description }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Main Dashboard Section (Charts + Table) -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Left Side: Recent Tickets Table -->
            <Card
                class="flex flex-col border-sidebar-border/70 lg:col-span-2 dark:border-sidebar-border"
            >
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>Recent Tickets</CardTitle>
                        <CardDescription
                            >Review the latest ticket activity assigned or
                            requested.</CardDescription
                        >
                    </div>
                    <Button
                        :as="Link"
                        :href="ticketsIndex()"
                        variant="outline"
                        size="sm"
                        class="gap-1.5"
                    >
                        <span>View All</span>
                        <ArrowUpRight class="h-4 w-4" />
                    </Button>
                </CardHeader>
                <CardContent class="flex-1 overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[120px]"
                                    >Ticket No</TableHead
                                >
                                <TableHead>Subject</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Priority</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead v-if="props.role !== 'requester'"
                                    >Requester</TableHead
                                >
                                <TableHead v-else>Assignee</TableHead>
                                <TableHead>Date</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="ticket in props.recentTickets"
                                :key="ticket.id"
                            >
                                <TableCell
                                    class="font-medium whitespace-nowrap"
                                >
                                    <Link
                                        :href="show({ ticket: ticket.id })"
                                        class="text-primary hover:underline"
                                    >
                                        {{ ticket.ticket_number }}
                                    </Link>
                                </TableCell>
                                <TableCell class="max-w-[200px] truncate">{{
                                    ticket.subject
                                }}</TableCell>
                                <TableCell class="capitalize">{{
                                    ticket.type
                                }}</TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            ticket.priority.variant as 'default'
                                        "
                                        class="font-normal"
                                    >
                                        {{ ticket.priority.label }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            ticket.status.variant as 'default'
                                        "
                                        class="font-normal"
                                    >
                                        {{ ticket.status.label }}
                                    </Badge>
                                </TableCell>
                                <TableCell
                                    v-if="props.role !== 'requester'"
                                    class="whitespace-nowrap"
                                >
                                    {{ ticket.requester_name }}
                                </TableCell>
                                <TableCell v-else class="whitespace-nowrap">
                                    {{ ticket.assignee_name }}
                                </TableCell>
                                <TableCell class="whitespace-nowrap">
                                    {{ formatDate(ticket.created_at) }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="props.recentTickets.length === 0">
                                <TableCell
                                    colspan="8"
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    No recent tickets found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Right Side: Charts & Gauges -->
            <div class="flex flex-col gap-6">
                <!-- SLA Compliance Radial Gauge (IT Agent & Super Admin) -->
                <Card
                    v-if="props.charts?.slaComplianceRate !== undefined"
                    class="flex flex-col items-center justify-center border-sidebar-border/70 p-6 text-center dark:border-sidebar-border"
                >
                    <CardHeader class="pb-2">
                        <CardTitle
                            class="flex items-center gap-1.5 text-sm font-medium text-muted-foreground"
                        >
                            <ShieldCheck class="h-4 w-4 text-emerald-500" />
                            <span>SLA Compliance Rate</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent
                        class="relative flex items-center justify-center py-4"
                    >
                        <svg class="h-32 w-32 -rotate-90 transform">
                            <!-- Background Circle -->
                            <circle
                                cx="64"
                                cy="64"
                                r="50"
                                stroke="currentColor"
                                stroke-width="10"
                                class="text-muted/30"
                                fill="transparent"
                            />
                            <!-- Progress Circle -->
                            <circle
                                cx="64"
                                cy="64"
                                r="50"
                                stroke="currentColor"
                                stroke-width="10"
                                :stroke-dasharray="2 * Math.PI * 50"
                                :stroke-dashoffset="
                                    2 *
                                    Math.PI *
                                    50 *
                                    (1 -
                                        (props.charts?.slaComplianceRate ??
                                            100) /
                                            100)
                                "
                                class="text-emerald-500 transition-all duration-500 ease-out dark:text-emerald-400"
                                stroke-linecap="round"
                                fill="transparent"
                            />
                        </svg>
                        <div
                            class="absolute flex flex-col items-center justify-center"
                        >
                            <span
                                class="text-3xl font-extrabold tracking-tight"
                            >
                                {{ props.charts.slaComplianceRate }}%
                            </span>
                            <span class="text-xs text-muted-foreground"
                                >Resolved in SLA</span
                            >
                        </div>
                    </CardContent>
                    <CardDescription class="px-2 text-xs leading-relaxed">
                        Percentage of resolved tickets completed before their
                        target resolution due deadline.
                    </CardDescription>
                </Card>

                <!-- Pie/Donut Chart breakdown -->
                <Card
                    class="flex flex-col border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <CardHeader class="pb-2">
                        <CardTitle
                            class="text-sm font-semibold text-muted-foreground"
                        >
                            {{
                                props.role === 'it_agent'
                                    ? 'Active Status Distribution'
                                    : 'Ticket Priority Distribution'
                            }}
                        </CardTitle>
                        <CardDescription>
                            {{
                                props.role === 'it_agent'
                                    ? 'Breakdown of current active tickets'
                                    : 'Distribution across priority levels'
                            }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent
                        class="flex min-h-[220px] flex-1 items-center justify-center pb-4"
                    >
                        <div
                            v-if="totalChartTickets > 0"
                            class="flex w-full justify-center"
                        >
                            <ChartContainer
                                :config="chartConfig"
                                class="mx-auto aspect-square w-full max-w-[200px]"
                                :style="{
                                    '--vis-donut-central-label-font-size':
                                        'var(--text-3xl)',
                                    '--vis-donut-central-label-font-weight':
                                        'var(--font-weight-bold)',
                                    '--vis-donut-central-label-text-color':
                                        'var(--foreground)',
                                    '--vis-donut-central-sub-label-text-color':
                                        'var(--muted-foreground)',
                                }"
                            >
                                <VisSingleContainer
                                    :data="formattedChartData"
                                    :margin="{
                                        top: 10,
                                        bottom: 10,
                                        left: 10,
                                        right: 10,
                                    }"
                                >
                                    <VisDonut
                                        :value="(d: any) => d.value"
                                        :color="
                                            (d: any) =>
                                                chartConfig[d.name]?.color
                                        "
                                        :arc-width="20"
                                        :central-label-offset-y="5"
                                        :central-label="
                                            totalChartTickets.toString()
                                        "
                                        central-sub-label="Tickets"
                                    />
                                    <ChartTooltip
                                        :triggers="{
                                            [Donut.selectors.segment]:
                                                componentToString(
                                                    chartConfig,
                                                    ChartTooltipContent,
                                                    { hideLabel: true },
                                                )!,
                                        }"
                                    />
                                </VisSingleContainer>
                            </ChartContainer>
                        </div>
                        <div
                            v-else
                            class="flex flex-col items-center gap-2 py-10 text-center text-sm text-muted-foreground"
                        >
                            <CircleHelp class="h-8 w-8 text-muted/50" />
                            <span>No ticket distribution data available.</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
