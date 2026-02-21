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
const appLogo = computed(() => page.props.settings.general.app_logo ?? undefined);
const appName = computed(() => page.props.settings.general.app_name);
</script>

<template>
    <img :src="appLogo" :alt="appName" :class="className" v-bind="$attrs" />
</template>
