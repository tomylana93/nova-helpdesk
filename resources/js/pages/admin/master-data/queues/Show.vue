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
import { edit, index, show } from '@/routes/admin/master-data/queues';
import type { SharedPageProps } from '@/types';

type Queue = {
    id: string;
    name: string;
    description: string | null;
    status: 'active' | 'inactive';
    statusLabel: string;
    created_at: string;
    updated_at: string;
};

type Props = {
    queue: Queue;
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
            title: trans('admin.master_data.queue.index.title'),
            href: index(),
        },
        {
            title: trans('admin.master_data.queue.show.title'),
            href: show(props.queue.id),
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
    <Head :title="trans('admin.master_data.queue.show.title')" />

    <PageWrapper
        :title="trans('admin.master_data.queue.show.heading')"
        :description="trans('admin.master_data.queue.show.description')"
    >
        <template #actions>
            <Button
                v-if="page.props.auth.abilities.manage_queues"
                :as="Link"
                :href="edit(props.queue.id)"
                prefetch
            >
                {{ trans('user.action.edit') }}
            </Button>
            <Button :as="Link" variant="outline" :href="index()">
                {{ trans('admin.master_data.queue.action.back') }}
            </Button>
        </template>

        <div class="flex flex-col gap-6">
            <Card>
                <CardContent class="grid gap-4 pt-6">
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="min-w-[140px] text-sm font-medium text-muted-foreground"
                        >
                            {{ trans('admin.master_data.queue.label.name') }}
                        </span>
                        <span class="text-sm font-semibold">
                            {{ props.queue.name }}
                        </span>
                    </div>
                    <Separator />
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="min-w-[140px] text-sm font-medium text-muted-foreground"
                        >
                            {{
                                trans(
                                    'admin.master_data.queue.label.description',
                                )
                            }}
                        </span>
                        <span class="text-sm">
                            {{ props.queue.description ?? '-' }}
                        </span>
                    </div>
                    <Separator />
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="min-w-[140px] text-sm font-medium text-muted-foreground"
                        >
                            {{ trans('admin.master_data.queue.label.status') }}
                        </span>
                        <Badge
                            :variant="
                                props.queue.status === 'active'
                                    ? 'default'
                                    : 'destructive'
                            "
                            class="w-fit font-normal capitalize"
                        >
                            {{ props.queue.statusLabel }}
                        </Badge>
                    </div>
                    <Separator />
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="min-w-[140px] text-sm font-medium text-muted-foreground"
                        >
                            {{ trans('user.label.created_at') }}
                        </span>
                        <span class="text-sm">
                            {{ formatDate(props.queue.created_at) }}
                        </span>
                    </div>
                    <Separator />
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="min-w-[140px] text-sm font-medium text-muted-foreground"
                        >
                            {{ trans('user.label.updated_at') }}
                        </span>
                        <span class="text-sm">
                            {{ formatDate(props.queue.updated_at) }}
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </PageWrapper>
</template>
