<script setup lang="ts">
import type { Component } from 'vue';

import DeltaBadge from '@/components/dashboard/DeltaBadge.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    label: string;
    value: number | string;
    icon: Component;
    deltaPercent?: number | null;
    direction?: 'up' | 'down' | 'flat';
    sentiment?: 'higher_is_better' | 'lower_is_better' | 'neutral';
    caption?: string;
}>();
</script>

<template>
    <Card class="border-border/60">
        <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
            <CardTitle class="text-sm font-medium text-muted-foreground">
                {{ label }}
            </CardTitle>
            <div class="rounded-lg bg-muted p-1.5">
                <component :is="icon" class="h-4 w-4 text-muted-foreground" />
            </div>
        </CardHeader>
        <CardContent>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight">{{
                    value
                }}</span>
                <DeltaBadge
                    v-if="direction"
                    :delta-percent="deltaPercent ?? null"
                    :direction="direction"
                    :sentiment="sentiment ?? 'neutral'"
                />
            </div>
            <p v-if="caption" class="mt-1 text-xs text-muted-foreground">
                {{ caption }}
            </p>
        </CardContent>
    </Card>
</template>
