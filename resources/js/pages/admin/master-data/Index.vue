<script setup lang="ts">
import { Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
import { computed } from 'vue';
import PageWrapper from '@/components/PageWrapper.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import CardFooter from '@/components/ui/card/CardFooter.vue';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { index } from '@/routes/admin/master-data';
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

const masterDataCard = computed<CardItem[]>(() =>
    page.props.auth.abilities.view_users
        ? [
              {
                  title: trans('admin.master_data.user.index.title'),
                  description: trans(
                      'admin.master_data.user.index.description',
                  ),
                  href: indexUsers(),
                  icon: Users,
              },
          ]
        : [],
);
</script>

<template>
    <Head :title="trans('admin.master_data.title')" />

    <PageWrapper
        :title="trans('admin.master_data.heading')"
        :description="trans('admin.master_data.description')"
    >
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card v-for="item in masterDataCard" :key="item.title">
                <CardContent class="flex items-center gap-4">
                    <component :is="item.icon" class="size-6" />
                    <div class="flex flex-col gap-1">
                        <h3 class="text-sm font-medium">{{ item.title }}</h3>
                        <p class="text-sm text-muted-foreground">
                            {{ item.description }}
                        </p>
                    </div>
                </CardContent>
                <CardFooter>
                    <Button :as="Link" :href="item.href">
                        {{ trans('admin.master_data.action.open') }}
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </PageWrapper>
</template>
