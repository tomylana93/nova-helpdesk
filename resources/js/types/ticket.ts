import type { DataTablePayload } from '@/types';

export type TicketStatus =
    | 'open'
    | 'pending_approval'
    | 'in_progress'
    | 'waiting_for_requester'
    | 'resolved'
    | 'closed'
    | 'reopened';
export type TicketPriority = 'low' | 'medium' | 'high' | 'critical';
export type TicketType = 'incident' | 'service_request';

export type Ticket = {
    id: string;
    ticket_number: string;
    type: TicketType;
    typeLabel: string;
    subject: string;
    description: string;
    status: TicketStatus;
    statusLabel: string;
    statusVariant: string;
    priority: TicketPriority;
    priorityLabel: string;
    priorityVariant: string;
    branch_id: string | null;
    branchName: string | null;
    department_id: string | null;
    departmentName: string | null;
    requester_id: string;
    requesterName: string | null;
    requesterEmail: string | null;
    assigned_to: string | null;
    assigneeName: string | null;
    category_id: string | null;
    categoryName: string | null;
    submitted_at: string | null;
    resolved_at: string | null;
    closed_at: string | null;
    created_at: string;
    updated_at: string;
};

export type TicketTableRow = {
    id: string;
    ticketNumber: string;
    type: TicketType;
    typeLabel: string;
    subject: string;
    status: TicketStatus;
    statusLabel: string;
    statusVariant: string;
    priority: TicketPriority;
    priorityLabel: string;
    priorityVariant: string;
    requesterName: string | null;
    assigneeName: string | null;
    branchName: string | null;
    submittedAt: string | null;
    createdAt: string;
};

export type TicketTableFilters = {
    search?: string | null;
    status?: TicketStatus | null;
    type?: TicketType | null;
    priority?: TicketPriority | null;
};

export const EMPTY_TICKET_TABLE_FILTERS: TicketTableFilters = {
    search: null,
    status: null,
    type: null,
    priority: null,
};

export type TicketTablePayload = DataTablePayload<
    TicketTableRow,
    TicketTableFilters
>;
