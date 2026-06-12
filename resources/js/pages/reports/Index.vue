<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Download, FileClock } from 'lucide-vue-next';
import type { AcceptableValue } from 'reka-ui';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { availableYears } from '@/composables/useDashboard';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { index as reportsIndex } from '@/routes/reports';
import {
    audit as exportAudit,
    operational as exportOperational,
} from '@/routes/reports/export';
import type {
    ReportFilters,
    ReportOption,
    ReportsProps,
    TicketPriority,
    TicketStatus,
    TicketType,
} from '@/types';

const props = defineProps<ReportsProps>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Reports', href: reportsIndex() },
        ],
    },
});

const { trans, locale } = useTrans();
const currentYear = new Date().getFullYear();
const years = availableYears(currentYear);

const months = computed(() =>
    Array.from({ length: 12 }, (_, index) => ({
        value: index + 1,
        label: new Date(2000, index, 1).toLocaleDateString(locale.value, {
            month: 'long',
        }),
    })),
);

const summaryCards = computed(() => [
    {
        key: 'created',
        label: trans('reports.summary.created'),
        value: props.summary.created,
    },
    {
        key: 'resolved',
        label: trans('reports.summary.resolved'),
        value: props.summary.resolved,
    },
    {
        key: 'active',
        label: trans('reports.summary.active'),
        value: props.summary.active,
    },
    {
        key: 'overdue',
        label: trans('reports.summary.overdue'),
        value: props.summary.overdue,
    },
]);

const breakdownGroups = computed(() =>
    Object.entries(props.breakdowns).map(([key, segments]) => ({
        key,
        title: trans(`reports.breakdown.${key}`),
        segments,
    })),
);

function queryFromFilters(filters: ReportFilters): Record<string, string> {
    const query: Record<string, string> = {
        mode: filters.mode,
        year: String(filters.year),
    };

    if (filters.mode === 'monthly' && filters.month !== null) {
        query.month = String(filters.month);
    }

    for (const key of [
        'branch_id',
        'department_id',
        'category_id',
        'assignee_id',
        'status',
        'priority',
        'type',
        'event',
    ] as const) {
        const value = filters[key];

        if (value !== null && value !== '') {
            query[key] = value;
        }
    }

    return query;
}

function reportsUrl(filters: ReportFilters, page?: number): string {
    const query = queryFromFilters(filters);

    if (page !== undefined) {
        query.audit_page = String(page);
    }

    return reportsIndex.url({ query });
}

function exportUrl(kind: 'operational' | 'audit'): string {
    const query = queryFromFilters(props.filters);

    return kind === 'operational'
        ? exportOperational.url({ query })
        : exportAudit.url({ query });
}

function visit(next: ReportFilters, page?: number): void {
    router.visit(reportsUrl(next, page), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        only: ['filters', 'summary', 'breakdowns', 'audit'],
    });
}

function updateFilter<K extends keyof ReportFilters>(
    key: K,
    value: ReportFilters[K],
): void {
    visit({ ...props.filters, [key]: value });
}

function selectValue(
    value: AcceptableValue | AcceptableValue[],
): string | null {
    const normalized = Array.isArray(value) ? value[0] : value;

    if (
        normalized === undefined ||
        normalized === null ||
        normalized === '__all'
    ) {
        return null;
    }

    return String(normalized);
}

function selectOptionValue<T extends string>(
    value: AcceptableValue | AcceptableValue[],
    options: ReportOption<T>[],
): T | null {
    const selected = selectValue(value);

    if (selected === null) {
        return null;
    }

    return options.find((option) => option.value === selected)?.value ?? null;
}

function setMode(mode: 'monthly' | 'yearly'): void {
    visit({
        ...props.filters,
        mode,
        month:
            mode === 'monthly'
                ? (props.filters.month ?? new Date().getMonth() + 1)
                : null,
    });
}

function setMonth(value: AcceptableValue | AcceptableValue[]): void {
    updateFilter('month', Number(value));
}

function setYear(value: AcceptableValue | AcceptableValue[]): void {
    updateFilter('year', Number(value));
}

function metadataText(metadata: Record<string, unknown> | null): string {
    return metadata === null ? '-' : JSON.stringify(metadata);
}

function optionLabel(options: ReportOption[], value: string | null): string {
    return options.find((option) => option.value === value)?.label ?? '';
}
</script>

<template>
    <Head :title="trans('reports.title')" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between"
        >
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ trans('reports.heading') }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ trans('reports.description') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Button
                    as="a"
                    variant="outline"
                    :href="exportUrl('operational')"
                >
                    <Download class="size-4" />
                    {{ trans('reports.export.operational') }}
                </Button>
                <Button as="a" variant="outline" :href="exportUrl('audit')">
                    <FileClock class="size-4" />
                    {{ trans('reports.export.audit') }}
                </Button>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>{{ trans('reports.filters.period') }}</CardTitle>
                <CardDescription>
                    {{ trans('reports.description') }}
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div
                    class="flex flex-col gap-3 lg:flex-row lg:items-center lg:gap-4"
                >
                    <div class="flex shrink-0 items-center gap-2">
                        <Button
                            v-for="mode in ['monthly', 'yearly'] as const"
                            :key="mode"
                            size="sm"
                            :variant="
                                filters.mode === mode ? 'default' : 'outline'
                            "
                            @click="setMode(mode)"
                        >
                            {{ trans(`reports.filters.${mode}`) }}
                        </Button>
                    </div>

                    <div
                        class="grid w-full gap-3 sm:grid-cols-2 lg:max-w-md lg:grid-cols-[minmax(10rem,1fr)_minmax(8rem,10rem)]"
                    >
                        <Select
                            v-if="filters.mode === 'monthly'"
                            :model-value="String(filters.month)"
                            @update:model-value="setMonth"
                        >
                            <SelectTrigger class="w-full justify-between">
                                <SelectValue
                                    :placeholder="
                                        trans('reports.filters.month')
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="month in months"
                                    :key="month.value"
                                    :value="String(month.value)"
                                >
                                    {{ month.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Select
                            :model-value="String(filters.year)"
                            @update:model-value="setYear"
                        >
                            <SelectTrigger class="w-full justify-between">
                                <SelectValue
                                    :placeholder="trans('reports.filters.year')"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="year in years"
                                    :key="year"
                                    :value="String(year)"
                                >
                                    {{ year }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div
                    class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5"
                >
                    <Select
                        :model-value="filters.branch_id ?? '__all'"
                        @update:model-value="
                            (value) =>
                                updateFilter('branch_id', selectValue(value))
                        "
                    >
                        <SelectTrigger class="w-full justify-between">
                            <SelectValue
                                :placeholder="trans('reports.filters.branch')"
                            >
                                {{
                                    optionLabel(
                                        options.branches,
                                        filters.branch_id,
                                    ) || trans('reports.filters.all')
                                }}
                            </SelectValue>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all">
                                {{ trans('reports.filters.all') }}
                            </SelectItem>
                            <SelectItem
                                v-for="option in options.branches"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select
                        :model-value="filters.department_id ?? '__all'"
                        @update:model-value="
                            (value) =>
                                updateFilter(
                                    'department_id',
                                    selectValue(value),
                                )
                        "
                    >
                        <SelectTrigger class="w-full justify-between">
                            <SelectValue
                                :placeholder="
                                    trans('reports.filters.department')
                                "
                            >
                                {{
                                    optionLabel(
                                        options.departments,
                                        filters.department_id,
                                    ) || trans('reports.filters.all')
                                }}
                            </SelectValue>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all">
                                {{ trans('reports.filters.all') }}
                            </SelectItem>
                            <SelectItem
                                v-for="option in options.departments"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select
                        :model-value="filters.category_id ?? '__all'"
                        @update:model-value="
                            (value) =>
                                updateFilter('category_id', selectValue(value))
                        "
                    >
                        <SelectTrigger class="w-full justify-between">
                            <SelectValue
                                :placeholder="trans('reports.filters.category')"
                            >
                                {{
                                    optionLabel(
                                        options.categories,
                                        filters.category_id,
                                    ) || trans('reports.filters.all')
                                }}
                            </SelectValue>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all">
                                {{ trans('reports.filters.all') }}
                            </SelectItem>
                            <SelectItem
                                v-for="option in options.categories"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select
                        v-if="options.assignees.length > 0"
                        :model-value="filters.assignee_id ?? '__all'"
                        @update:model-value="
                            (value) =>
                                updateFilter('assignee_id', selectValue(value))
                        "
                    >
                        <SelectTrigger class="w-full justify-between">
                            <SelectValue
                                :placeholder="trans('reports.filters.assignee')"
                            >
                                {{
                                    optionLabel(
                                        options.assignees,
                                        filters.assignee_id,
                                    ) || trans('reports.filters.all')
                                }}
                            </SelectValue>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all">
                                {{ trans('reports.filters.all') }}
                            </SelectItem>
                            <SelectItem
                                v-for="option in options.assignees"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select
                        :model-value="filters.status ?? '__all'"
                        @update:model-value="
                            (value) =>
                                updateFilter(
                                    'status',
                                    selectOptionValue<TicketStatus>(
                                        value,
                                        options.statuses,
                                    ),
                                )
                        "
                    >
                        <SelectTrigger class="w-full justify-between">
                            <SelectValue
                                :placeholder="trans('reports.filters.status')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all">
                                {{ trans('reports.filters.all') }}
                            </SelectItem>
                            <SelectItem
                                v-for="option in options.statuses"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select
                        :model-value="filters.priority ?? '__all'"
                        @update:model-value="
                            (value) =>
                                updateFilter(
                                    'priority',
                                    selectOptionValue<TicketPriority>(
                                        value,
                                        options.priorities,
                                    ),
                                )
                        "
                    >
                        <SelectTrigger class="w-full justify-between">
                            <SelectValue
                                :placeholder="trans('reports.filters.priority')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all">
                                {{ trans('reports.filters.all') }}
                            </SelectItem>
                            <SelectItem
                                v-for="option in options.priorities"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select
                        :model-value="filters.type ?? '__all'"
                        @update:model-value="
                            (value) =>
                                updateFilter(
                                    'type',
                                    selectOptionValue<TicketType>(
                                        value,
                                        options.types,
                                    ),
                                )
                        "
                    >
                        <SelectTrigger class="w-full justify-between">
                            <SelectValue
                                :placeholder="trans('reports.filters.type')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all">
                                {{ trans('reports.filters.all') }}
                            </SelectItem>
                            <SelectItem
                                v-for="option in options.types"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select
                        :model-value="filters.event ?? '__all'"
                        @update:model-value="
                            (value) => updateFilter('event', selectValue(value))
                        "
                    >
                        <SelectTrigger class="w-full justify-between">
                            <SelectValue
                                :placeholder="trans('reports.filters.event')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all">
                                {{ trans('reports.filters.all') }}
                            </SelectItem>
                            <SelectItem
                                v-for="option in audit.events"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardContent>
        </Card>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <Card v-for="card in summaryCards" :key="card.key">
                <CardHeader class="pb-2">
                    <CardDescription>{{ card.label }}</CardDescription>
                    <CardTitle class="text-3xl">{{ card.value }}</CardTitle>
                </CardHeader>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>
                        {{ trans('reports.summary.compliance') }}
                    </CardDescription>
                    <CardTitle class="text-3xl">
                        {{ summary.complianceRate }}%
                    </CardTitle>
                    <CardDescription>
                        {{
                            trans('reports.summary.resolved_within_due', {
                                within: summary.resolvedWithinDue,
                                total: summary.totalResolved,
                            })
                        }}
                    </CardDescription>
                </CardHeader>
            </Card>
        </section>

        <section class="space-y-3">
            <h2
                class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
            >
                {{ trans('reports.breakdown.heading') }}
            </h2>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <Card v-for="group in breakdownGroups" :key="group.key">
                    <CardHeader>
                        <CardTitle class="text-base">{{
                            group.title
                        }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-if="group.segments.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            {{ trans('reports.breakdown.empty') }}
                        </div>
                        <div
                            v-for="segment in group.segments"
                            :key="segment.key"
                            class="flex items-center justify-between gap-4 text-sm"
                        >
                            <span class="truncate text-muted-foreground">
                                {{ segment.label }}
                            </span>
                            <span class="font-medium">{{ segment.value }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>

        <Card>
            <CardHeader>
                <CardTitle>{{ trans('reports.audit.heading') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="overflow-x-auto rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    {{ trans('reports.audit.occurred_at') }}
                                </TableHead>
                                <TableHead>{{
                                    trans('reports.audit.event')
                                }}</TableHead>
                                <TableHead>{{
                                    trans('reports.audit.actor')
                                }}</TableHead>
                                <TableHead>{{
                                    trans('reports.audit.ticket')
                                }}</TableHead>
                                <TableHead>{{
                                    trans('reports.audit.branch')
                                }}</TableHead>
                                <TableHead>{{
                                    trans('reports.audit.department')
                                }}</TableHead>
                                <TableHead>{{
                                    trans('reports.audit.metadata')
                                }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="row in audit.rows" :key="row.id">
                                <TableCell class="whitespace-nowrap">
                                    {{ row.occurredAt ?? '-' }}
                                </TableCell>
                                <TableCell class="whitespace-nowrap">
                                    {{ row.event }}
                                </TableCell>
                                <TableCell>{{
                                    row.actorName ?? '-'
                                }}</TableCell>
                                <TableCell>
                                    <div class="font-medium">
                                        {{ row.ticketNumber ?? '-' }}
                                    </div>
                                    <div
                                        class="max-w-[240px] truncate text-xs text-muted-foreground"
                                    >
                                        {{ row.ticketSubject ?? '-' }}
                                    </div>
                                </TableCell>
                                <TableCell>{{
                                    row.branchName ?? '-'
                                }}</TableCell>
                                <TableCell>{{
                                    row.departmentName ?? '-'
                                }}</TableCell>
                                <TableCell class="max-w-[260px] truncate">
                                    {{ metadataText(row.metadata) }}
                                </TableCell>
                            </TableRow>
                            <TableEmpty
                                v-if="audit.rows.length === 0"
                                :colspan="7"
                            >
                                {{ trans('reports.audit.empty') }}
                            </TableEmpty>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-muted-foreground">
                        {{
                            trans('reports.audit.page', {
                                current: audit.meta.currentPage,
                                last: audit.meta.lastPage,
                            })
                        }}
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="audit.meta.currentPage <= 1"
                            @click="visit(filters, audit.meta.currentPage - 1)"
                        >
                            {{ trans('reports.audit.previous') }}
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="
                                audit.meta.currentPage >= audit.meta.lastPage
                            "
                            @click="visit(filters, audit.meta.currentPage + 1)"
                        >
                            {{ trans('reports.audit.next') }}
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
