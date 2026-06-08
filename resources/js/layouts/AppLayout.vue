<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useStyleSettings } from '@/composables/useStyleSettings';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbsProps, SharedPageProps } from '@/types';

const { breadcrumbs = [] } = defineProps<BreadcrumbsProps>();

const page = usePage<SharedPageProps>();
useStyleSettings();
const layoutComponent = computed(() =>
    page.props.style.site_layout === 'header'
        ? AppHeaderLayout
        : AppSidebarLayout,
);
</script>

<template>
    <component :is="layoutComponent" :breadcrumbs="breadcrumbs">
        <slot />
    </component>
</template>
