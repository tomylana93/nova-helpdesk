import type { DashboardPeriodProp } from './dashboard';
import type { TicketPriority, TicketStatus, TicketType } from './ticket';

export type ReportFilters = DashboardPeriodProp & {
    branch_id: string | null;
    department_id: string | null;
    category_id: string | null;
    assignee_id: string | null;
    status: TicketStatus | null;
    priority: TicketPriority | null;
    type: TicketType | null;
    event: string | null;
};

export type ReportOption<T extends string = string> = {
    value: T;
    label: string;
    branch_id?: string;
    parent_id?: string | null;
    parent_name?: string | null;
};

export type ReportSummary = {
    created: number;
    resolved: number;
    active: number;
    overdue: number;
    complianceRate: number;
    resolvedWithinDue: number;
    totalResolved: number;
};

export type ReportBreakdownSegment = {
    key: string;
    label: string;
    value: number;
};

export type ReportAuditRow = {
    id: string;
    occurredAt: string | null;
    event: string;
    actorName: string | null;
    ticketNumber: string | null;
    ticketSubject: string | null;
    ticketStatus: TicketStatus | null;
    branchName: string | null;
    departmentName: string | null;
    metadata: Record<string, unknown> | null;
};

export type ReportsProps = {
    filters: ReportFilters;
    options: {
        branches: ReportOption[];
        departments: ReportOption[];
        categories: { label: string; options: ReportOption[] }[];
        assignees: ReportOption[];
        statuses: ReportOption<TicketStatus>[];
        priorities: ReportOption<TicketPriority>[];
        types: ReportOption<TicketType>[];
    };
    summary: ReportSummary;
    breakdowns: Record<string, ReportBreakdownSegment[]>;
    audit: {
        rows: ReportAuditRow[];
        meta: {
            currentPage: number;
            lastPage: number;
            perPage: number;
            total: number;
        };
        events: ReportOption[];
    };
};
