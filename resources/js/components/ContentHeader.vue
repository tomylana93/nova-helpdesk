<script setup lang="ts">
import { type HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    title?: string;
    description?: string;
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <header>
        <div
            class="flex items-center justify-between"
            :class="className"
            v-bind="$attrs"
        >
            <div v-if="title || description" class="space-y-0.5">
                <h1 v-if="title" class="text-2xl font-semibold">
                    {{ title }}
                </h1>
                <p v-if="description" class="text-sm text-muted-foreground">
                    {{ description }}
                </p>
            </div>
            <div class="flex gap-2">
                <slot />
            </div>
        </div>
    </header>
</template>