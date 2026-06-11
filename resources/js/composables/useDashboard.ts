import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import type { DashboardPeriodProp } from '@/types';

export function useDashboard() {
    const { trans, locale } = useTrans();

    /** Human label for the active period, e.g. "June 2026" or "2026". */
    function periodLabel(period: DashboardPeriodProp): string {
        if (period.mode === 'yearly') {
            return String(period.year);
        }

        const date = new Date(period.year, (period.month ?? 1) - 1, 1);

        return date.toLocaleDateString(locale.value, {
            month: 'long',
            year: 'numeric',
        });
    }

    /** Human label for the comparison period (previous month/year). */
    function previousPeriodLabel(period: DashboardPeriodProp): string {
        if (period.mode === 'yearly') {
            return String(period.year - 1);
        }

        const prev = new Date(period.year, (period.month ?? 1) - 2, 1);

        return prev.toLocaleDateString(locale.value, {
            month: 'long',
            year: 'numeric',
        });
    }

    /** Wayfinder URL preserving the period query for instant Inertia visits. */
    function periodUrl(period: DashboardPeriodProp): string {
        const query: Record<string, string> =
            period.mode === 'yearly'
                ? { mode: 'yearly', year: String(period.year) }
                : {
                      mode: 'monthly',
                      month: String(period.month ?? 1),
                      year: String(period.year),
                  };

        return dashboard({ query }).url;
    }

    /** Trend x-axis tick: short month name (yearly) or day number (monthly). */
    function trendTick(granularity: 'day' | 'month', label: string): string {
        if (granularity === 'month') {
            const date = new Date(2000, Number(label) - 1, 1);

            return date.toLocaleDateString(locale.value, { month: 'short' });
        }

        return String(Number(label));
    }

    return {
        trans,
        locale,
        periodLabel,
        previousPeriodLabel,
        periodUrl,
        trendTick,
    };
}

/** Years available in the year selector: current year back to 2023. */
export function availableYears(currentYear: number): number[] {
    const years: number[] = [];

    for (let y = currentYear; y >= 2023; y--) {
        years.push(y);
    }

    return years;
}
