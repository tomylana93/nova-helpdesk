<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { DataTable, DeferredDataTablePage } from '@/components/datatable';
import { useDeferredDataTableState } from '@/composables/useDataTableState';
import { useTrans } from '@/composables/useTrans';
import UserTableActions from '@/pages/admin/master-data/users/UserTableActions.vue';
import { userTableColumns } from '@/pages/admin/master-data/users/userTableColumns';
import { dashboard } from '@/routes';
import { index as adminMasterDataIndex } from '@/routes/admin/master-data';
import { index } from '@/routes/admin/master-data/users';
import { EMPTY_USER_TABLE_FILTERS } from '@/types';
import type {
    SharedPageProps,
    UserTableFilters,
    UserTablePayload,
} from '@/types';

defineOptions({ inheritAttrs: false });

type Props = {
    table?: UserTablePayload;
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
            title: trans('admin.master_data.user.index.title'),
            href: index(),
        },
    ],
});

const columns = computed(() => userTableColumns());

const tableState = useDeferredDataTableState<UserTableFilters>({
    table: () => props.table,
    emptyFilters: EMPTY_USER_TABLE_FILTERS,
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
    <Head :title="trans('admin.master_data.user.index.title')" />

    <DeferredDataTablePage
        data="table"
        :title="trans('admin.master_data.user.index.heading')"
        :description="trans('admin.master_data.user.index.description')"
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
                    updateFilter(key as keyof UserTableFilters, value)
            "
            @page-change="setPage"
            @per-page-change="setPerPage"
            @sorting-change="setSorting"
        >
            <template #actions="{ selectedRows }">
                <UserTableActions
                    :can-create="page.props.auth.abilities.create_users"
                    :has-selection="selectedRows.length > 0"
                />
            </template>
        </DataTable>
    </DeferredDataTablePage>
</template>
