<script setup lang="ts">
import { Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import PageWrapper from '@/components/PageWrapper.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import UserAvatar from '@/components/UserAvatar.vue';
import UserStatusBadge from '@/components/UserStatusBadge.vue';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { index as adminMasterDataIndex } from '@/routes/admin/master-data';
import { edit, index, show } from '@/routes/admin/master-data/users';
import type { SharedPageProps, User } from '@/types';

type Props = {
    user: User & { branchName?: string | null; departmentName?: string | null };
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
            title: trans('admin.master_data.user.index.title'),
            href: index(),
        },
        {
            title: trans('admin.master_data.user.show.title'),
            href: show(props.user.id),
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
    <Head :title="trans('admin.master_data.user.show.title')" />

    <PageWrapper
        :title="trans('admin.master_data.user.show.heading')"
        :description="trans('admin.master_data.user.show.description')"
    >
        <template #actions>
            <Button
                v-if="page.props.auth.abilities.update_users"
                :as="Link"
                :href="edit(props.user.id)"
                prefetch
            >
                {{ trans('user.action.edit') }}
            </Button>
            <Button :as="Link" variant="outline" :href="index()">
                {{ trans('admin.master_data.user.action.back') }}
            </Button>
        </template>

        <div class="flex flex-col gap-6">
            <Card>
                <CardContent
                    class="flex flex-col items-center gap-4 pt-6 sm:flex-row sm:items-start"
                >
                    <UserAvatar
                        :name="props.user.name"
                        :avatar="props.user.avatar"
                        class="size-20 text-2xl"
                    />
                    <div class="text-center sm:text-left">
                        <h2 class="text-xl font-semibold">
                            {{ props.user.name }}
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            {{ props.user.email }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="grid gap-4 pt-6">
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="text-sm font-medium text-muted-foreground sm:min-w-[120px]"
                        >
                            {{ trans('admin.master_data.user.label.status') }}
                        </span>
                        <UserStatusBadge
                            :status="props.user.status"
                            :label="props.user.statusLabel ?? ''"
                        />
                    </div>
                    <Separator />
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="text-sm font-medium text-muted-foreground sm:min-w-[120px]"
                        >
                            {{ trans('admin.master_data.user.label.role') }}
                        </span>
                        <Badge
                            v-if="props.user.roleLabel"
                            variant="outline"
                            class="w-fit font-normal"
                        >
                            {{ props.user.roleLabel }}
                        </Badge>
                        <span v-else class="text-sm text-muted-foreground"
                            >—</span
                        >
                    </div>
                    <Separator />
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="text-sm font-medium text-muted-foreground sm:min-w-[120px]"
                        >
                            {{ trans('admin.master_data.user.label.branch') }}
                        </span>
                        <span class="text-sm">
                            {{ props.user.branchName ?? '—' }}
                        </span>
                    </div>
                    <Separator />
                    <div
                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4"
                    >
                        <span
                            class="text-sm font-medium text-muted-foreground sm:min-w-[120px]"
                        >
                            {{
                                trans('admin.master_data.user.label.department')
                            }}
                        </span>
                        <span class="text-sm">
                            {{ props.user.departmentName ?? '—' }}
                        </span>
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
                            {{ formatDate(props.user.created_at) }}
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
                            {{ formatDate(props.user.updated_at) }}
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </PageWrapper>
</template>
