<script setup lang="ts">
import { type HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <div
        class="flex h-full flex-1 flex-col gap-8 rounded-xl p-4"
        :class="className"
        v-bind="$attrs"
    >
        <slot />
    </div>
</template>