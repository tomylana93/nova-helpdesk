import type { DataTablePayload } from '@/types';

export type TicketCategory = {
    id: string;
    parent_id?: string | null;
    parentName?: string | null;
    name: string;
    description?: string | null;
    status: 'active' | 'inactive';
    statusLabel?: string;
    created_at?: string;
    updated_at?: string;
};

export type TicketCategoryTableRow = {
    id: string;
    parentId: string | null;
    parentName: string | null;
    name: string;
    description: string | null;
    status: 'active' | 'inactive';
    statusLabel: string;
    createdAt: string;
};

export type TicketCategoryTableFilters = {
    search?: string | null;
    parent_id?: string | null;
    status?: 'active' | 'inactive' | null;
};

export const EMPTY_TICKET_CATEGORY_TABLE_FILTERS: TicketCategoryTableFilters = {
    search: null,
    parent_id: null,
    status: null,
};

export type TicketCategoryTablePayload = DataTablePayload<
    TicketCategoryTableRow,
    TicketCategoryTableFilters
>;
