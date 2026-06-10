<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { DataTable, DeferredDataTablePage } from '@/components/datatable';
import { useDeferredDataTableState } from '@/composables/useDataTableState';
import { useTrans } from '@/composables/useTrans';
import TicketTableActions from '@/pages/tickets/TicketTableActions.vue';
import { ticketTableColumns } from '@/pages/tickets/ticketTableColumns';
import { dashboard } from '@/routes';
import { index } from '@/routes/tickets';
import { EMPTY_TICKET_TABLE_FILTERS } from '@/types';
import type {
    SharedPageProps,
    TicketTableFilters,
    TicketTablePayload,
} from '@/types';

defineOptions({ inheritAttrs: false });

type Props = {
    table?: TicketTablePayload;
};

const props = defineProps<Props>();

const { trans } = useTrans();
const page = usePage<SharedPageProps>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: trans('helpdesk.ticket.index.title'),
            href: index(),
        },
    ],
});

const columns = computed(() => ticketTableColumns());

const tableState = useDeferredDataTableState<TicketTableFilters>({
    table: () => props.table,
    emptyFilters: EMPTY_TICKET_TABLE_FILTERS,
    route: (options) => index.url(options as never),
});

const {
    filters: tableFilters,
    filterDefinitions,
    setPage,
    setPerPage,
    setSorting,
    sorting,
    updateFilter,
} = tableState;
</script>

<template>
    <Head :title="trans('helpdesk.ticket.index.title')" />

    <DeferredDataTablePage
        data="table"
        :title="trans('helpdesk.ticket.index.heading')"
        :description="trans('helpdesk.ticket.index.description')"
    >
        <DataTable
            :columns="columns"
            :data="table?.rows ?? []"
            :filter-definitions="table?.schema.filters ?? filterDefinitions"
            :filter-values="tableFilters"
            :meta="table?.meta"
            :per-page-options="table?.state?.perPageOptions"
            :sorting="sorting"
            :get-row-id="(row) => row.id"
            @filter-change="
                ({ key, value }) =>
                    updateFilter(key as keyof TicketTableFilters, value)
            "
            @page-change="setPage"
            @per-page-change="setPerPage"
            @sorting-change="setSorting"
        >
            <template #actions="{ selectedRows }">
                <TicketTableActions
                    :can-create="page.props.auth.abilities.create_tickets"
                    :has-selection="selectedRows.length > 0"
                />
            </template>
        </DataTable>
    </DeferredDataTablePage>
</template>
