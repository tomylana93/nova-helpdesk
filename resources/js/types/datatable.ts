import type { SelectOption } from './ui';

export type DataTableMeta = {
    currentPage: number;
    from: number | null;
    lastPage: number;
    perPage: number;
    to: number | null;
    total: number;
};

type DataTableBaseFilterDefinition = {
    key: string;
    label?: string;
    placeholder?: string;
};

type DataTableSearchFilterDefinition = DataTableBaseFilterDefinition & {
    type: 'search';
    minimumSearchLength?: number;
};

type DataTableSelectFilterDefinition = DataTableBaseFilterDefinition & {
    type: 'select';
    allLabel?: string;
    options: SelectOption[];
};

export type DataTableFilterDefinition =
    DataTableSearchFilterDefinition | DataTableSelectFilterDefinition;

type DataTableSchema = {
    filters: DataTableFilterDefinition[];
};

type DataTableState<TFilters extends Record<string, unknown>> = {
    filters: TFilters;
    perPage: number;
    perPageOptions: number[];
    sort: string | null;
};

export type DataTablePayload<TRow, TFilters extends Record<string, unknown>> = {
    meta: DataTableMeta;
    rows: TRow[];
    schema: DataTableSchema;
    state: DataTableState<TFilters>;
};
