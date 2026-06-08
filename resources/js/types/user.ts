import type { DataTablePayload, UserRoleName, UserStatus } from '@/types';

export type UserTableRow = {
    id: string;
    name: string;
    email: string;
    role: UserRoleName | null;
    roleLabel: string | null;
    status: UserStatus;
    statusLabel: string;
};

export type UserTableFilters = {
    search?: string | null;
    status?: UserStatus | null;
};

export const EMPTY_USER_TABLE_FILTERS: UserTableFilters = {
    search: null,
    status: null,
};

export type UserTablePayload = DataTablePayload<UserTableRow, UserTableFilters>;
