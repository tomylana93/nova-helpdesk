<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { DataTable, DeferredDataTablePage } from '@/components/datatable';
import { useDeferredDataTableState } from '@/composables/useDataTableState';
import { useTrans } from '@/composables/useTrans';
import AssetTableActions from '@/pages/admin/master-data/assets/AssetTableActions.vue';
import { assetTableColumns } from '@/pages/admin/master-data/assets/assetTableColumns';
import { dashboard } from '@/routes';
import { index as adminMasterDataIndex } from '@/routes/admin/master-data';
import { index } from '@/routes/admin/master-data/assets';
import type {
    SharedPageProps,
    AssetTableFilters,
    AssetTablePayload,
} from '@/types';
import { EMPTY_ASSET_TABLE_FILTERS } from '@/types/asset';

defineOptions({ inheritAttrs: false });

type Props = {
    table?: AssetTablePayload;
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
            title: trans('admin.master_data.asset.index.title'),
            href: index(),
        },
    ],
});

const columns = computed(() => assetTableColumns());

const tableState = useDeferredDataTableState<AssetTableFilters>({
    table: () => props.table,
    emptyFilters: EMPTY_ASSET_TABLE_FILTERS,
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
    <Head :title="trans('admin.master_data.asset.index.title')" />

    <DeferredDataTablePage
        data="table"
        :title="trans('admin.master_data.asset.index.heading')"
        :description="trans('admin.master_data.asset.index.description')"
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
                    updateFilter(key as keyof AssetTableFilters, value)
            "
            @page-change="setPage"
            @per-page-change="setPerPage"
            @sorting-change="setSorting"
        >
            <template #actions="{ selectedRows }">
                <AssetTableActions
                    :can-create="page.props.auth.abilities.manage_assets"
                    :has-selection="selectedRows.length > 0"
                />
            </template>
        </DataTable>
    </DeferredDataTablePage>
</template>
