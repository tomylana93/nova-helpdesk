<script setup lang="ts" generic="TData, TValue">
import { router } from '@inertiajs/vue3';
import type {
    ColumnDef,
    ColumnFiltersState,
    ExpandedState,
    SortingState,
    VisibilityState,
} from '@tanstack/vue-table';
import {
    FlexRender,
    getCoreRowModel,
    getExpandedRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';

import { DataTablePagination, DataTableToolbar } from '@/components/datatable';
import type {
    DataTableFilterKey,
    DataTableFiltersConfig,
    DataTableFilterState,
    Pagination
} from '@/components/datatable/types';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { debounce, valueUpdater } from '@/lib/utils';

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    pagination: Pagination;
    route: string;
    exportRoute?: string;
    filters?: DataTableFiltersConfig;
}>();

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const globalFilter = ref<string>('');
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref({});
const expanded = ref<ExpandedState>({});
const tableError = ref<string | null>(null);
const exportError = ref<string | null>(null);
const pagination = ref({
    pageIndex: props.pagination.current_page - 1,
    pageSize: props.pagination.per_page,
});

const selectFilters = ref<DataTableFilterState>({});

const getCurrentQueryParams = (): Record<string, any> => {
    const params: Record<string, any> = {};

    if (typeof window === 'undefined') {
        return params;
    }

    const searchParams = new URLSearchParams(window.location.search);
    for (const [key, value] of searchParams.entries()) {
        params[key] = value;
    }

    return params;
};

const initialQueryParams = getCurrentQueryParams();

if (typeof initialQueryParams['filter[global]'] === 'string') {
    globalFilter.value = initialQueryParams['filter[global]'];
}

if (typeof initialQueryParams.page === 'string') {
    const pageNumber = Number(initialQueryParams.page);
    if (Number.isFinite(pageNumber) && pageNumber > 0) {
        pagination.value.pageIndex = pageNumber - 1;
    }
}

if (typeof initialQueryParams.per_page === 'string') {
    const perPage = Number(initialQueryParams.per_page);
    if (Number.isFinite(perPage) && perPage > 0) {
        pagination.value.pageSize = perPage;
    }
}

if (typeof initialQueryParams.sort === 'string' && initialQueryParams.sort) {
    sorting.value = initialQueryParams.sort
        .split(',')
        .filter(Boolean)
        .map((sortKey: string) => {
            if (sortKey.startsWith('-')) {
                return { id: sortKey.slice(1), desc: true };
            }

            return { id: sortKey, desc: false };
        });
}

if (props.filters) {
    for (const field of props.filters.fields) {
        const raw = initialQueryParams[field.queryKey];
        selectFilters.value[field.key] = typeof raw === 'string' ? raw : '';
    }
}

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },

    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    manualPagination: true,
    manualSorting: true,
    get pageCount() {
        return props.pagination.last_page;
    },
    getExpandedRowModel: getExpandedRowModel(),
    onSortingChange: (updaterOrValue) => {
        valueUpdater(updaterOrValue, sorting);
        fetchData();
    },
    onPaginationChange: (updater) => {
        if (typeof updater === 'function') {
            pagination.value = updater(pagination.value);
        } else {
            pagination.value = updater;
        }
        fetchData();
    },
    onGlobalFilterChange: (updaterOrValue) => {
        valueUpdater(updaterOrValue, globalFilter);
        pagination.value.pageIndex = 0;
        fetchData();
    },
    onColumnFiltersChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, rowSelection),
    onExpandedChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, expanded),
    state: {
        get sorting() {
            return sorting.value;
        },
        get pagination() {
            return pagination.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get globalFilter() {
            return globalFilter.value;
        },
        get columnVisibility() {
            return columnVisibility.value;
        },
        get rowSelection() {
            return rowSelection.value;
        },
        get expanded() {
            return expanded.value;
        },
    },
    initialState: {
        pagination: {
            pageSize: props.pagination.per_page,
        },
    },
});

const fetchData = debounce(() => {
    const params: Record<string, any> = {
        ...getCurrentQueryParams(),
        page: pagination.value.pageIndex + 1,
        per_page: pagination.value.pageSize,
    };

    const route = props.route.split('?')[0];

    if (globalFilter.value) {
        params['filter[global]'] = globalFilter.value;
    } else {
        delete params['filter[global]'];
    }

    if (sorting.value.length > 0) {
        const sortParam = sorting.value
            .map((sort) => (sort.desc ? `-${sort.id}` : sort.id))
            .join(',');
        params.sort = sortParam;
    } else {
        delete params.sort;
    }

    const setOrDelete = (key: string, value: string) => {
        if (value) {
            params[key] = value;

            return;
        }

        delete params[key];
    };

    if (props.filters) {
        for (const field of props.filters.fields) {
            setOrDelete(field.queryKey, selectFilters.value[field.key] ?? '');
        }
    }

    router.get(route, params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            tableError.value = null;
        },
        onError: () => {
            tableError.value = trans('ui.datatable.error.load_failed');
        },
    });
}, 300);

const updateSelectFilter = (key: DataTableFilterKey, value: string) => {
    selectFilters.value[key] = value;
    pagination.value.pageIndex = 0;
    fetchData();
};

const buildQueryParamsForExport = (): Record<string, any> => {
    const params: Record<string, any> = {
        ...getCurrentQueryParams(),
    };

    if (globalFilter.value) {
        params['filter[global]'] = globalFilter.value;
    } else {
        delete params['filter[global]'];
    }

    if (sorting.value.length > 0) {
        const sortParam = sorting.value
            .map((sort) => (sort.desc ? `-${sort.id}` : sort.id))
            .join(',');
        params.sort = sortParam;
    } else {
        delete params.sort;
    }

    const setOrDelete = (key: string, value: string) => {
        if (value) {
            params[key] = value;

            return;
        }

        delete params[key];
    };

    if (props.filters) {
        for (const field of props.filters.fields) {
            setOrDelete(field.queryKey, selectFilters.value[field.key] ?? '');
        }
    }

    delete params.page;
    delete params.per_page;

    return params;
};

const getCookieValue = (name: string): string | null => {
    if (typeof document === 'undefined') {
        return null;
    }

    const cookies = document.cookie ? document.cookie.split('; ') : [];

    for (const cookie of cookies) {
        const index = cookie.indexOf('=');
        const key = index >= 0 ? cookie.slice(0, index) : cookie;
        const value = index >= 0 ? cookie.slice(index + 1) : '';

        if (key === name) {
            return value;
        }
    }

    return null;
};

const getCsrfToken = (): { headerToken: string; xsrfToken: string } => {
    const headerToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';

    const xsrfCookie = getCookieValue('XSRF-TOKEN');
    const xsrfToken = xsrfCookie ? decodeURIComponent(xsrfCookie) : '';

    return { headerToken, xsrfToken };
};

const parseExportErrorMessage = async (response: Response): Promise<string> => {
    try {
        const payload = (await response.json()) as {
            message?: string;
            errors?: Record<string, string[]>;
        };

        if (typeof payload.message === 'string' && payload.message.length > 0) {
            return payload.message;
        }

        const firstError = payload.errors
            ? Object.values(payload.errors)[0]?.[0]
            : null;

        if (typeof firstError === 'string' && firstError.length > 0) {
            return firstError;
        }
    } catch {
        // Ignore JSON parse errors and fallback to text response.
    }

    const message = await response.text().catch(() => '');

    return message || trans('ui.datatable.error.export_failed');
};

const handleExport = async (format: 'excel') => {
    if (!props.exportRoute) {
        return;
    }

    exportError.value = null;

    const exportAllFilteredRows = table.getIsAllPageRowsSelected();

    const selectedIds = exportAllFilteredRows
        ? []
        : table
              .getSelectedRowModel()
              .rows.map((row: any) => row.original)
              .map((original: any) => {
                  const key =
                      original?.id ??
                      original?.code ??
                      original?.code_alpha_3 ??
                      original?.code_alpha_2;

                  return key === undefined || key === null ? null : String(key);
              })
              .filter((id: string | null): id is string => Boolean(id));

    try {
        const { headerToken, xsrfToken } = getCsrfToken();

        const response = await fetch(props.exportRoute, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': headerToken,
                'X-XSRF-TOKEN': xsrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                ids: selectedIds,
                format: format,
                query: buildQueryParamsForExport(),
            }),
        });

        if (!response.ok) {
            const message = await parseExportErrorMessage(response);
            throw new Error(message);
        }

        const blob = await response.blob();

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;

        const contentDisposition = response.headers.get('Content-Disposition');
        const filename =
            contentDisposition?.split('filename=')[1]?.replace(/"/g, '') ||
            `export_${new Date().getTime()}.xlsx`;

        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        table.resetRowSelection();
    } catch (error) {
        console.error('Export error:', error);
        exportError.value =
            error instanceof Error && error.message.length > 0
                ? error.message
                : trans('ui.datatable.error.export_failed');
    }
};
</script>

<template>
    <div class="space-y-4">
        <div
            v-if="tableError"
            class="flex items-center justify-between rounded-md border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive"
        >
            <span>{{ tableError }}</span>
            <button
                type="button"
                class="underline underline-offset-4"
                @click="fetchData()"
            >
                {{ trans('ui.datatable.actions.retry') }}
            </button>
        </div>
        <div
            v-if="exportError"
            class="flex items-center justify-between rounded-md border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive"
        >
            <span>{{ exportError }}</span>
            <button
                type="button"
                class="underline underline-offset-4"
                @click="exportError = null"
            >
                {{ trans('ui.common.close') }}
            </button>
        </div>
        <DataTableToolbar
            :table="table"
            :filters="props.filters"
            :filter-state="props.filters ? selectFilters : undefined"
            :can-export="Boolean(props.exportRoute)"
            @export="handleExport"
            @update-filter="updateSelectFilter"
        />
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                        >
                            <template v-if="!header.isPlaceholder">
                                <span
                                    v-if="
                                        typeof header.column.columnDef
                                            .header === 'string'
                                    "
                                >
                                    {{ header.column.columnDef.header }}
                                </span>
                                <FlexRender
                                    v-else
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                            </template>
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            :data-state="
                                row.getIsSelected() ? 'selected' : undefined
                            "
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell
                                :colspan="columns.length"
                                class="h-28 text-center"
                            >
                                <div class="space-y-1">
                                    <p class="font-medium">
                                        {{
                                            trans(
                                                'ui.datatable.empty.title',
                                            )
                                        }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{
                                            trans(
                                                'ui.datatable.empty.description',
                                            )
                                        }}
                                    </p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>
        <DataTablePagination :table="table" />
    </div>
</template>