# Frontend / DataTable

- Backend: `app/Tables/AbstractTable` with `spatie/laravel-query-builder` integration; subclasses define `query()`, `rows()`, `filterConfigurations()`, `allowedSorts()`, `defaultSort()`, `perPageOptions()`, `defaultPerPage()`, `maxPerPage()`.
- Data served via Inertia deferred props for non-blocking table rendering.
- Frontend components in `resources/js/components/datatable/`: `DataTable`, `DataTableColumnHeader`, `DataTableFilters`, `DataTablePagination`, `DataTableSelectFilter`, `DataTableToolbar`, `DataTableViewOptions`, `DeferredDataTablePage`.
- `useDataTableState` composable manages URL-synced query state: pagination, sorting, filters, per-page, global search, with next-page prefetch support via Inertia `cacheFor`.
- Types in `resources/js/types/datatable.ts`: `DataTableFilterDefinition`, `DataTableMeta`, `DataTablePayload`.
