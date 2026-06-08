<script setup lang="ts">
import { Deferred } from '@inertiajs/vue3';
import PageWrapper from '@/components/PageWrapper.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';

withDefaults(
    defineProps<{
        data: string | string[];
        description?: string;
        loadingVariant?: 'cards' | 'table';
        title?: string;
    }>(),
    {
        description: undefined,
        loadingVariant: 'table',
        title: undefined,
    },
);
</script>

<template>
    <PageWrapper :title="title" :description="description">
        <Deferred :data="data">
            <template #fallback>
                <TableSkeleton :variant="loadingVariant" />
            </template>

            <slot />
        </Deferred>
    </PageWrapper>
</template>
