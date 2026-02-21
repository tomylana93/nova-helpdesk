<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Props = {
    title?: string;
};

const props = defineProps<Props>();

const appName = usePage().props.settings.general.app_name;

const pageTitle = computed(() => {
    if (props.title) {
        return `${props.title} - ${appName}`;
    }
    return appName;
});
</script>

<template>
    <Head :title="pageTitle" />
</template>