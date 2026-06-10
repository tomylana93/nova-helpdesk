import type { DataTablePayload } from '@/types';

export type Queue = {
    id: string;
    name: string;
    description?: string | null;
    status: 'active' | 'inactive';
    statusLabel?: string;
    created_at?: string;
    updated_at?: string;
};

export type QueueTableRow = {
    id: string;
    name: string;
    description: string | null;
    status: 'active' | 'inactive';
    statusLabel: string;
    createdAt: string;
};

export type QueueTableFilters = {
    search?: string | null;
    status?: 'active' | 'inactive' | null;
};

export const EMPTY_QUEUE_TABLE_FILTERS: QueueTableFilters = {
    search: null,
    status: null,
};

export type QueueTablePayload = DataTablePayload<
    QueueTableRow,
    QueueTableFilters
>;
