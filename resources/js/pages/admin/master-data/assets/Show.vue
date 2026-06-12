<script setup lang="ts">
import { Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import PageWrapper from '@/components/PageWrapper.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { index as adminMasterDataIndex } from '@/routes/admin/master-data';
import { edit, index, show } from '@/routes/admin/master-data/assets';
import { show as showTicket } from '@/routes/tickets';
import type { SharedPageProps } from '@/types';

type TicketItem = {
    id: string;
    ticket_number: string;
    subject: string;
    status: string;
    statusLabel: string;
    statusVariant: string;
    priority: string;
    priorityLabel: string;
    priorityVariant: string;
    created_at: string | null;
};

type Asset = {
    id: string;
    asset_tag: string;
    name: string;
    category: string;
    categoryLabel: string;
    status: string;
    statusLabel: string;
    statusVariant: string;
    created_at: string;
    updated_at: string;
    branch?: {
        id: string;
        name: string;
    } | null;
    user?: {
        id: string;
        name: string;
        email: string;
    } | null;
    tickets?: TicketItem[];
};

type Props = {
    asset: Asset;
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
            title: trans('admin.master_data.asset.index.title'),
            href: index(),
        },
        {
            title: trans('admin.master_data.asset.show.title'),
            href: show(props.asset.id),
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
    <Head :title="trans('admin.master_data.asset.show.title')" />

    <PageWrapper
        :title="trans('admin.master_data.asset.show.heading')"
        :description="trans('admin.master_data.asset.show.description')"
    >
        <template #actions>
            <Button
                v-if="page.props.auth.abilities.manage_assets"
                :as="Link"
                :href="edit(props.asset.id)"
                prefetch
            >
                {{ trans('user.action.edit') }}
            </Button>
            <Button :as="Link" variant="outline" :href="index()">
                {{ trans('admin.master_data.asset.action.back') }}
            </Button>
        </template>

        <div class="flex flex-col gap-6 lg:grid lg:grid-cols-3">
            <div class="flex flex-col gap-6 lg:col-span-2">
                <Card>
                    <CardContent class="grid gap-4 pt-6">
                        <div
                            class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                        >
                            <span
                                class="text-sm font-medium text-muted-foreground sm:min-w-[150px]"
                            >
                                {{
                                    trans(
                                        'admin.master_data.asset.label.asset_tag',
                                    )
                                }}
                            </span>
                            <span class="text-sm font-semibold">
                                {{ props.asset.asset_tag }}
                            </span>
                        </div>
                        <Separator />
                        <div
                            class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                        >
                            <span
                                class="text-sm font-medium text-muted-foreground sm:min-w-[150px]"
                            >
                                {{
                                    trans('admin.master_data.asset.label.name')
                                }}
                            </span>
                            <span class="text-sm font-medium">
                                {{ props.asset.name }}
                            </span>
                        </div>
                        <Separator />
                        <div
                            class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                        >
                            <span
                                class="text-sm font-medium text-muted-foreground sm:min-w-[150px]"
                            >
                                {{
                                    trans(
                                        'admin.master_data.asset.label.category',
                                    )
                                }}
                            </span>
                            <span class="text-sm">
                                {{ props.asset.categoryLabel }}
                            </span>
                        </div>
                        <Separator />
                        <div
                            class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                        >
                            <span
                                class="text-sm font-medium text-muted-foreground sm:min-w-[150px]"
                            >
                                {{
                                    trans(
                                        'admin.master_data.asset.label.status',
                                    )
                                }}
                            </span>
                            <Badge
                                :variant="
                                    props.asset.statusVariant as 'default'
                                "
                                class="w-fit font-normal capitalize"
                            >
                                {{ props.asset.statusLabel }}
                            </Badge>
                        </div>
                        <Separator />
                        <div
                            class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                        >
                            <span
                                class="text-sm font-medium text-muted-foreground sm:min-w-[150px]"
                            >
                                {{
                                    trans(
                                        'admin.master_data.asset.label.branch',
                                    )
                                }}
                            </span>
                            <span class="text-sm">
                                {{
                                    props.asset.branch?.name ||
                                    trans(
                                        'admin.master_data.asset.placeholder.branch_unassigned',
                                    )
                                }}
                            </span>
                        </div>
                        <Separator />
                        <div
                            class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                        >
                            <span
                                class="text-sm font-medium text-muted-foreground sm:min-w-[150px]"
                            >
                                {{
                                    trans('admin.master_data.asset.label.user')
                                }}
                            </span>
                            <span class="text-sm">
                                {{
                                    props.asset.user?.name ||
                                    trans(
                                        'admin.master_data.asset.placeholder.user_unassigned',
                                    )
                                }}
                                <span
                                    v-if="props.asset.user?.email"
                                    class="ml-1 text-xs text-muted-foreground"
                                >
                                    ({{ props.asset.user.email }})
                                </span>
                            </span>
                        </div>
                        <Separator />
                        <div
                            class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                        >
                            <span
                                class="text-sm font-medium text-muted-foreground sm:min-w-[150px]"
                            >
                                {{ trans('user.label.created_at') }}
                            </span>
                            <span class="text-sm">
                                {{ formatDate(props.asset.created_at) }}
                            </span>
                        </div>
                        <Separator />
                        <div
                            class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                        >
                            <span
                                class="text-sm font-medium text-muted-foreground sm:min-w-[150px]"
                            >
                                {{ trans('user.label.updated_at') }}
                            </span>
                            <span class="text-sm">
                                {{ formatDate(props.asset.updated_at) }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">
                            {{ trans('admin.master_data.asset.history.title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="
                                props.asset.tickets &&
                                props.asset.tickets.length > 0
                            "
                            class="divide-y divide-border"
                        >
                            <div
                                v-for="ticket in props.asset.tickets"
                                :key="ticket.id"
                                class="flex items-center justify-between py-3 first:pt-0 last:pb-0"
                            >
                                <div class="flex flex-col gap-1">
                                    <Link
                                        :href="showTicket(ticket.id)"
                                        class="text-sm font-semibold text-primary hover:underline"
                                    >
                                        {{ ticket.ticket_number }}
                                    </Link>
                                    <span
                                        class="text-sm text-muted-foreground"
                                        >{{ ticket.subject }}</span
                                    >
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge
                                        :variant="
                                            ticket.priorityVariant as 'default'
                                        "
                                        class="text-xs capitalize"
                                    >
                                        {{ ticket.priorityLabel }}
                                    </Badge>
                                    <Badge
                                        :variant="
                                            ticket.statusVariant as 'default'
                                        "
                                        class="text-xs capitalize"
                                    >
                                        {{ ticket.statusLabel }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="py-6 text-center text-sm text-muted-foreground"
                        >
                            {{ trans('admin.master_data.asset.history.empty') }}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </PageWrapper>
</template>
