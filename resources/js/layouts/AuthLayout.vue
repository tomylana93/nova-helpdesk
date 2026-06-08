<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useStyleSettings } from '@/composables/useStyleSettings';
import AuthCardLayout from '@/layouts/auth/AuthCardLayout.vue';
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import type { SharedPageProps } from '@/types';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage<SharedPageProps>();
useStyleSettings();
const layoutComponent = computed(() => {
    switch (page.props.style.site_auth_layout) {
        case 'card':
            return AuthCardLayout;
        case 'split':
            return AuthSplitLayout;
        default:
            return AuthSimpleLayout;
    }
});
</script>

<template>
    <component :is="layoutComponent" :title="title" :description="description">
        <slot />
    </component>
</template>
