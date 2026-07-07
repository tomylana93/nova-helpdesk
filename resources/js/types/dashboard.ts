import type { Asset } from './asset';

export type DashboardRole =
    'requester' | 'it_agent' | 'auditor' | 'super_admin';

export interface DashboardPeriodProp {
    mode: 'monthly' | 'yearly';
    month: number | null;
    year: number;
}

export interface DashboardLiveMetric {
    key:
        | 'active'
        | 'assigned'
        | 'unassigned'
        | 'pending_approval'
        | 'sla_breached';
    value: number;
}

export interface DashboardPeriodMetric {
    key: 'created' | 'resolved';
    value: number;
    previous: number;
    deltaPercent: number | null;
    direction: 'up' | 'down' | 'flat';
    sentiment: 'higher_is_better' | 'lower_is_better' | 'neutral';
}

export interface DashboardCompliance {
    rate: number;
    resolvedWithinDue: number;
    totalResolved: number;
    previousRate: number;
    deltaPercent: number | null;
    direction: 'up' | 'down' | 'flat';
}

export interface DashboardTrendPoint {
    label: string;
    created: number;
    resolved: number;
}

export interface DashboardBreakdownSegment {
    key: string;
    value: number;
}

export interface DashboardProps {
    role: DashboardRole;
    period: DashboardPeriodProp;
    live: DashboardLiveMetric[];
    periodMetrics: DashboardPeriodMetric[];
    compliance: DashboardCompliance | null;
    trend: {
        granularity: 'day' | 'month';
        points: DashboardTrendPoint[];
    };
    breakdown: {
        type: 'priority' | 'status';
        segments: DashboardBreakdownSegment[];
    };
    myAssets?: Asset[];
}
