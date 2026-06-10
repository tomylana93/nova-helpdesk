import type { DataTablePayload } from '@/types';

export type BranchTableRow = {
    id: string;
    code: string;
    name: string;
    status: 'active' | 'inactive';
    statusLabel: string;
    createdAt: string;
};

export type BranchTableFilters = {
    search?: string | null;
    status?: 'active' | 'inactive' | null;
};

export const EMPTY_BRANCH_TABLE_FILTERS: BranchTableFilters = {
    search: null,
    status: null,
};

export type BranchTablePayload = DataTablePayload<
    BranchTableRow,
    BranchTableFilters
>;
