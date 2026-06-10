import type { DataTablePayload } from '@/types';

export type DepartmentTableRow = {
    id: string;
    branchId: string | null;
    branchName: string | null;
    code: string;
    name: string;
    status: 'active' | 'inactive';
    statusLabel: string;
    createdAt: string;
};

export type DepartmentTableFilters = {
    search?: string | null;
    branch_id?: string | null;
    status?: 'active' | 'inactive' | null;
};

export const EMPTY_DEPARTMENT_TABLE_FILTERS: DepartmentTableFilters = {
    search: null,
    branch_id: null,
    status: null,
};

export type DepartmentTablePayload = DataTablePayload<
    DepartmentTableRow,
    DepartmentTableFilters
>;
