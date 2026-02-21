<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { PlusSquare } from 'lucide-vue-next';

import ContentHeader from '@/components/ContentHeader.vue';
import ContentWrapper from '@/components/ContentWrapper.vue';
import { DataTable } from '@/components/datatable';
import type { Pagination } from '@/components/datatable/types';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { create as usersCreate, index as usersIndex } from '@/routes/master/users';
import type { UserTableRow } from '@/types';

import { userColumns } from './columns';

defineProps<{
    users: {
        data: UserTableRow[];
        meta: Pagination;
    };
}>();
</script>

<template>
    <Head :title="trans('user.title.index')" />

    <AppLayout>
        <ContentWrapper>
            <ContentHeader
                :title="trans('user.header.title')"
                :description="trans('user.header.description')"
            >
                <Button :as="Link" :href="usersCreate()">
                    <PlusSquare class="size-4" />
                    {{ trans('app.button.create') }}
                </Button>
            </ContentHeader>
            <DataTable
                :columns="userColumns"
                :data="users.data"
                :pagination="users.meta"
                :route="usersIndex.url()"
            />
        </ContentWrapper>
    </AppLayout>
</template>
