<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import AppLogoIconText from '@/components/AppLogoIconText.vue';

const page = usePage();
const headerStyle = computed(() => page.props.settings.general.header_style);
</script>

<template>
    <div v-if="headerStyle === 'icon'">
        <AppLogoIconText />
    </div>

    <div
        v-else
        class="h-12 w-auto transition-all duration-200 ease-linear group-data-[collapsible=icon]:h-8 group-data-[collapsible=icon]:w-auto"
    >
        <AppLogoIcon class="h-full w-auto object-contain" />
    </div>
</template>