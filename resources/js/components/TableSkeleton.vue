<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

withDefaults(
    defineProps<{
        variant?: 'cards' | 'table';
    }>(),
    {
        variant: 'table',
    },
);
</script>

<template>
    <div
        v-if="variant === 'cards'"
        class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
    >
        <div
            v-for="item in 3"
            :key="item"
            class="rounded-xl border border-border/70 p-6"
        >
            <Skeleton class="mb-3 h-5 w-1/2" />
            <Skeleton class="mb-6 h-4 w-full" />
            <Skeleton class="h-9 w-28" />
        </div>
    </div>

    <div v-else class="space-y-4">
        <div class="rounded-xl border bg-card p-4">
            <div
                class="mb-4 flex flex-col gap-4 lg:flex-row lg:justify-between"
            >
                <Skeleton class="h-10 w-full md:max-w-sm" />
                <div class="flex gap-2">
                    <Skeleton class="h-10 w-28" />
                    <Skeleton class="h-10 w-24" />
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border">
                <div class="grid grid-cols-4 gap-px bg-border">
                    <Skeleton
                        v-for="item in 20"
                        :key="item"
                        class="h-11 rounded-none bg-card"
                    />
                </div>
            </div>
        </div>

        <Skeleton class="ml-auto h-9 w-64" />
    </div>
</template>
