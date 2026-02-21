<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { ArrowRight, Users } from 'lucide-vue-next';

import ContentHeader from '@/components/ContentHeader.vue';
import ContentWrapper from '@/components/ContentWrapper.vue';
import {
    Card,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as usersIndex } from '@/routes/master/users';
import type { ModuleCard } from '@/types';

const moduleCards: ModuleCard[] = [
    {
        key: 'users',
        badgeKey: 'breadcrumbs.master',
        titleKey: 'master.modules.users.title',
        descriptionKey: 'master.modules.users.description',
        ctaKey: 'master.modules.users.cta',
        href: usersIndex.url(),
        icon: Users,
    },
];
</script>

<template>
    <Head :title="trans('master.title.index')" />

    <AppLayout>
        <ContentWrapper>
            <ContentHeader
                :title="trans('master.header.title')"
                :description="trans('master.header.description')"
            />

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <Card
                    v-for="moduleCard in moduleCards"
                    :key="moduleCard.key"
                    class="flex h-full flex-col"
                >
                    <CardHeader class="space-y-2">
                        <div
                            class="inline-flex items-center gap-2 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            <component :is="moduleCard.icon" class="size-4" />
                            <span>{{ trans(moduleCard.badgeKey) }}</span>
                        </div>
                        <CardTitle>{{ trans(moduleCard.titleKey) }}</CardTitle>
                        <CardDescription>
                            {{ trans(moduleCard.descriptionKey) }}
                        </CardDescription>
                    </CardHeader>
                    <CardFooter>
                        <Link
                            :href="moduleCard.href"
                            class="inline-flex items-center text-sm font-medium text-primary hover:underline"
                        >
                            <span>{{ trans(moduleCard.ctaKey) }}</span>
                            <ArrowRight class="ml-1 size-4" />
                        </Link>
                    </CardFooter>
                </Card>
            </div>
        </ContentWrapper>
    </AppLayout>
</template>
