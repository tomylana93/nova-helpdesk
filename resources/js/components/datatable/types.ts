export type DataTableFilterOption = {
    value: string;
    label: string;
};

export type DataTableFilterKind = 'select' | 'command';

export type DataTableFilterField = {
    key: string;
    kind: DataTableFilterKind;
    queryKey: string;
    options: DataTableFilterOption[];
    allLabel: string;
    placeholder?: string;
    searchPlaceholder?: string;
};

export type DataTableFiltersConfig = {
    fields: DataTableFilterField[];
};

export type DataTableFilterState = Record<string, string>;

export type DataTableFilterKey = string;

export type Pagination = {
    current_page: number;
    per_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
};