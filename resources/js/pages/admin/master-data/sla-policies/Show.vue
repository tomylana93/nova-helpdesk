<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { index as adminMasterDataIndex } from '@/routes/admin/master-data';
import { edit, index, show } from '@/routes/admin/master-data/sla-policies';
import type { SlaPolicy } from '@/types';

type Props = {
    slaPolicy: SlaPolicy;
};

const props = defineProps<Props>();
const { trans } = useTrans();

setLayoutProps({
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        {
            title: trans('admin.master_data.title'),
            href: adminMasterDataIndex(),
        },
        {
            title: trans('admin.master_data.sla_policy.index.title'),
            href: index(),
        },
        { title: props.slaPolicy.name, href: show(props.slaPolicy.id) },
    ],
});
</script>

<template>
    <Head :title="trans('admin.master_data.sla_policy.show.title')" />

    <div class="mx-auto max-w-2xl space-y-6 p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ slaPolicy.name }}</h1>
            <div class="flex gap-2">
                <Button :as="Link" :href="edit(slaPolicy.id)" size="sm">
                    {{ trans('admin.master_data.sla_policy.action.update') }}
                </Button>
                <Button :as="Link" variant="outline" :href="index()" size="sm">
                    {{ trans('admin.master_data.sla_policy.action.back') }}
                </Button>
            </div>
        </div>

        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <dl class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{
                            trans(
                                'admin.master_data.sla_policy.label.ticket_type',
                            )
                        }}
                    </dt>
                    <dd class="mt-1 text-sm">
                        {{ slaPolicy.ticketTypeLabel ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{
                            trans('admin.master_data.sla_policy.label.priority')
                        }}
                    </dt>
                    <dd class="mt-1">
                        <Badge
                            variant="outline"
                            class="font-normal capitalize"
                            >{{ slaPolicy.priorityLabel }}</Badge
                        >
                    </dd>
                </div>
                <div>
                    <dt
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{
                            trans(
                                'admin.master_data.sla_policy.label.first_response',
                            )
                        }}
                    </dt>
                    <dd class="mt-1 text-sm">
                        {{ slaPolicy.first_response_target_minutes }} min
                    </dd>
                </div>
                <div>
                    <dt
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{
                            trans(
                                'admin.master_data.sla_policy.label.resolution',
                            )
                        }}
                    </dt>
                    <dd class="mt-1 text-sm">
                        {{ slaPolicy.resolution_target_minutes }} min
                    </dd>
                </div>
                <div>
                    <dt
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{
                            trans(
                                'admin.master_data.sla_policy.label.is_active',
                            )
                        }}
                    </dt>
                    <dd class="mt-1">
                        <Badge
                            :variant="
                                slaPolicy.is_active ? 'default' : 'destructive'
                            "
                            class="font-normal"
                        >
                            {{ slaPolicy.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
