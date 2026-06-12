import type { DataTablePayload } from '@/types';

export type Asset = {
    id: string;
    asset_tag: string;
    name: string;
    category: string;
    categoryLabel: string;
    status: string;
    statusLabel: string;
    statusVariant: string;
    serial_number?: string | null;
    model?: string | null;
    manufacturer?: string | null;
    purchase_date?: string | null;
    branch_id?: string | null;
    user_id?: string | null;
    branch?: {
        id: string;
        name: string;
        [key: string]: unknown;
    } | null;
    user?: {
        id: string;
        name: string;
        email?: string;
        [key: string]: unknown;
    } | null;
    created_at?: string;
    updated_at?: string;
};

export type AssetTableRow = {
    id: string;
    assetTag: string;
    name: string;
    category: string;
    categoryLabel: string;
    status: string;
    statusLabel: string;
    statusVariant: string;
    branchName?: string | null;
    userName?: string | null;
    createdAt: string;
};

export type AssetTableFilters = {
    search?: string | null;
    category?: string | null;
    status?: string | null;
    branch_id?: string | null;
};

export const EMPTY_ASSET_TABLE_FILTERS: AssetTableFilters = {
    search: null,
    category: null,
    status: null,
    branch_id: null,
};

export type AssetTablePayload = DataTablePayload<
    AssetTableRow,
    AssetTableFilters
>;
