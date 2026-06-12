<script setup lang="ts">
import { Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import PageWrapper from '@/components/PageWrapper.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { index as adminMasterDataIndex } from '@/routes/admin/master-data';
import { edit, index, show } from '@/routes/admin/master-data/branches';
import type { SharedPageProps } from '@/types';

type Branch = {
    id: string;
    code: string;
    name: string;
    status: 'active' | 'inactive';
    statusLabel: string;
    created_at: string;
    updated_at: string;
};

type Props = {
    branch: Branch;
};

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });

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
            title: trans('admin.master_data.branch.index.title'),
            href: index(),
        },
        {
            title: trans('admin.master_data.branch.show.title'),
            href: show(props.branch.id),
        },
    ],
});

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString(page.props.locale, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="trans('admin.master_data.branch.show.title')" />

    <PageWrapper
        :title="trans('admin.master_data.branch.show.heading')"
        :description="trans('admin.master_data.branch.show.description')"
    >
        <template #actions>
            <Button
                v-if="page.props.auth.abilities.manage_branches"
                :as="Link"
                :href="edit(props.branch.id)"
                prefetch
            >
                {{ trans('user.action.edit') }}
            </Button>
            <Button :as="Link" variant="outline" :href="index()">
                {{ trans('admin.master_data.branch.action.back') }}
            </Button>
        </template>

        <div class="flex flex-col gap-6">
            <Card>
                <CardContent class="grid gap-4 pt-6">
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="text-sm font-medium text-muted-foreground sm:min-w-[120px]"
                        >
                            {{ trans('admin.master_data.branch.label.code') }}
                        </span>
                        <span class="text-sm font-semibold">
                            {{ props.branch.code }}
                        </span>
                    </div>
                    <Separator />
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="text-sm font-medium text-muted-foreground sm:min-w-[120px]"
                        >
                            {{ trans('admin.master_data.branch.label.name') }}
                        </span>
                        <span class="text-sm">
                            {{ props.branch.name }}
                        </span>
                    </div>
                    <Separator />
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="text-sm font-medium text-muted-foreground sm:min-w-[120px]"
                        >
                            {{ trans('admin.master_data.branch.label.status') }}
                        </span>
                        <Badge
                            :variant="
                                props.branch.status === 'active'
                                    ? 'default'
                                    : 'destructive'
                            "
                            class="w-fit font-normal capitalize"
                        >
                            {{ props.branch.statusLabel }}
                        </Badge>
                    </div>
                    <Separator />
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="text-sm font-medium text-muted-foreground sm:min-w-[120px]"
                        >
                            {{ trans('user.label.created_at') }}
                        </span>
                        <span class="text-sm">
                            {{ formatDate(props.branch.created_at) }}
                        </span>
                    </div>
                    <Separator />
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="text-sm font-medium text-muted-foreground sm:min-w-[120px]"
                        >
                            {{ trans('user.label.updated_at') }}
                        </span>
                        <span class="text-sm">
                            {{ formatDate(props.branch.updated_at) }}
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </PageWrapper>
</template>
