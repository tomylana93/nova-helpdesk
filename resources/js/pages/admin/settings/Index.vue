<script setup lang="ts">
import { Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import { KeyRound, Paintbrush, Wrench } from '@lucide/vue';
import { computed } from 'vue';
import PageWrapper from '@/components/PageWrapper.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import CardFooter from '@/components/ui/card/CardFooter.vue';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { index } from '@/routes/admin/settings';
import { edit as editGeneral } from '@/routes/admin/settings/general';
import { edit as editPassword } from '@/routes/admin/settings/password';
import { edit as editStyle } from '@/routes/admin/settings/style';
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
            title: trans('admin.settings.title'),
            href: index(),
        },
    ],
});

const settingsCard = computed<CardItem[]>(() =>
    page.props.auth.abilities.manage_settings
        ? [
              {
                  title: trans('admin.settings.general.heading'),
                  description: trans('admin.settings.general.description'),
                  href: editGeneral(),
                  icon: Wrench,
              },
              {
                  title: trans('admin.settings.style.heading'),
                  description: trans('admin.settings.style.description'),
                  href: editStyle(),
                  icon: Paintbrush,
              },
              {
                  title: trans('admin.settings.password.heading'),
                  description: trans('admin.settings.password.description'),
                  href: editPassword(),
                  icon: KeyRound,
              },
          ]
        : [],
);
</script>

<template>
    <Head :title="trans('admin.settings.title')" />

    <PageWrapper
        :title="trans('admin.settings.heading')"
        :description="trans('admin.settings.description')"
    >
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="item in settingsCard"
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
                        {{ trans('admin.settings.action.open') }}
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </PageWrapper>
</template>
