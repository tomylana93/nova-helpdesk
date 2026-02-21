<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, type HTMLAttributes } from 'vue';

    defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();

const page = usePage();
const appIcon = computed(() => page.props.settings.general.app_icon ?? undefined);
const appName = computed(() => page.props.settings.general.app_name);
</script>

<template>
    <div class="flex items-center justify-center gap-2">
        <div
            class="flex aspect-square size-8 items-center justify-center rounded-md text-sidebar-primary-foreground"
        >
            <img
                :src="appIcon"
                :alt="appName"
                :class="className"
                v-bind="$attrs"
            />
        </div>
        <div class="ml-1 grid flex-1 text-left text-sm">
            <span class="mb-0.5 truncate leading-tight font-semibold">
                {{ appName }}
            </span>
        </div>
    </div>
</template>
