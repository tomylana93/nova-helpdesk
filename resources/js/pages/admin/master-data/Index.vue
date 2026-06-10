<script setup lang="ts">
import { Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import { Building2, FolderTree, Layers, Network, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import PageWrapper from '@/components/PageWrapper.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import CardFooter from '@/components/ui/card/CardFooter.vue';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { index } from '@/routes/admin/master-data';
import { index as indexBranches } from '@/routes/admin/master-data/branches';
import { index as indexDepartments } from '@/routes/admin/master-data/departments';
import { index as indexQueues } from '@/routes/admin/master-data/queues';
import { index as indexCategories } from '@/routes/admin/master-data/ticket-categories';
import { index as indexUsers } from '@/routes/admin/master-data/users';
import type { CardItem, SharedPageProps } from '@/types';

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
            href: index(),
        },
    ],
});

const masterDataCard = computed<CardItem[]>(() => {
    const cards: CardItem[] = [];

    if (page.props.auth.abilities.view_users) {
        cards.push({
            title: trans('admin.master_data.user.index.title'),
            description: trans('admin.master_data.user.index.description'),
            href: indexUsers(),
            icon: Users,
        });
    }

    if (page.props.auth.abilities.manage_branches) {
        cards.push({
            title: trans('admin.master_data.branch.index.title'),
            description: trans('admin.master_data.branch.index.description'),
            href: indexBranches(),
            icon: Building2,
        });
    }

    if (page.props.auth.abilities.manage_departments) {
        cards.push({
            title: trans('admin.master_data.department.index.title'),
            description: trans(
                'admin.master_data.department.index.description',
            ),
            href: indexDepartments(),
            icon: Network,
        });
    }

    if (page.props.auth.abilities.manage_queues) {
        cards.push({
            title: trans('admin.master_data.queue.index.title'),
            description: trans('admin.master_data.queue.index.description'),
            href: indexQueues(),
            icon: Layers,
        });
    }

    if (page.props.auth.abilities.manage_categories) {
        cards.push({
            title: trans('admin.master_data.ticket_category.index.title'),
            description: trans(
                'admin.master_data.ticket_category.index.description',
            ),
            href: indexCategories(),
            icon: FolderTree,
        });
    }

    return cards;
});
</script>

<template>
    <Head :title="trans('admin.master_data.title')" />

    <PageWrapper
        :title="trans('admin.master_data.heading')"
        :description="trans('admin.master_data.description')"
    >
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="item in masterDataCard"
                :key="item.title"
                class="flex h-full flex-col"
            >
                <CardContent class="flex flex-1 items-start gap-4">
                    <component :is="item.icon" class="mt-0.5 size-6" />
                    <div class="flex flex-col gap-1">
                        <h3 class="text-sm font-medium">{{ item.title }}</h3>
                        <p class="text-sm text-muted-foreground">
                            {{ item.description }}
                        </p>
                    </div>
                </CardContent>
                <CardFooter class="pt-0">
                    <Button :as="Link" :href="item.href">
                        {{ trans('admin.master_data.action.open') }}
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </PageWrapper>
</template>
