<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import { toast } from 'vue-sonner';

import { Toaster } from '@/components/ui/sonner';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import 'vue-sonner/style.css';

const page = usePage();
const showFlashToast = (flash: any) => {
    if (flash?.success) {
        toast.success(flash.success);
    }
    if (flash?.error) {
        toast.error(flash.error);
    }
    if (flash?.warning) {
        toast.warning(flash.warning);
    }
    if (flash?.info) {
        toast.info(flash.info);
    }
};

onMounted(() => {
    showFlashToast(page.props.flash);
});

watch(
    () => page.props.flash,
    (flash: any) => {
        showFlashToast(flash);
    },
    { deep: true },
);
</script>

<template>
    <AppLayout>
        <slot />
    </AppLayout>
    <Toaster position="top-center" :expand="true" richColors />
</template>
