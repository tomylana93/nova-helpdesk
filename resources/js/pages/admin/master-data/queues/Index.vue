<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { DataTable, DeferredDataTablePage } from '@/components/datatable';
import { useDeferredDataTableState } from '@/composables/useDataTableState';
import { useTrans } from '@/composables/useTrans';
import QueueTableActions from '@/pages/admin/master-data/queues/QueueTableActions.vue';
import { queueTableColumns } from '@/pages/admin/master-data/queues/queueTableColumns';
import { dashboard } from '@/routes';
import { index as adminMasterDataIndex } from '@/routes/admin/master-data';
import { index } from '@/routes/admin/master-data/queues';
import { EMPTY_QUEUE_TABLE_FILTERS } from '@/types';
import type {
    SharedPageProps,
    QueueTableFilters,
    QueueTablePayload,
} from '@/types';

defineOptions({ inheritAttrs: false });

type Props = {
    table?: QueueTablePayload;
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
            title: trans('admin.master_data.title'),
            href: adminMasterDataIndex(),
        },
        {
            title: trans('admin.master_data.queue.index.title'),
            href: index(),
        },
    ],
});

const columns = computed(() => queueTableColumns());

const tableState = useDeferredDataTableState<QueueTableFilters>({
    table: () => props.table,
    emptyFilters: EMPTY_QUEUE_TABLE_FILTERS,
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
    <Head :title="trans('admin.master_data.queue.index.title')" />

    <DeferredDataTablePage
        data="table"
        :title="trans('admin.master_data.queue.index.heading')"
        :description="trans('admin.master_data.queue.index.description')"
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
                    updateFilter(key as keyof QueueTableFilters, value)
            "
            @page-change="setPage"
            @per-page-change="setPerPage"
            @sorting-change="setSorting"
        >
            <template #actions="{ selectedRows }">
                <QueueTableActions
                    :can-create="page.props.auth.abilities.manage_queues"
                    :has-selection="selectedRows.length > 0"
                />
            </template>
        </DataTable>
    </DeferredDataTablePage>
</template>
