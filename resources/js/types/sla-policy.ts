import type { DataTablePayload } from '@/types';
import type { TicketPriority, TicketType } from '@/types';

export type SlaPolicy = {
    id: string;
    name: string;
    ticket_type: TicketType | null;
    ticketTypeLabel: string | null;
    priority: TicketPriority;
    priorityLabel: string;
    queue_id: string | null;
    queueName: string | null;
    first_response_target_minutes: number;
    resolution_target_minutes: number;
    is_active: boolean;
    created_at: string | null;
    updated_at: string | null;
};

export type SlaPolicyTableRow = {
    id: string;
    name: string;
    ticketType: TicketType | null;
    ticketTypeLabel: string;
    priority: TicketPriority;
    priorityLabel: string;
    queueName: string | null;
    firstResponseTargetMinutes: number;
    resolutionTargetMinutes: number;
    isActive: boolean;
    createdAt: string | null;
};

export type SlaPolicyTableFilters = {
    search?: string | null;
    ticket_type?: TicketType | null;
    priority?: TicketPriority | null;
};

export const EMPTY_SLA_POLICY_TABLE_FILTERS: SlaPolicyTableFilters = {
    search: null,
    ticket_type: null,
    priority: null,
};

export type SlaPolicyTablePayload = DataTablePayload<
    SlaPolicyTableRow,
    SlaPolicyTableFilters
>;
