import type { DataTablePayload, UserRoleName, UserStatus } from '@/types';

export type UserTableRow = {
    id: string;
    name: string;
    email: string;
    phone: string | null;
    role: UserRoleName | null;
    roleLabel: string | null;
    branchName: string | null;
    departmentName: string | null;
    status: UserStatus;
    statusLabel: string;
    lastLoginAt?: string | null;
};

export type UserTableFilters = {
    search?: string | null;
    status?: UserStatus | null;
    role?: string | null;
    branch_id?: string | null;
    department_id?: string | null;
};

export const EMPTY_USER_TABLE_FILTERS: UserTableFilters = {
    search: null,
    status: null,
    role: null,
    branch_id: null,
    department_id: null,
};

export type UserTablePayload = DataTablePayload<UserTableRow, UserTableFilters>;
