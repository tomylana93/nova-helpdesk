import { router } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { SortingState } from '@tanstack/vue-table';
import { reactive, shallowRef, watch } from 'vue';
import type {
    DataTableFilterDefinition,
    DataTableMeta,
    DataTablePayload,
} from '@/types';

type QueryParamValue =
    | string
    | number
    | boolean
    | null
    | undefined
    | QueryParamValue[]
    | { [key: string]: QueryParamValue };

type QueryParams = Record<string, QueryParamValue>;
type VisitRoute = (options?: {
    query?: QueryParams;
}) => Parameters<typeof router.get>[0];

type NextPagePrefetchContext = {
    currentPage: number;
    nextPage: number;
    query: QueryParams;
};

type NextPagePrefetchOptions = {
    cacheFor?: InertiaLinkProps['cacheFor'];
    cacheTags?: InertiaLinkProps['cacheTags'];
    nextPage?: () => number | null;
    only?: string[];
    shouldPrefetch?: (context: NextPagePrefetchContext) => boolean;
};

type PageResolver = number | (() => number);

type UseDataTableStateOptions<TFilters extends Record<string, unknown>> = {
    filterDefinitions?: DataTableFilterDefinition[];
    currentPage: number;
    initialFilters: TFilters;
    initialPerPage: number;
    initialSort: string | null;
    lastPage?: PageResolver;
    minimumSearchLength?: number;
    only?: string[];
    prefetchNextPage?: NextPagePrefetchOptions;
    route: VisitRoute;
    searchDebounce?: number;
};

type DeferredDataTable<TFilters extends Record<string, unknown>> = Pick<
    DataTablePayload<unknown, TFilters>,
    'meta' | 'schema' | 'state'
>;

type UseDeferredDataTableStateOptions<
    TFilters extends Record<string, unknown>,
> = Omit<
    UseDataTableStateOptions<TFilters>,
    | 'currentPage'
    | 'initialFilters'
    | 'initialPerPage'
    | 'initialSort'
    | 'lastPage'
> & {
    emptyFilters: TFilters;
    table?:
        | DeferredDataTable<TFilters>
        | (() => DeferredDataTable<TFilters> | undefined);
};

export const DEFAULT_DATA_TABLE_META: Readonly<DataTableMeta> = Object.freeze({
    currentPage: 1,
    from: null,
    lastPage: 1,
    perPage: 10,
    to: null,
    total: 0,
});

export const DEFAULT_DATA_TABLE_PER_PAGE_OPTIONS: number[] = [10, 25, 50];

export function useDeferredDataTableState<
    TFilters extends Record<string, unknown>,
>(options: UseDeferredDataTableStateOptions<TFilters>) {
    const { emptyFilters, table, ...stateOptions } = options;
    const resolvedTable = typeof table === 'function' ? table : () => table;
    const initialTable = resolvedTable();

    const tableState = useDataTableState({
        ...stateOptions,
        filterDefinitions: initialTable?.schema.filters ?? [],
        currentPage:
            initialTable?.meta.currentPage ??
            DEFAULT_DATA_TABLE_META.currentPage,
        initialFilters: initialTable?.state.filters ?? emptyFilters,
        initialPerPage:
            initialTable?.state.perPage ?? DEFAULT_DATA_TABLE_META.perPage,
        initialSort: initialTable?.state.sort ?? '',
        lastPage: () =>
            resolvedTable()?.meta.lastPage ?? DEFAULT_DATA_TABLE_META.lastPage,
    });

    watch(
        resolvedTable,
        (nextTable) => {
            tableState.syncState(nextTable);
        },
        { deep: true, immediate: true },
    );

    return tableState;
}

export function useDataTableState<TFilters extends Record<string, unknown>>(
    options: UseDataTableStateOptions<TFilters>,
) {
    const currentPage = shallowRef(options.currentPage);
    const filterDefinitions = shallowRef(options.filterDefinitions ?? []);
    const only = options.only ?? ['table'];
    const perPage = shallowRef(options.initialPerPage);
    const sort = shallowRef(options.initialSort ?? '');
    const filters = reactive({
        ...options.initialFilters,
    }) as TFilters;
    const sorting = shallowRef<SortingState>(
        sort.value !== ''
            ? [
                  {
                      id: sort.value.replace(/^-/, ''),
                      desc: sort.value.startsWith('-'),
                  },
              ]
            : [],
    );
    let searchTimeout: ReturnType<typeof setTimeout> | null = null;

    function searchDefinition():
        Extract<DataTableFilterDefinition, { type: 'search' }> | undefined {
        return filterDefinitions.value.find(
            (
                definition,
            ): definition is Extract<
                DataTableFilterDefinition,
                { type: 'search' }
            > => definition.type === 'search',
        );
    }

    function minimumSearchLength(): number {
        return (
            searchDefinition()?.minimumSearchLength ??
            options.minimumSearchLength ??
            3
        );
    }

    function replaceFilters(nextFilters: TFilters): void {
        for (const key of Object.keys(filters)) {
            if (!Object.prototype.hasOwnProperty.call(nextFilters, key)) {
                delete filters[key as keyof TFilters];
            }
        }

        Object.assign(filters, nextFilters);
    }

    function normalizedFilters(): QueryParams {
        return Object.entries(filters).reduce<QueryParams>(
            (carry, [key, value]) => {
                if (value === null || value === undefined || value === '') {
                    return carry;
                }

                const definition = filterDefinitions.value.find(
                    (item) => item.key === key,
                );

                if (definition?.type === 'search') {
                    const normalized = String(value).trim();

                    if (normalized.length >= minimumSearchLength()) {
                        carry[key] = normalized;
                    }

                    return carry;
                }

                carry[key] = value as QueryParams[string];

                return carry;
            },
            {},
        );
    }

    function syncState(
        nextTable: DeferredDataTable<TFilters> | undefined,
    ): void {
        if (nextTable === undefined) {
            return;
        }

        filterDefinitions.value = nextTable.schema.filters;
        currentPage.value = nextTable.meta.currentPage;
        perPage.value = nextTable.state.perPage;
        sort.value = nextTable.state.sort ?? '';
        replaceFilters(nextTable.state.filters);
        sorting.value =
            sort.value !== ''
                ? [
                      {
                          id: sort.value.replace(/^-/, ''),
                          desc: sort.value.startsWith('-'),
                      },
                  ]
                : [];
    }

    function currentQuery(page: number = currentPage.value): QueryParams {
        const query: QueryParams = {
            page,
            per_page: perPage.value,
        };

        if (sort.value !== '') {
            query.sort = sort.value;
        }

        const activeFilters = normalizedFilters();

        if (Object.keys(activeFilters).length > 0) {
            query.filter = activeFilters;
        }

        return query;
    }

    function resolvedPage(page: PageResolver): number {
        return typeof page === 'function' ? page() : page;
    }

    function defaultNextPage(): number | null {
        if (options.lastPage === undefined) {
            return null;
        }

        return currentPage.value < resolvedPage(options.lastPage)
            ? currentPage.value + 1
            : null;
    }

    function prefetchNextPage(): void {
        if (typeof window === 'undefined' || !options.prefetchNextPage) {
            return;
        }

        const nextPage =
            options.prefetchNextPage.nextPage?.() ?? defaultNextPage();

        if (nextPage === null || nextPage <= currentPage.value) {
            return;
        }

        const query = currentQuery(nextPage);
        const context = {
            currentPage: currentPage.value,
            nextPage,
            query,
        };

        if (options.prefetchNextPage.shouldPrefetch?.(context) === false) {
            return;
        }

        const prefetchOptions = {
            ...(options.prefetchNextPage.cacheFor !== undefined
                ? { cacheFor: options.prefetchNextPage.cacheFor }
                : {}),
            ...(options.prefetchNextPage.cacheTags !== undefined
                ? { cacheTags: options.prefetchNextPage.cacheTags }
                : {}),
        };

        router.prefetch(
            options.route({ query }),
            {
                only: options.prefetchNextPage.only ?? only,
                preserveScroll: true,
                preserveState: true,
            },
            prefetchOptions,
        );
    }

    function visit(page: number = currentPage.value): void {
        currentPage.value = page;

        router.get(
            options.route({ query: currentQuery(page) }),
            {},
            {
                only,
                preserveScroll: true,
                preserveState: true,
                replace: true,
                onSuccess: () => prefetchNextPage(),
            },
        );
    }

    function setFilter<TKey extends keyof TFilters>(
        key: TKey,
        value: TFilters[TKey] | null | undefined,
    ): void {
        filters[key] = (
            (value ?? null) === '' ? null : (value ?? null)
        ) as TFilters[TKey];
        visit(1);
    }

    function updateFilter(key: keyof TFilters, value: unknown): void {
        const definition = filterDefinitions.value.find(
            (item) => item.key === key,
        );

        if (definition?.type !== 'search') {
            setFilter(
                key,
                value as TFilters[keyof TFilters] | null | undefined,
            );

            return;
        }

        const previousValue = String(filters[key] ?? '').trim();
        const normalized = String(value ?? '').trim();

        filters[key] = (
            normalized === '' ? null : normalized
        ) as TFilters[keyof TFilters];

        if (normalized === '') {
            if (searchTimeout !== null) {
                clearTimeout(searchTimeout);
                searchTimeout = null;
            }

            visit(1);

            return;
        }

        if (normalized.length >= minimumSearchLength()) {
            if (searchTimeout !== null) {
                clearTimeout(searchTimeout);
            }

            searchTimeout = setTimeout(() => {
                visit(1);
                searchTimeout = null;
            }, options.searchDebounce ?? 300);

            return;
        }

        filters[key] = null as TFilters[keyof TFilters];

        if (previousValue.length >= minimumSearchLength()) {
            if (searchTimeout !== null) {
                clearTimeout(searchTimeout);
                searchTimeout = null;
            }

            visit(1);
        }
    }

    function setPage(page: number): void {
        visit(page);
    }

    function setPerPage(value: number): void {
        perPage.value = value;
        visit(1);
    }

    function setSorting(nextSorting: SortingState): void {
        sorting.value = nextSorting;
        const first = nextSorting[0];

        sort.value = first ? `${first.desc ? '-' : ''}${first.id}` : '';
        visit(1);
    }

    watch(
        () => options.filterDefinitions,
        (definitions) => {
            filterDefinitions.value = definitions ?? [];
        },
    );

    return {
        currentPage,
        currentQuery,
        filterDefinitions,
        filters,
        perPage,
        setFilter,
        setPage,
        setPerPage,
        setSorting,
        sorting,
        syncState,
        updateFilter,
    };
}
