<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { AcceptableValue } from 'reka-ui';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { availableYears, useDashboard } from '@/composables/useDashboard';
import { useTrans } from '@/composables/useTrans';
import type { DashboardPeriodProp } from '@/types';

const props = defineProps<{ period: DashboardPeriodProp }>();

const { trans, locale } = useTrans();
const { periodUrl } = useDashboard();

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

function visit(next: DashboardPeriodProp): void {
    router.visit(periodUrl(next), {
        preserveScroll: true,
        preserveState: true,
        only: [
            'live',
            'periodMetrics',
            'compliance',
            'trend',
            'breakdown',
            'period',
        ],
    });
}

function setMode(mode: 'monthly' | 'yearly'): void {
    visit({
        mode,
        year: props.period.year,
        month:
            mode === 'monthly'
                ? (props.period.month ?? new Date().getMonth() + 1)
                : null,
    });
}

function setMonth(value: AcceptableValue | AcceptableValue[]): void {
    visit({ ...props.period, mode: 'monthly', month: Number(value) });
}

function setYear(value: AcceptableValue | AcceptableValue[]): void {
    visit({ ...props.period, year: Number(value) });
}
</script>

<template>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <div class="inline-flex rounded-lg border border-border/60 p-0.5">
            <Button
                v-for="mode in ['monthly', 'yearly'] as const"
                :key="mode"
                size="sm"
                :variant="period.mode === mode ? 'default' : 'ghost'"
                @click="setMode(mode)"
            >
                {{ trans(`dashboard.period.${mode}`) }}
            </Button>
        </div>

        <Select
            v-if="period.mode === 'monthly'"
            :model-value="String(period.month)"
            @update:model-value="setMonth"
        >
            <SelectTrigger class="w-full sm:w-[140px]">
                <SelectValue :placeholder="trans('dashboard.period.month')" />
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
            :model-value="String(period.year)"
            @update:model-value="setYear"
        >
            <SelectTrigger class="w-full sm:w-[100px]">
                <SelectValue :placeholder="trans('dashboard.period.year')" />
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
</template>
